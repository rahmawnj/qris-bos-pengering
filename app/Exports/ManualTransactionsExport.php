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

class ManualTransactionsExport implements
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
    protected $brandName;

    public function __construct($transactions, $startDate = null, $endDate = null, $totalAmount = 0, $totalTransactionsCount = 0, $brandName = null)
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
        $this->brandName = $brandName;
    }

    public function collection(): Collection
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'No.',
            'ID Pesanan',
            'Owner & Outlet',
            'Kode Device',
            'Kasir',
            'Pelanggan',
            'Estimasi Selesai',
            'Jumlah Pembayaran',
            'Layanan & Unit',
            'Metode Bayar',
            'Status',
            'Tanggal',
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

        $manual = optional($transaction)->manualTransaction;
        $ownerBrand = (string)optional(optional($transaction)->owner)->brand_name ?? '-';
        $outletName = (string)optional(optional($transaction)->outlet)->outlet_name ?? 'N/A';
        $ownerOutletText = trim($ownerBrand . ' | ' . $outletName);

        $deviceCode = (string)optional($transaction)->device_code
            ?: (string)optional(optional($transaction)->deviceTransactions)->first()?->device_code ?? 'N/A';

        $estimatedText = 'N/A';
        if ($manual && $manual->estimated_completion_at) {
            $estimatedText = Carbon::parse($manual->estimated_completion_at)->setTimezone($tz)->format('d-m-Y H:i') . ' ' . strtoupper((string)optional($transaction)->timezone ?? '');
        }

        $amount = (float)optional($transaction)->amount ?? 0;
        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');

        $serviceName = optional(optional($manual)->service)->name ?? 'N/A';
        $qty = $manual?->quantity ?? 1;
        $unit = $manual?->unit ?? 'Unit';
        $methodText = $manual
            ? ucfirst(str_replace('_', ' ', (string)$manual->payment_method))
            : 'N/A';
        $serviceText = "Layanan: {$serviceName} | Jumlah: {$qty} {$unit}";

        $paymentBadgeText = $manual
            ? ($manual->payment_method === 'cash' ? 'Tunai' : ($manual->payment_method === 'non_cash' ? 'Transfer' : ucfirst((string)$manual->payment_method)))
            : 'N/A';

        $statusText = ucfirst((string)optional($transaction)->status ?? 'N/A');

        $dateText = $transactionDateTime
            ? $transactionDateTime->format('d-m-Y H:i') . ' ' . strtoupper((string)optional($transaction)->timezone ?? '')
            : 'N/A';

        return [
            (string)optional($transaction)->no ?? 'N/A',
            (string)optional($transaction)->order_id ?? 'N/A',
            $ownerOutletText ?: 'N/A',
            $deviceCode ?: 'N/A',
            (string)($manual->cashier_name ?? 'N/A'),
            (string)trim(($manual->customer_name ?? 'N/A') . ' | ' . ($manual->customer_phone_number ?? '-')),
            $estimatedText,
            $formattedAmount,
            $serviceText,
            $paymentBadgeText,
            $statusText,
            $dateText,
        ];
    }

    public function title(): string
    {
        return 'TRANSAKSI DROP-OFF';
    }

    public static function afterSheet(AfterSheet $event)
    {
        $sheet = $event->sheet->getDelegate();
        $export = $event->getConcernable();

        // Insert 6 blank rows so header starts at row 7
        $sheet->insertNewRowBefore(1, 6);

        $startDateFormatted = $export->startDate ? $export->startDate->isoFormat('D MMMM YYYY') : 'N/A';
        $endDateFormatted = $export->endDate ? $export->endDate->isoFormat('D MMMM YYYY') : 'N/A';
        $dateRangeText = "Tanggal : {$startDateFormatted} - {$endDateFormatted}";

        // Title (Row 2)
        $sheet->setCellValue('A2', 'TRANSAKSI DROP-OFF');
        $sheet->mergeCells('A2:M2');
        $sheet->getStyle('A2:M2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Date (Row 4) + Brand (optional)
        if (!empty($export->brandName)) {
            $sheet->setCellValue('A4', 'Brand: ' . $export->brandName);
            $sheet->mergeCells('A4:E4');
            $sheet->getStyle('A4:E4')->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);

            $sheet->setCellValue('F4', $dateRangeText);
            $sheet->mergeCells('F4:M4');
            $sheet->getStyle('F4:M4')->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);
        } else {
            $sheet->setCellValue('A4', $dateRangeText);
            $sheet->mergeCells('A4:M4');
            $sheet->getStyle('A4:M4')->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
        }

        // Styling header & data after rows inserted
        $highestDataRow = $sheet->getHighestDataRow();
        $headerRow = 7;
        $dataStartRow = 8;

        $sheet->getStyle('A' . $headerRow . ':M' . $headerRow)->applyFromArray([
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
            $sheet->getStyle('A' . $dataStartRow . ':M' . $highestDataRow)->applyFromArray([
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
            $sheet->getStyle('G' . $dataStartRow . ':G' . $highestDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('M' . $dataStartRow . ':M' . $highestDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            for ($row = $dataStartRow; $row <= $highestDataRow; $row++) {
                if (($row - $dataStartRow) % 2 === 0) {
                    $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['argb' => 'FFE8F5E9'],
                        ],
                    ]);
                }
            }
        }

        // Summary row
        $summaryRow = $sheet->getHighestDataRow() + 1;
        $totalAmountFormatted = 'Rp ' . number_format((float)$export->totalAmount, 0, ',', '.');

        $sheet->setCellValue('A' . $summaryRow, 'Total Transaksi');
        $sheet->setCellValue('B' . $summaryRow, $export->totalTransactionsCount);
        $sheet->setCellValue('F' . $summaryRow, 'Total Jumlah (Rp)');
        $sheet->setCellValue('G' . $summaryRow, $totalAmountFormatted);

        $sheet->getStyle('A' . $summaryRow . ':M' . $summaryRow)->applyFromArray([
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
        $sheet->getStyle('F' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}
