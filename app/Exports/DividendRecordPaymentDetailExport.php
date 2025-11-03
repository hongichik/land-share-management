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

class DividendRecordPaymentDetailExport implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithStyles, WithCustomStartCell
{
    protected $transferDate;
    protected $data;

    /**
     * DividendRecordPaymentDetailExport constructor
     *
     * @param string $transferDate
     */
    public function __construct($transferDate)
    {
        $this->transferDate = $transferDate;
        $this->data = $this->generatePaymentDetailData();
    }

    /**
     * Tạo dữ liệu cho danh sách chi tiết thanh toán cổ tức theo transfer_date
     * 
     * @return array
     */
    private function generatePaymentDetailData()
    {
        $paymentData = [];
        
        // Lấy tất cả bản ghi cổ tức của transfer_date được chọn
        $records = DividendRecord::with('dividend')
            ->where('transfer_date', $this->transferDate)
            ->whereIn('payment_status', ['paid_not_deposited', 'paid_both'])
            ->orderBy('created_at', 'asc')
            ->get();
        
        $index = 1;
        foreach ($records as $record) {
            $investor = $record->dividend;
            
            // Tính số tiền còn được lĩnh (tổng tiền - thuế)
            $totalBeforeTax = ($record->deposited_amount_before_tax ?? 0) + ($record->non_deposited_amount_before_tax ?? 0);
            $totalTax = ($record->deposited_personal_income_tax ?? 0) + ($record->non_deposited_personal_income_tax ?? 0);
            
            $paymentData[] = [
                $index, // A - STT
                $investor->full_name ?? '', // B - Họ và tên
                $investor->registration_number ?? '', // C - Số ĐK
                $investor->issue_date ? date('d/m/Y', strtotime($investor->issue_date)) : '', // D - Ngày cấp
                $investor->address ?? '', // E - Địa chỉ
                $totalBeforeTax, // F - Số tiền
                $totalTax, // G - Thuế TNCN
                '=F' . ($index + 6) . '-G' . ($index + 6), // H - Còn được lĩnh (công thức)
                $record->account_number ?? '', // I - Tài khoản
                $record->bank_name ?? '', // J - Ngân hàng
                $record->payment_date ? date('d/m/Y', strtotime($record->payment_date)) : '', // K - Thời gian trả cổ tức
                $record->transfer_date ? date('d/m/Y', strtotime($record->transfer_date)) : '', // L - Thời gian thanh toán tiền mặt (chưa LK)
            ];
            
            $index++;
        }
        
        return $paymentData;
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
        return 'Chi tiết thanh toán ' . date('d/m/Y', strtotime($this->transferDate));
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
            'L' => 20,     // Thời gian thanh toán tiền mặt (chưa LK)
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
        
        // Dòng 4: Tiêu đề danh sách (Hợp nhất A4:L4)
        $sheet->setCellValue('A4', 'CHI TIẾT THANH TOÁN CỔ TỨC - ' . date('d/m/Y', strtotime($this->transferDate)));
        $sheet->mergeCells('A4:L4');
        
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
        $sheet->setCellValue('L6', 'Thời gian thanh toán tiền mặt (chưa LK)');
        
        // Định dạng dòng 6 (tiêu đề)
        $sheet->getStyle('A6:L6')->applyFromArray([
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
        $sheet->getStyle('A6:L' . $lastRowWithData)->applyFromArray([
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
            
            // Cột B (Họ và tên) - Tự động xuống dòng
            $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            
            // Cột E (Địa chỉ) - Tự động xuống dòng
            $sheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            
            // Cột F (Số tiền) - Định dạng số, căn phải
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('0');
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            // Cột G (Thuế TNCN) - Định dạng số, căn phải
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('0');
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            // Cột H (Còn được lĩnh) - Định dạng số, căn phải
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('0');
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            // Cột J (Ngân hàng) - Tự động xuống dòng
            $sheet->getStyle('J' . $row)->getAlignment()->setWrapText(true);
        }
        
        // Dòng tổng (dòng sau dữ liệu cuối cùng)
        $totalRow = $lastRowWithData + 1;
        $sheet->setCellValue('B' . $totalRow, 'Tổng');
        $sheet->getStyle('B' . $totalRow)->getFont()->setBold(true);
        $sheet->getStyle('B' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Công thức SUM cho cột F (Số tiền)
        $sheet->setCellValue('F' . $totalRow, '=SUM(F7:F' . $lastRowWithData . ')');
        $sheet->getStyle('F' . $totalRow)->getFont()->setBold(true);
        $sheet->getStyle('F' . $totalRow)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('F' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Công thức SUM cho cột G (Thuế TNCN)
        $sheet->setCellValue('G' . $totalRow, '=SUM(G7:G' . $lastRowWithData . ')');
        $sheet->getStyle('G' . $totalRow)->getFont()->setBold(true);
        $sheet->getStyle('G' . $totalRow)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('G' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Công thức SUM cho cột H (Còn được lĩnh)
        $sheet->setCellValue('H' . $totalRow, '=SUM(H7:H' . $lastRowWithData . ')');
        $sheet->getStyle('H' . $totalRow)->getFont()->setBold(true);
        $sheet->getStyle('H' . $totalRow)->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle('H' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Định dạng viền cho dòng tổng
        $sheet->getStyle('A' . $totalRow . ':L' . $totalRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        // Dòng "Bằng chữ"
        $baEngChuRow = $totalRow + 1;
        $totalValue = $this->getTotalAmount();
        $sheet->setCellValue('B' . $baEngChuRow, 'Bằng chữ: ' . $this->convertNumberToWords($totalValue));
        $sheet->getStyle('B' . $baEngChuRow)->getFont()->setBold(true);
        
        // Dòng ngày tháng năm
        $dateRow = $baEngChuRow + 1;
        $sheet->setCellValue('H' . $dateRow, 'Quảng Ninh, Ngày     tháng     năm');
        $sheet->getStyle('H' . $dateRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        // Dòng chữ ký
        $signatureRow = $dateRow + 1;
        $sheet->setCellValue('B' . $signatureRow, 'Người lập');
        $sheet->setCellValue('D' . $signatureRow, 'Kế toán trưởng');
        $sheet->setCellValue('F' . $signatureRow, 'Kế toán trưởng');
        $sheet->setCellValue('H' . $signatureRow, 'Tổng giám đốc');
        
        // Định dạng dòng chữ ký
        $sheet->getStyle('B' . $signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F' . $signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H' . $signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        return $sheet;
    }
    
    /**
     * Tính tổng tiền từ dữ liệu
     * 
     * @return float
     */
    private function getTotalAmount()
    {
        $total = 0;
        foreach ($this->data as $row) {
            // Cột F (index 5) là tổng tiền
            $total += $row[5] ?? 0;
        }
        $total_ = 0;
        foreach ($this->data as $row) {
            // Cột F (index 5) là tổng tiền
            $total_ += $row[6] ?? 0;
        }
        $total= $total - $total_;
        return $total;
    }
    
    /**
     * Chuyển đổi số thành chữ
     * 
     * @param float $number
     * @return string
     */
    private function convertNumberToWords($number)
    {
        if ($number == 0) {
            return 'không';
        }
        
        $units = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ'];
        $words = [];
        
        // Đổi số thành chuỗi và xóa các ký tự không phải số
        $number = (string)$number;
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // Chia thành các nhóm 3 chữ số từ phải sang trái
        $groups = str_split(strrev($number), 3);
        
        foreach ($groups as $i => $group) {
            $group = strrev($group);
            $groupValue = (int)$group;
            
            if ($groupValue > 0) {
                $groupText = $this->readThreeDigits($groupValue);
                if ($i > 0) {
                    $groupText .= ' ' . $units[$i];
                }
                $words[] = $groupText;
            }
        }
        
        // Đảo ngược mảng để có thứ tự đúng
        $words = array_reverse($words);
        $result = implode(' ', $words);
        
        // Chuẩn hóa kết quả
        $result = $this->normalizeResult($result);
        
        return $result;
    }
    
    /**
     * Đọc ba chữ số
     * 
     * @param int $number
     * @return string
     */
    private function readThreeDigits($number)
    {
        $ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $teens = ['mười', 'mười một', 'mười hai', 'mười ba', 'mười bốn', 'mười năm', 'mười sáu', 'mười bảy', 'mười tám', 'mười chín'];
        $tens = ['', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
        
        $result = '';
        
        // Hàng trăm
        $hundreds = intdiv($number, 100);
        if ($hundreds > 0) {
            $result .= $ones[$hundreds] . ' trăm';
        }
        
        // Hàng chục và hàng đơn vị
        $remainder = $number % 100;
        if ($remainder >= 20) {
            if ($result) {
                $result .= ' ';
            }
            $tens_digit = intdiv($remainder, 10);
            $ones_digit = $remainder % 10;
            $result .= $tens[$tens_digit];
            if ($ones_digit > 0) {
                $result .= ' ' . $ones[$ones_digit];
            }
        } elseif ($remainder >= 10) {
            if ($result) {
                $result .= ' ';
            }
            $result .= $teens[$remainder - 10];
        } elseif ($remainder > 0) {
            if ($result) {
                $result .= ' ';
            }
            $result .= $ones[$remainder];
        }
        
        return $result;
    }
    
    /**
     * Chuẩn hóa kết quả chuyển đổi số sang chữ
     * 
     * @param string $text
     * @return string
     */
    private function normalizeResult($text)
    {
        // Xóa khoảng trắng thừa
        $text = trim(preg_replace('/\s+/', ' ', $text));
        
        return $text;
    }
}

