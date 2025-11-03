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
use App\Models\SecuritiesManagement;

class LargeShareholderExport implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithStyles, WithCustomStartCell
{
    protected $data;
    protected $totalShares;

    /**
     * LargeShareholderExport constructor
     */
    public function __construct()
    {
        // Lấy tổng số cổ phần
        $totalStats = SecuritiesManagement::selectRaw('
            SUM(deposited_quantity) as total_deposited,
            SUM(not_deposited_quantity) as total_not_deposited
        ')->first();

        $this->totalShares = ($totalStats->total_deposited ?? 0) + ($totalStats->total_not_deposited ?? 0);
        
        $this->data = $this->generateLargeShareholderData();
    }

    /**
     * Tạo dữ liệu cho danh sách cổ đông lớn (>= 5%)
     * 
     * @return array
     */
    private function generateLargeShareholderData()
    {
        $largeShareholderData = [];
        
        // Lấy tất cả cổ đông
        $shareholders = SecuritiesManagement::all();
        
        $index = 1;
        foreach ($shareholders as $shareholder) {
            $totalShares = ($shareholder->deposited_quantity ?? 0) + ($shareholder->not_deposited_quantity ?? 0);
            
            // Tính tỷ lệ cổ phần
            $percentage = 0;
            if ($this->totalShares > 0) {
                $percentage = ($totalShares / $this->totalShares) * 100;
            }
            
            // Chỉ thêm nếu >= 5%
            if ($percentage >= 5) {
                $largeShareholderData[] = [
                    $index, // A - STT
                    $shareholder->full_name ?? '', // B - Họ và tên
                    $shareholder->registration_number ?? '', // C - Số ĐK
                    $shareholder->address ?? '', // D - Địa chỉ
                    $shareholder->not_deposited_quantity ?? 0, // E - Chưa lưu ký
                    $shareholder->deposited_quantity ?? 0, // F - Đã lưu ký
                    $shareholder->issue_date ? date('d/m/Y', strtotime($shareholder->issue_date)) : '', // G - Ngày cấp
                    $shareholder->email ?? '', // H - Gmail
                    $shareholder->phone ?? '', // I - Số điện thoại
                ];
                
                $index++;
            }
        }
        
        return $largeShareholderData;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection($this->data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'STT',
            'Họ và tên',
            'Số ĐK',
            'Địa chỉ',
            'Chưa LK',
            'Đã LK',
            'Ngày cấp',
            'Gmail',
            'Số điện thoại',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Cổ đông lớn';
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A5';
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 25,
            'C' => 15,
            'D' => 30,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 25,
            'I' => 15,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Dòng 1: CÔNG TY CỔ PHẦN
        $sheet->setCellValue('A1', 'CÔNG TY CỔ PHẦN');
        $sheet->mergeCells('A1:C1');
        
        // Định dạng dòng 1
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        // Dòng 2: Tên công ty (Hợp nhất A2:G2)
        $sheet->setCellValue('A2', 'NHIỆT ĐIỆN QUẢNG NINH');
        $sheet->mergeCells('A2:C2');
        
        // Định dạng dòng 2
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(25);
        
        // Dòng 3: Để trống
        $sheet->getRowDimension(3)->setRowHeight(15);

        // Dòng 4: Tiêu đề của bảng
        $sheet->setCellValue('A4', 'DANH SÁCH CỔ ĐÔNG LỚN');
        $sheet->mergeCells('A4:I4');
        
        // Định dạng dòng 4
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // Định dạng header row (dòng 5)
        $headerRange = 'A5:I5';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11)->setColor(
            new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF')
        );
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setWrapText(true);
        
        // Đặt màu nền cho header
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle($headerRange)->getFill()->getStartColor()->setARGB('FF4472C4');
        
        // Thêm border cho header
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle($headerRange)->applyFromArray($borderStyle);
        $sheet->getRowDimension(5)->setRowHeight(25);

        // Định dạng dữ liệu
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 5) {
            $dataRange = 'A6:I' . $highestRow;
            $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($dataRange)->applyFromArray($borderStyle);
            
            // Định dạng các ô số
            $sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E6:F' . $highestRow)->getNumberFormat()->setFormatCode('0');
        }

        // Nội dung tự động xuống dòng nếu như nội dung dài cho tất cả các cột
        $sheet->getStyle('A')->getAlignment()->setWrapText(true);
        $sheet->getStyle('B')->getAlignment()->setWrapText(true);
        $sheet->getStyle('C')->getAlignment()->setWrapText(true);
        $sheet->getStyle('D')->getAlignment()->setWrapText(true);
        $sheet->getStyle('E')->getAlignment()->setWrapText(true);
        $sheet->getStyle('F')->getAlignment()->setWrapText(true);
        $sheet->getStyle('G')->getAlignment()->setWrapText(true);
        $sheet->getStyle('H')->getAlignment()->setWrapText(true);
        $sheet->getStyle('I')->getAlignment()->setWrapText(true);

        return [];
    }
}
