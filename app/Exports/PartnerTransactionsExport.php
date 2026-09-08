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

class PartnerTransactionsExport implements
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
    protected $totalTransactions;
    protected $brandName; // Added brandName property
    protected $summaryEnabled;

    public function __construct($transactions, $startDate = null, $endDate = null, $totalAmount = 0, $totalTransactions = 0, $brandName = null, $summaryEnabled = true) // Added $brandName parameter
    {
        $this->transactions = collect($transactions)->values()->map(function ($item, $key) {
            $item->no = $key + 1;
            return $item;
        });

        $this->startDate = $startDate ? Carbon::parse($startDate) : null;
        $this->endDate = $endDate ? Carbon::parse($endDate) : null;
        $this->totalAmount = $totalAmount;
        $this->totalTransactions = $totalTransactions;
        $this->brandName = $brandName; // Set brandName
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
            'Outlet',
            'Jumlah (Rp)',
            'Tipe Pembayaran',
            'Status',
            'Payment Method / Provider',
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
        $paymentMethodText = match (optional($transaction)->type) {
            'manual' => optional(optional($transaction)->manualTransaction)->payment_method === 'cash'
                ? 'Tunai'
                : (optional(optional($transaction)->manualTransaction)->payment_method === 'non_cash'
                    ? 'Transfer'
                    : ucfirst(optional(optional($transaction)->manualTransaction)->payment_method ?? '')),
            'qris' => (string) optional($transaction)->qris_provider_label,
            default => ucfirst(optional($transaction)->type ?? ''),
        };

        return [
            (string)optional($transaction)->no ?? 'N/A',
            (string)optional($transaction)->order_id ?? 'N/A',
            (string)optional(optional($transaction)->outlet)->outlet_name ?? 'N/A',
            $formattedAmount,
            (string)(optional($transaction)->type === 'manual' ? 'Drop Off' : ucfirst(optional($transaction)->type ?? '')) ?? 'N/A',
            (string)ucfirst(optional($transaction)->status ?? '') ?? 'N/A',
            $paymentMethodText !== '' ? $paymentMethodText : 'N/A',
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

        // --- Header: Title + Date ---
        $startDateFormatted = $export->startDate ? $export->startDate->isoFormat('D MMMM YYYY') : 'N/A';
        $endDateFormatted = $export->endDate ? $export->endDate->isoFormat('D MMMM YYYY') : 'N/A';
        $dateRangeText = "Tanggal : {$startDateFormatted} - {$endDateFormatted}";

        // Insert 2 new rows before the original row 1 (for title and date)
        $sheet->insertNewRowBefore(1, 2);

        // Title (Row 1)
        $sheet->setCellValue('A1', 'REPORT TRANSACTION');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
                'color' => ['argb' => 'FF111111'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Date (Row 2) + Brand (optional)
        if (!empty($export->brandName)) {
            $sheet->setCellValue('A2', 'Brand: ' . $export->brandName);
            $sheet->mergeCells('A2:C2');
            $sheet->getStyle('A2:C2')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                    'color' => ['argb' => 'FF333333'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            $sheet->setCellValue('D2', $dateRangeText);
            $sheet->mergeCells('D2:H2');
            $sheet->getStyle('D2:H2')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                    'color' => ['argb' => 'FF333333'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        } else {
            $sheet->setCellValue('A2', $dateRangeText);
            $sheet->mergeCells('A2:H2');
            $sheet->getStyle('A2:H2')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                    'color' => ['argb' => 'FF333333'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }
        // --- End of Header Info Section ---

        // --- Bagian untuk Total Amount dan Total Transaksi di Bawah Data ---
        if (!$export->summaryEnabled) {
            return;
        }
        // highestDataRow will give the last row containing transaction data.
        // The summary row will be one row after the last data row.
        $summaryRow = $sheet->getHighestDataRow() + 1;

        // Format total amount as string, consistent with individual amounts
        $totalAmountFormatted = 'Rp ' . number_format((float)$export->totalAmount, 0, ',', '.');

        // Write 'Total Transaksi' label and its count in column A and B
        $sheet->setCellValue('A' . $summaryRow, 'Total Transaksi');
        $sheet->setCellValue('B' . $summaryRow, $export->totalTransactions);

        // Write 'Total Jumlah (Rp)' label and the formatted total amount in column C and D
        $sheet->setCellValue('C' . $summaryRow, 'Total Jumlah (Rp)');
        $sheet->setCellValue('D' . $summaryRow, $totalAmountFormatted);

        // Apply styling to the summary row
        $sheet->getStyle('A' . $summaryRow . ':H' . $summaryRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_MEDIUM, // Medium border on top
                    'color' => ['argb' => 'FF000000'],
                ],
                'bottom' => [ // Optional: Add a bottom border to close the footer section
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFEEEEEE'], // Light gray background
            ],
        ]);

        // Align the labels and values in the summary row
        $sheet->getStyle('A' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Align 'Total Transaksi' label right
        $sheet->getStyle('B' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Center 'Total Transaksi' count
        $sheet->getStyle('C' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Align 'Total Jumlah (Rp)' label right
        $sheet->getStyle('D' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Align 'Total Jumlah (Rp)' value right

        // --- End of Total Amount and Total Transaksi Section ---
    }

    public function styles(Worksheet $sheet)
    {
        // Because 2 rows are inserted at the top:
        // - Header is now on ROW 3
        // - Data starts from ROW 4
        // $highestDataRow will correctly give the last row of actual data,
        // before the total rows are added by afterSheet event.
        $highestDataRow = $sheet->getHighestDataRow();

        // Style header row (now on row 3)
        $sheet->getStyle('A3:' . $sheet->getHighestColumn() . '3')->applyFromArray([
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

        // Style data rows (now from row 4 onwards, up to the last data row)
        $sheet->getStyle('A4:' . $sheet->getHighestColumn() . $highestDataRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFDDDDDD'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);

        // Center align specific columns (now from row 4 onwards)
        // 'No.' (A) and Waktu (H)
        $sheet->getStyle('A4:A' . $highestDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H4:H' . $highestDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Right align 'Jumlah (Rp)' column (now column D, from row 4 onwards)
        $sheet->getStyle('D4:D' . $highestDataRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        // Alternating row colors (now from row 4 onwards)
        for ($row = 4; $row <= $highestDataRow; $row++) {
            if ($row % 2 === 0) { // Even rows (since 4 is the first)
                $sheet->getStyle('A' . $row . ':' . $sheet->getHighestColumn() . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFE8F5E9'],
                    ],
                ]);
            }
        }

        return [];
    }
}
