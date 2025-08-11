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
use Carbon\Carbon;
use App\Models\LandRentalContract;

class LandNonAgriTaxCalculationExport implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithStyles, WithCustomStartCell
{
    protected $year;
    protected $data;

    /**
     * LandNonAgriTaxCalculationExport constructor
     *
     * @param int $year
     */
    public function __construct($year)
    {
        $this->year = $year;
        $this->data = $this->generateTaxData();
    }

    /**
     * Tạo dữ liệu cho bảng tính thuế đất phi nông nghiệp
     * 
     * @return array
     */
    private function generateTaxData()
    {
        $contracts = LandRentalContract::with(['landRentalPrices'])->get();
        $taxData = [];
        
        foreach ($contracts as $index => $contract) {
            // Bỏ qua các hợp đồng không có giá hoặc diện tích hoặc thuế
            if (!$contract->area || !isset($contract->area['value']) || !$contract->export_tax || !$contract->land_tax_price) {
                continue;
            }
            
            // Lấy diện tích
            $area = (float)$contract->area['value'];
            
            // Lấy đơn giá thuế
            $taxPrice = (float)$contract->land_tax_price;
            
            // Lấy thuế suất (%) - đã được lưu dưới dạng thập phân (0.03), cần chuyển thành phần trăm (3%)
            $taxRate = (float)$contract->export_tax;
            
            // Số tháng tính thuế (thường là 12 tháng nếu hợp đồng có hiệu lực cả năm)
            $months = 12;
            
            // Nếu hợp đồng bắt đầu trong năm hiện tại, tính số tháng chính xác
            if ($contract->rental_period && isset($contract->rental_period['start_date'])) {
                $startDate = Carbon::parse($contract->rental_period['start_date']);

                if ($startDate->year == $this->year) {
                    $endOfYear = Carbon::createFromDate($this->year, 12, 31);
                    
                    // Tính số tháng đầy đủ
                    $fullMonths = $startDate->copy()->floorMonth()->diffInMonths($endOfYear->copy()->floorMonth());
                    
                    // Tính số ngày còn lại trong tháng cuối
                    $remainingDays = $startDate->day;
                    
                    // Áp dụng quy tắc làm tròn:
                    // - Nếu số ngày còn lại < 15, làm tròn nửa tháng
                    // - Nếu số ngày còn lại >= 15, làm tròn 1 tháng
                    if ($remainingDays < 15) {
                        $months = $fullMonths;
                    } else {
                        $months = $fullMonths + 1;
                    }
                }
            }
            
            // Xác định vị trí đất thuê
            $location = trim($contract->rental_zone . ' ' . $contract->rental_location);
            $location = $location ?: 'Chưa xác định';
            
            // Tính tiền thuế = (diện tích × đơn giá × thuế suất × số tháng) / 12
            $taxAmount = ($area * $taxPrice * ($taxRate) * $months)/12;

            $taxData[] = [
                'index' => $index + 1,
                'location' => $location,
                'area' => $area,
                'unit_price' => $taxPrice,
                'tax_rate' => $taxRate,
                'months' => $months,
                'amount' => $taxAmount,
                'notes' => ''
            ];
        }
        
        return $taxData;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $formattedData = [];
        
        foreach ($this->data as $row) {
            $formattedData[] = [
                $row['index'],
                $row['location'],
                (float)$row['area'],
                (float)$row['unit_price'],
                (float)$row['tax_rate'],
                (float)$row['months'],
                (float)$row['amount'],
                $row['notes']
            ];
        }
        
        return new Collection($formattedData);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Bảng tính thuế SDD PNN ' . $this->year;
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A9'; // Dời xuống dòng 9 theo yêu cầu
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 25,
            'C' => 15,
            'D' => 15,
            'E' => 12,
            'F' => 20,
            'G' => 20,
            'H' => 15,
        ];
    }

    /**
     * Chuyển đổi số thành chữ tiếng Việt
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
     * Đọc nhóm 3 chữ số
     */
    private function readThreeDigits($number)
    {
        $hundred = floor($number / 100);
        $ten = floor(($number % 100) / 10);
        $unit = $number % 10;
        
        $result = '';
        
        if ($hundred > 0) {
            $digits = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
            $result .= $digits[$hundred] . ' trăm';
            
            if ($ten > 0 || $unit > 0) {
                $result .= ' ';
            } else {
                return $result;
            }
        }
        
        if ($ten > 0) {
            if ($ten == 1) {
                $result .= 'mười';
            } else {
                $digits = ['', '', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
                $result .= $digits[$ten] . ' mươi';
            }
            
            if ($unit > 0) {
                $result .= ' ';
            } else {
                return $result;
            }
        } else if ($hundred > 0 && $unit > 0) {
            $result .= 'lẻ ';
        }
        
        if ($unit > 0) {
            if ($unit == 1 && $ten > 1) {
                $result .= 'mốt';
            } else if ($unit == 5 && $ten > 0) {
                $result .= 'lăm';
            } else {
                $digits = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
                $result .= $digits[$unit];
            }
        }
        
        return $result;
    }
    
    /**
     * Chuẩn hóa kết quả đầu ra
     */
    private function normalizeResult($result)
    {
        // Thay thế các khoảng trắng thừa
        $result = preg_replace('/\s+/', ' ', $result);
        $result = trim($result);
        
        // Viết hoa chữ cái đầu
        $result = ucfirst($result);
        
        return $result;
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Thiết lập tiêu đề
        $sheet->setCellValue('A1', 'CÔNG TY CỔ PHẦN');
        $sheet->setCellValue('A2', 'NHIỆT ĐIỆN QUẢNG NINH');
        // Dòng 3 để trống
        $sheet->setCellValue('A4', 'BẢNG TÍNH TIỀN THUẾ SỬ DỤNG ĐẤT PHẢI NỘP NĂM ' . $this->year);
        $sheet->mergeCells('A4:H4');
        
        // Định dạng tiêu đề
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
        
        // Chỉ căn giữa tiêu đề A4, không căn giữa A1 và A2
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Dòng 5 và 6 để trống

        // Thiết lập và định dạng hàng tiêu đề chính - dòng 7
        $firstHeaderRow = 7;
        $sheet->setCellValue('A'.$firstHeaderRow, 'Stt');
        $sheet->setCellValue('B'.$firstHeaderRow, 'Vị trí đất thuê');
        $sheet->setCellValue('C'.$firstHeaderRow, 'Diện tích (m2)');
        $sheet->setCellValue('D'.$firstHeaderRow, 'Đơn giá (đồng)');
        $sheet->setCellValue('E'.$firstHeaderRow, 'Thuế suất (%)');
        $sheet->setCellValue('F'.$firstHeaderRow, 'Tháng sử dụng (tháng)');
        $sheet->setCellValue('G'.$firstHeaderRow, 'Thành tiền');
        $sheet->setCellValue('H'.$firstHeaderRow, 'Ghi chú');

        // Thiết lập và định dạng hàng chú thích công thức - dòng 8
        $secondHeaderRow = 8;
        $sheet->setCellValue('A'.$secondHeaderRow, 'A');
        $sheet->setCellValue('B'.$secondHeaderRow, 'B');
        $sheet->setCellValue('C'.$secondHeaderRow, '(1)');
        $sheet->setCellValue('D'.$secondHeaderRow, '(2)');
        $sheet->setCellValue('E'.$secondHeaderRow, '(3)');
        $sheet->setCellValue('F'.$secondHeaderRow, '(4)');
        $sheet->setCellValue('G'.$secondHeaderRow, '(5)=(1x2x3x4)/12');
        
        // Định dạng cho cả hai hàng header
        $sheet->getStyle('A'.$firstHeaderRow.':H'.$secondHeaderRow)->applyFromArray([
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
        
        // Thiết lập chiều cao hàng tiêu đề
        $sheet->getRowDimension($firstHeaderRow)->setRowHeight(30);
        $sheet->getRowDimension($secondHeaderRow)->setRowHeight(25);
        
        // Định dạng dữ liệu
        $dataStartRow = 9; // Bắt đầu từ dòng 9 (sau 2 hàng header)
        $dataEndRow = 8 + count($this->data);
        
        if ($dataEndRow >= $dataStartRow) {
            // Định dạng tất cả các ô dữ liệu
            $sheet->getStyle('A'.$dataStartRow.':H'.$dataEndRow)->applyFromArray([
                'font' => ['size' => 11],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            
            // Căn giữa một số cột
            $sheet->getStyle('A'.$dataStartRow.':A'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$dataStartRow.':C'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E'.$dataStartRow.':E'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F'.$dataStartRow.':F'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Căn phải các cột số
            $sheet->getStyle('D'.$dataStartRow.':D'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G'.$dataStartRow.':G'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            // Định dạng số cho các cột số
            $sheet->getStyle('C'.$dataStartRow.':C'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('D'.$dataStartRow.':D'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('E'.$dataStartRow.':E'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('F'.$dataStartRow.':F'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0.0');
            $sheet->getStyle('G'.$dataStartRow.':G'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
            
            // Thêm dòng tổng cộng
            $totalRow = $dataEndRow + 1;
            $sheet->setCellValue('B'.$totalRow, 'Tổng');
            $sheet->setCellValue('G'.$totalRow, '=SUM(G'.$dataStartRow.':G'.$dataEndRow.')');
            
            // Định dạng dòng tổng
            $sheet->getStyle('A'.$totalRow.':H'.$totalRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'F2F2F2',
                    ],
                ],
            ]);
            
            $sheet->getStyle('B'.$totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('G'.$totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G'.$totalRow)->getNumberFormat()->setFormatCode('#,##0');
            
            // Đảm bảo dòng tổng sử dụng giá trị số
            $cellRef = 'G'.$totalRow;
            $sheet->getCell($cellRef)->setValueExplicit(
                $sheet->getCell($cellRef)->getCalculatedValue(),
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
            );

            // Thêm dòng bằng chữ
            $wordsRow = $totalRow + 1;
            $totalAmount = $sheet->getCell('G'.$totalRow)->getCalculatedValue();
            $amountInWords = $this->convertNumberToWords((int)$totalAmount) . ' đồng';
            
            $sheet->setCellValue('B'.$wordsRow, 'Bằng chữ:');
            $sheet->setCellValue('C'.$wordsRow, ucfirst($amountInWords));
            $sheet->mergeCells('C'.$wordsRow.':H'.$wordsRow);
            
            // Định dạng dòng bằng chữ
            $sheet->getStyle('A'.$wordsRow.':H'.$wordsRow)->applyFromArray([
                'font' => ['italic' => true, 'size' => 11],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
            
            // Thêm phần người lập và phòng tài chính - để trống 1 dòng
            $signatureRow = $wordsRow + 2;
            $sheet->setCellValue('B'.$signatureRow, 'Người lập');
            $sheet->setCellValue('D'.$signatureRow, 'Kế toán trưởng');
            $sheet->setCellValue('G'.$signatureRow, 'Tổng giám đốc');
            
            $sheet->getStyle('B'.$signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G'.$signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$signatureRow.':G'.$signatureRow)->getFont()->setBold(true);
            
            // Thêm khoảng trống cho chữ ký
            $sheet->getRowDimension($signatureRow + 1)->setRowHeight(40);
            $sheet->getRowDimension($signatureRow + 2)->setRowHeight(40);
        }

        return $sheet;
    }
}
