<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Transaction;

class TransactionsExport implements WithTitle, WithStyles
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function title(): string
    {
        return 'Transactions Report';
    }

    public function styles(Worksheet $sheet)
    {
        // Set the 'Laporan Dibuat' with date on the right (italic)
        $sheet->setCellValue('A1', 'Laporan Dibuat: ' . date('Y-m-d H:i:s'));
        $sheet->getStyle('A1')->getFont()->setItalic(true); // Make it italic
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Align it to the right
    
        // Set title and center it
        $sheet->setCellValue('A3', 'Transaction Report');
        $sheet->mergeCells('A3:G3'); // Merge cells for the title
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14); // Bold and larger font for the title
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Center the title
    
        // Column headers
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Outlet Name');
        $sheet->setCellValue('C5', 'Amount');
        $sheet->setCellValue('D5', 'Time');
        $sheet->setCellValue('E5', 'RFID');
        $sheet->setCellValue('F5', 'Status');
        $sheet->setCellValue('G5', 'Secret Code');
    
        // Merge header cells and format headers
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('B5:G5')->getFont()->setBold(true)->setSize(12); // Bold font for headers
        $sheet->getStyle('A5:G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Center headers
        $sheet->getStyle('A5:G5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN); // Borders for headers
    
        // Set column widths for better readability
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setWidth(20); // Adjust column width
        }
    
        // Add transaction data starting from row 6
        $row = 6;
        foreach ($this->transactions as $index => $transaction) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $transaction->outlet->name ?? 'Unknown');
            $sheet->setCellValue('C' . $row, number_format($transaction->amount, 2, '.', ''));
            $sheet->setCellValue('D' . $row, $transaction->time);
            $sheet->setCellValue('E' . $row, $transaction->rfid);
            $sheet->setCellValue('F' . $row, $transaction->status);
            $sheet->setCellValue('G' . $row, $transaction->secret_code);
            $row++;
        }
    
        // Apply border to the entire data section
        $sheet->getStyle('A5:G' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
    
}
