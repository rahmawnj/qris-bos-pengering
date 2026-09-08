<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AdminTransactionsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    use RegistersEventListeners;

    protected $transactions;
    protected $startDate;
    protected $endDate;
    protected $totalAmount;
    protected $totalTransactionsCount;
    protected $summaryEnabled;

    public function __construct($transactions, $startDate = null, $endDate = null, $totalAmount = 0, $totalTransactionsCount = 0, $summaryEnabled = true)
    {
        $this->transactions = collect($transactions)->values()->map(function ($item, $key) {
            $item->no = $key + 1;
            return $item;
        });

        $this->startDate = $startDate ? Carbon::parse($startDate) : null;
        $this->endDate = $endDate ? Carbon::parse($endDate) : null;

        if (!$this->startDate || !$this->endDate) {
            $minCreatedAt = collect($transactions)->min('created_at');
            $maxCreatedAt = collect($transactions)->max('created_at');
            $this->startDate = $this->startDate ?: ($minCreatedAt ? Carbon::parse($minCreatedAt) : null);
            $this->endDate = $this->endDate ?: ($maxCreatedAt ? Carbon::parse($maxCreatedAt) : null);
        }
        $this->totalAmount = $totalAmount;
        $this->totalTransactionsCount = $totalTransactionsCount ?: $transactions->count();
        $this->summaryEnabled = $summaryEnabled;
    }

    public function collection(): Collection
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Order ID',
            'Owner',
            'Outlet',
            'Kode Device',
            'Jumlah (Rp)',
            'Tipe Pembayaran / Provider',
            'Kategori',
            'Status',
            'Waktu',
        ];
    }

    public function map($transaction): array
    {
        $timezoneMap = [
            'wib' => 'Asia/Jakarta',
            'wita' => 'Asia/Makassar',
            'wit' => 'Asia/Jayapura',
        ];

        $tzKey = strtolower(optional($transaction)->timezone);
        $tz = $timezoneMap[$tzKey] ?? 'Asia/Jakarta';

        $transactionDateTime = optional($transaction->created_at)
            ? Carbon::parse($transaction->created_at)->setTimezone($tz)
            : null;

        $amount = (float)optional($transaction)->amount ?? 0;
        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');

        $serviceCategory = 'Lainnya';
        if (optional($transaction)->type === 'manual') {
            $serviceCategory = 'Drop Off';
        } elseif (optional($transaction)->type === 'qris') {
            $serviceCategory = 'Self Service';
        } elseif (optional($transaction)->type === 'member') {
            $serviceCategory = 'Member';
        }

        $manual = optional($transaction)->manualTransaction;
        $ownerBrand = (string)optional(optional($transaction)->owner)->brand_name ?? '-';
        $ownerName = (string)optional(optional(optional($transaction)->owner)->user)->name ?? '-';
        $ownerText = ($ownerBrand === '-' && $ownerName === '-') ? '-' : trim($ownerBrand . ' - ' . $ownerName);
        $paymentTypeText = match (optional($transaction)->type) {
            'manual' => optional($manual)->payment_method === 'cash'
                ? 'Tunai'
                : (optional($manual)->payment_method === 'non_cash'
                    ? 'Transfer'
                    : ucfirst(optional($manual)->payment_method ?? '')),
            'qris' => 'QRIS - ' . (string) optional($transaction)->qris_provider_label,
            default => ucfirst(optional($transaction)->type ?? ''),
        };

        return [
            (string)optional($transaction)->no ?? 'N/A',
            (string)optional($transaction)->order_id ?? 'N/A',
            $ownerText,
            (string)optional(optional($transaction)->outlet)->outlet_name ?? 'N/A',
            (string)optional($transaction)->device_code ?? 'N/A',
            $formattedAmount,
            $paymentTypeText !== '' ? $paymentTypeText : 'N/A',
            $serviceCategory,
            (string)ucfirst(optional($transaction)->status ?? '') ?? 'N/A',
            (string)optional($transactionDateTime)->format('Y-m-d H:i:s') . ' ' . strtoupper((string)optional($transaction)->timezone ?? ''),
        ];
    }

    public function title(): string
    {
        return 'REPORT TRANSACTION';
    }

    public static function afterSheet(AfterSheet $event)
    {
        $sheet = $event->sheet->getDelegate();
        $export = $event->getConcernable();

        // Insert 6 baris kosong di atas agar Header bawaan pindah ke Row 7
        $sheet->insertNewRowBefore(1, 6);

        $startDateFormatted = $export->startDate ? $export->startDate->isoFormat('D MMMM YYYY') : 'N/A';
        $endDateFormatted = $export->endDate ? $export->endDate->isoFormat('D MMMM YYYY') : 'N/A';
        $dateRangeText = "Tanggal : {$startDateFormatted} - {$endDateFormatted}";

        // Judul Laporan (Row 2)
        $sheet->setCellValue('A2', 'REPORT TRANSACTION');
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2:J2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Tanggal (Row 4)
        $sheet->setCellValue('A4', $dateRangeText);
        $sheet->mergeCells('A4:J4');
        $sheet->getStyle('A4:J4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Styling header & data AFTER rows are inserted
        $highestDataRow = $sheet->getHighestDataRow();
        $headerRow = 7;
        $dataStartRow = 8;

        // Header row style
        $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FF4CAF50'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        if ($highestDataRow >= $dataStartRow) {
            $sheet->getStyle('A' . $dataStartRow . ':J' . $highestDataRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFDDDDDD'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ]);

            $sheet->getStyle('A' . $dataStartRow . ':A' . $highestDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $dataStartRow . ':F' . $highestDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J' . $dataStartRow . ':J' . $highestDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            for ($row = $dataStartRow; $row <= $highestDataRow; $row++) {
                if (($row - $dataStartRow) % 2 === 0) {
                    $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['argb' => 'FFE8F5E9'],
                        ],
                    ]);
                }
            }
        }

        // SUMMARY DI BAWAH DATA
        if (!$export->summaryEnabled) {
            return;
        }
        $summaryRow = $sheet->getHighestDataRow() + 1;
        $totalAmountFormatted = 'Rp ' . number_format((float)$export->totalAmount, 0, ',', '.');

        $sheet->setCellValue('A' . $summaryRow, 'Total Transaksi');
        $sheet->setCellValue('B' . $summaryRow, $export->totalTransactionsCount);
        $sheet->setCellValue('D' . $summaryRow, 'Total Jumlah (Rp)');
        $sheet->setCellValue('E' . $summaryRow, $totalAmountFormatted);

        $sheet->getStyle('A' . $summaryRow . ':J' . $summaryRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFEEEEEE'],
            ],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_MEDIUM],
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM],
            ],
        ]);

        $sheet->getStyle('A' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}
