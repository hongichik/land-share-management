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

class LandRentalPlanExport implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithStyles, WithCustomStartCell
{
    protected $year;
    protected $data;

    /**
     * LandRentalPlanExport constructor
     *
     * @param int $year
     */
    public function __construct($year)
    {
        $this->year = $year;
        $this->data = $this->generatePlanData();
    }

    /**
     * Tạo dữ liệu cho kế hoạch thuê đất
     * 
     * @return array
     */
    private function generatePlanData()
    {
        $contracts = LandRentalContract::with(['landRentalPrices'])->get();

        $planData = [];
        $contractIndex = 1;
        
        foreach ($contracts as $contract) {
            // Bỏ qua các hợp đồng không có giá hoặc diện tích
            if (!$contract->area || !isset($contract->area['value'])) {
                continue;
            }
            
            // Lấy tất cả giá thuê hiệu lực cho năm hiện tại
            $startOfYear = Carbon::createFromDate($this->year, 1, 1);
            $endOfYear = Carbon::createFromDate($this->year, 12, 31);
            
            $applicablePrices = $contract->landRentalPrices
                ->filter(function ($price) use ($startOfYear, $endOfYear) {
                    $start = Carbon::parse($price->price_period['start']);
                    $end = Carbon::parse($price->price_period['end']);
                    
                    // Kiểm tra nếu khoảng thời gian giá thuê trùng với năm được chọn
                    return $start->lessThanOrEqualTo($endOfYear) && $end->greaterThanOrEqualTo($startOfYear);
                })
                ->sortBy('price_period.start');
                
            if ($applicablePrices->isEmpty()) {
                continue;
            }
            
            $area = (float)$contract->area['value'];
            $purpose = $contract->rental_purpose ?: 'Chưa xác định';
            
            // Nếu chỉ có 1 mức giá, hiển thị bình thường
            if ($applicablePrices->count() == 1) {
                $price = $applicablePrices->first();
                $rentalPrice = (float)$price->rental_price;
                
                // Tính số tháng cho mức giá này trong năm
                $months = $this->calculateMonthsForPricePeriod($price, $startOfYear, $endOfYear);

                $planData[] = [
                    'index' => $contractIndex,
                    'purpose' => $purpose,
                    'contract' => $contract->contract_number,
                    'area' => $area,
                    'unit_price' => $rentalPrice,
                    'months' => $months,
                    'amount' => null, // Để Excel tính công thức
                    'notes' => $this->formatPricePeriod($price, $startOfYear, $endOfYear)
                ];
            } else {
                // Nếu có nhiều mức giá, hiển thị với sub-index
                $subIndex = 0;
                
                foreach ($applicablePrices as $price) {
                    $rentalPrice = (float)$price->rental_price;
                    
                    // Tính số tháng cho mức giá này trong năm
                    $months = $this->calculateMonthsForPricePeriod($price, $startOfYear, $endOfYear);
                    
                    if ($months > 0) {
                        // Tạo index với sub-index
                        $displayIndex = $subIndex == 0 ? $contractIndex : $contractIndex . '.' . $subIndex.' ';
                        
                        $planData[] = [
                            'index' => $displayIndex,
                            'purpose' => $subIndex == 0 ? $purpose : '', // Chỉ hiển thị mục đích ở dòng đầu
                            'contract' => $subIndex == 0 ? $contract->contract_number : '', // Chỉ hiển thị số HĐ ở dòng đầu
                            'area' => $area, // Hiển thị diện tích ở tất cả các dòng để công thức hoạt động
                            'unit_price' => $rentalPrice,
                            'months' => $months,
                            'amount' => null, // Để Excel tính công thức
                            'notes' => $this->formatPricePeriod($price, $startOfYear, $endOfYear)
                        ];
                        
                        $subIndex++;
                    }
                }
            }
            
            $contractIndex++;
        }
        
        return $planData;
    }

    /**
     * Tính số tháng cho một khoảng thời gian giá cụ thể trong năm
     */
    private function calculateMonthsForPricePeriod($price, $yearStart, $yearEnd)
    {
        $priceStart = Carbon::parse($price->price_period['start']);
        $priceEnd = Carbon::parse($price->price_period['end']);
        
        // Lấy giao điểm giữa khoảng thời gian giá và năm được chọn
        $segmentStart = $priceStart->copy()->max($yearStart);
        $segmentEnd = $priceEnd->copy()->min($yearEnd);
        
        if ($segmentStart->greaterThan($segmentEnd)) {
            return 0;
        }
        
        // Simplified month calculation
        $startMonth = $segmentStart->month;
        $startYear = $segmentStart->year;
        $endMonth = $segmentEnd->month;
        $endYear = $segmentEnd->year;
        
        // If start day >= 15, don't count start month
        if ($segmentStart->day >= 15) {
            if ($startMonth == 12) {
                $startMonth = 1;
                $startYear++;
            } else {
                $startMonth++;
            }
        }
        
        // If end day < 15, don't count end month
        if ($segmentEnd->day < 15) {
            if ($endMonth == 1) {
                $endMonth = 12;
                $endYear--;
            } else {
                $endMonth--;
            }
        }
        
        // Calculate months
        $months = 0;
        if ($startYear < $endYear || ($startYear == $endYear && $startMonth <= $endMonth)) {
            $months = ($endYear - $startYear) * 12 + ($endMonth - $startMonth) + 1;
        }
        
        return $months;
    }

    /**
     * Format khoảng thời gian giá cho ghi chú
     */
    private function formatPricePeriod($price, $yearStart, $yearEnd)
    {
        $priceStart = Carbon::parse($price->price_period['start']);
        $priceEnd = Carbon::parse($price->price_period['end']);
        
        // Lấy giao điểm giữa khoảng thời gian giá và năm được chọn
        $segmentStart = $priceStart->copy()->max($yearStart);
        $segmentEnd = $priceEnd->copy()->min($yearEnd);
        
        $timePeriod = $segmentStart->format('d/m/Y') . ' - ' . $segmentEnd->format('d/m/Y');
        
        $notes = [];
        if ($price->price_decision) {
            $notes[] = $price->price_decision;
        }
        $notes[] = 'Áp dụng: ' . $timePeriod;
        
        return implode('; ', $notes);
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
                $row['purpose'],
                $row['contract'],
                $row['area'] !== '' ? (float)$row['area'] : '',
                (float)$row['unit_price'],
                (float)$row['months'],
                null, // Để Excel tính công thức
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
        return 'Kế hoạch thuê đất ' . $this->year;
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A7'; // Dời xuống dòng 7 theo yêu cầu
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Stt',
            'Mục đích thuê',
            'Số Hợp đồng',
            'Diện tích (m2)',
            'Đơn giá (đồng)',
            'Số tháng sử dụng (tháng)',
            'Số tiền (đồng)',
            ''
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
            'C' => 25,
            'D' => 15,
            'E' => 15,
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
        $sheet->setCellValue('A1', 'CÔNG TY CỔ PHẦN NHIỆT ĐIỆN QUẢNG NINH');
        // Dòng 2 và 3 để trống
        $sheet->setCellValue('A4', 'KẾ HOẠCH NỘP TIỀN THUÊ ĐẤT NĂM ' . $this->year);
        $sheet->mergeCells('A4:H4');
        
        // Định dạng tiêu đề
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11);
        
        // Chỉ căn giữa tiêu đề A4, không căn giữa A1
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Dòng 5 để trống

        // Thiết lập và định dạng hàng tiêu đề chính - dòng 6
        $firstHeaderRow = 6;
        $sheet->setCellValue('A'.$firstHeaderRow, 'Stt');
        $sheet->setCellValue('B'.$firstHeaderRow, 'Mục đích thuê');
        $sheet->setCellValue('C'.$firstHeaderRow, 'Số Hợp đồng');
        $sheet->setCellValue('D'.$firstHeaderRow, 'Diện tích (m2)');
        $sheet->setCellValue('E'.$firstHeaderRow, 'Đơn giá (đồng)');
        $sheet->setCellValue('F'.$firstHeaderRow, 'Số tháng sử dụng (tháng)');
        $sheet->setCellValue('G'.$firstHeaderRow, 'Số tiền (đồng)');
        $sheet->setCellValue('H'.$firstHeaderRow, 'Ghi chú');

        // Thiết lập và định dạng hàng chú thích công thức - dòng 7
        $secondHeaderRow = 7;
        $sheet->setCellValue('A'.$secondHeaderRow, '');
        $sheet->setCellValue('B'.$secondHeaderRow, '');
        $sheet->setCellValue('C'.$secondHeaderRow, '');
        $sheet->setCellValue('D'.$secondHeaderRow, '(1)');
        $sheet->setCellValue('E'.$secondHeaderRow, '(2)');
        $sheet->setCellValue('F'.$secondHeaderRow, '(3)');
        $sheet->setCellValue('G'.$secondHeaderRow, '(4)=(1x2x3)/12');
        
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
        $dataStartRow = 8; // Bắt đầu từ dòng 8 (sau 2 hàng header)
        $dataEndRow = 7 + count($this->data);
        
        if ($dataEndRow >= $dataStartRow) {
            // Thêm công thức Excel cho cột G (Số tiền)
            for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                $sheet->setCellValue('G'.$row, '=(D'.$row.' * E'.$row.' * F'.$row.') / 12');
            }
            
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
            $sheet->getStyle('D'.$dataStartRow.':D'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F'.$dataStartRow.':F'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Căn phải các cột số
            $sheet->getStyle('E'.$dataStartRow.':E'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G'.$dataStartRow.':G'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            // Định dạng số cho các cột số
            $sheet->getStyle('D'.$dataStartRow.':D'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('E'.$dataStartRow.':E'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
            // Format để hiển thị số thập phân với dấu phẩy (VD: 11,5)
            $sheet->getStyle('F'.$dataStartRow.':F'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0.0');
            $sheet->getStyle('G'.$dataStartRow.':G'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
            
            // Thêm dòng tổng cộng
            $totalRow = $dataEndRow + 1;
            $sheet->setCellValue('B'.$totalRow, 'Tổng cộng');
            // Công thức tổng tất cả các giá trị từ cột G
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

            // Thêm dòng bằng chữ
            $wordsRow = $totalRow + 1;
            // Lấy công thức từ ô tổng để tính giá trị
            $totalFormula = $sheet->getCell('G'.$totalRow)->getValue();
            $calculatedTotal = $sheet->getCell('G'.$totalRow)->getCalculatedValue();
            $amountInWords = $this->convertNumberToWords((int)$calculatedTotal) . ' đồng';
            
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
            $sheet->setCellValue('F'.$signatureRow, 'Phòng Tài chính và kế toán');
            
            $sheet->getStyle('B'.$signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F'.$signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$signatureRow.':F'.$signatureRow)->getFont()->setBold(true);
            
            // Thêm khoảng trống cho chữ ký
            $sheet->getRowDimension($signatureRow + 1)->setRowHeight(40);
            $sheet->getRowDimension($signatureRow + 2)->setRowHeight(40);
        }

        return $sheet;
    }
}