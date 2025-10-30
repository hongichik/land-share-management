<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Models\DividendRecord;
use App\Models\SecuritiesManagement;

class DividendRecordExport implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithStyles, WithCustomStartCell
{
    protected $year;
    protected $data;

    /**
     * DividendRecordExport constructor
     *
     * @param int $year
     */
    public function __construct($year)
    {
        $this->year = $year;
        $this->data = $this->generateDividendData();
    }

    /**
     * Tạo dữ liệu cho danh sách cổ đông nhận cổ tức
     * 
     * @return array
     */
    private function generateDividendData()
    {
        $dividendData = [];
        
        // Lấy tất cả bản ghi cổ tức của năm được chọn
        $records = DividendRecord::with('securitiesManagement')
            ->whereYear('payment_date', $this->year)
            ->orderBy('payment_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $index = 1;
        foreach ($records as $record) {
            $investor = $record->securitiesManagement;
            
            // Tính số tiền còn được lĩnh (tổng tiền - thuế)
            $totalBeforeTax = ($record->deposited_amount_before_tax ?? 0) + ($record->non_deposited_amount_before_tax ?? 0);
            $totalTax = ($record->deposited_personal_income_tax ?? 0) + ($record->non_deposited_personal_income_tax ?? 0);
            $totalAfterTax = $totalBeforeTax - $totalTax;
            
            $dividendData[] = [
                $index, // A - STT
                $investor->full_name ?? '', // B - Họ và tên
                $investor->registration_number ?? '', // C - Số ĐK
                $investor->issue_date ? date('d/m/Y', strtotime($investor->issue_date)) : '', // D - Ngày cấp
                $investor->address ?? '', // E - Địa chỉ
                $totalBeforeTax, // F - Số tiền
                $totalTax, // G - Thuế TNCN
                $totalAfterTax, // H - Còn được lĩnh
                $record->account_number ?? '', // I - Tài khoản
                $record->bank_name ?? '', // J - Ngân hàng
                $record->payment_date ? date('d/m/Y', strtotime($record->payment_date)) : '', // K - Thời gian trả cổ tức
            ];
            
            $index++;
        }
        
        return $dividendData;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection($this->data);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Danh sách cổ đông ' . $this->year;
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A7'; // Bắt đầu từ dòng 7 (dòng 6 là header)
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,      // STT
            'B' => 25,     // Họ và tên
            'C' => 18,     // Số ĐK
            'D' => 15,     // Ngày cấp
            'E' => 30,     // Địa chỉ
            'F' => 18,     // Số tiền
            'G' => 15,     // Thuế TNCN
            'H' => 18,     // Còn được lĩnh
            'I' => 20,     // Tài khoản
            'J' => 20,     // Ngân hàng
            'K' => 18,     // Thời gian trả cổ tức
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Dòng 1: Tiêu đề công ty (Hợp nhất A1:C1)
        $sheet->setCellValue('A1', 'CÔNG TY CỔ PHẦN');
        $sheet->mergeCells('A1:C1');
        
        // Định dạng dòng 1
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        // Dòng 2: Tên công ty (Hợp nhất A2:C2)
        $sheet->setCellValue('A2', 'NHIỆT ĐIỆN QUẢNG NINH');
        $sheet->mergeCells('A2:C2');
        
        // Định dạng dòng 2
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(25);
        
        // Dòng 3: Để trống
        $sheet->getRowDimension(3)->setRowHeight(15);
        
        // Dòng 4: Tiêu đề danh sách (Hợp nhất A4:K4)
        $sheet->setCellValue('A4', 'DANH SÁCH CỔ ĐÔNG NHẬN CỔ TỨC NĂM ' . $this->year);
        $sheet->mergeCells('A4:K4');
        
        // Định dạng dòng 4
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(4)->setRowHeight(25);
        
        // Dòng 5: Để trống
        $sheet->getRowDimension(5)->setRowHeight(15);
        
        // Dòng 6: Hàng tiêu đề chính
        $sheet->setCellValue('A6', 'STT');
        $sheet->setCellValue('B6', 'Họ và tên');
        $sheet->setCellValue('C6', 'Số ĐK');
        $sheet->setCellValue('D6', 'Ngày cấp');
        $sheet->setCellValue('E6', 'Địa chỉ');
        $sheet->setCellValue('F6', 'Số tiền');
        $sheet->setCellValue('G6', 'Thuế TNCN');
        $sheet->setCellValue('H6', 'Còn được lĩnh');
        $sheet->setCellValue('I6', 'Tài khoản');
        $sheet->setCellValue('J6', 'Ngân hàng');
        $sheet->setCellValue('K6', 'Thời gian trả cổ tức');
        
        // Định dạng dòng 6 (tiêu đề)
        $sheet->getStyle('A6:K6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E2EFDA',
                ],
            ],
        ]);
        
        $sheet->getRowDimension(6)->setRowHeight(30);
        
        // Lấy số dòng dữ liệu cuối cùng
        $lastRowWithData = 6 + count($this->data);
        
        // Áp dụng định dạng viền, căn chỉnh và font cho tất cả dòng dữ liệu (từ dòng 6 trở xuống)
        $sheet->getStyle('A6:K' . $lastRowWithData)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        // Căn chỉnh cho các cột
        for ($row = 7; $row <= $lastRowWithData; $row++) {
            // Cột A (STT) - Căn giữa
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Cột F (Số tiền) - Định dạng số, căn phải
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            // Cột G (Thuế TNCN) - Định dạng số, căn phải
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            // Cột H (Còn được lĩnh) - Định dạng số, căn phải
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
        
        return $sheet;
    }
}
