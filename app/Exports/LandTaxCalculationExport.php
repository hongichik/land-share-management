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

class LandTaxCalculationExport implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithStyles, WithCustomStartCell
{
    protected $period;
    protected $year;
    protected $data;

    /**
     * LandTaxCalculationExport constructor
     *
     * @param int $period
     * @param int $year
     */
    public function __construct($period, $year)
    {
        $this->period = $period;
        $this->year = $year;
        $this->data = $this->generateTaxCalculationData();
    }

    /**
     * Calculate period months for a contract's prices in a given year
     */
    private function calculatePeriodMonths($contract, $currentYear)
    {
        $period1Months = 0; // January - June
        $period2Months = 0; // July - December

        $period1Start = Carbon::create($currentYear, 1, 1);
        $period1End = Carbon::create($currentYear, 6, 30);
        $period2Start = Carbon::create($currentYear, 7, 1);
        $period2End = Carbon::create($currentYear, 12, 31);

        // Get prices for the current year
        $prices = $contract->landRentalPrices
            ->filter(function ($price) use ($currentYear) {
                $startYear = Carbon::parse($price->price_period['start'])->year;
                $endYear = Carbon::parse($price->price_period['end'])->year;
                return $startYear == $currentYear || $endYear == $currentYear;
            })
            ->sortBy('price_period.start');

        foreach ($prices as $price) {
            $priceStart = Carbon::parse($price->price_period['start']);
            $priceEnd = Carbon::parse($price->price_period['end']);

            // Kỳ 1 (January - June)
            $segmentStart1 = $priceStart->copy()->max($period1Start);
            $segmentEnd1 = $priceEnd->copy()->min($period1End);
            if ($segmentStart1 <= $segmentEnd1) {
                $current = $segmentStart1->copy()->startOfMonth();
                while ($current <= $segmentEnd1) {
                    // Calculate days in this month for this price period
                    $monthStart = max($segmentStart1, $current->copy()->startOfMonth());
                    $monthEnd = min($segmentEnd1, $current->copy()->endOfMonth());
                    $daysInMonth = $monthStart->diffInDays($monthEnd) + 1;
                    
                    // Count month if >= 15 days
                    if ($daysInMonth >= 15) {
                        $period1Months++;
                    }
                    
                    $current->addMonth();
                }
            }

            // Kỳ 2 (July - December)
            $segmentStart2 = $priceStart->copy()->max($period2Start);
            $segmentEnd2 = $priceEnd->copy()->min($period2End);
            if ($segmentStart2 <= $segmentEnd2) {
                $current = $segmentStart2->copy()->startOfMonth();
                while ($current <= $segmentEnd2) {
                    // Calculate days in this month for this price period
                    $monthStart = max($segmentStart2, $current->copy()->startOfMonth());
                    $monthEnd = min($segmentEnd2, $current->copy()->endOfMonth());
                    $daysInMonth = $monthStart->diffInDays($monthEnd) + 1;
                    
                    // Count month if >= 15 days
                    if ($daysInMonth >= 15) {
                        $period2Months++;
                    }
                    
                    $current->addMonth();
                }
            }
        }

        $currentMonths = $period1Months + $period2Months;

        return [
            'current_months' => $currentMonths,
            'period1_months' => $period1Months,
            'period2_months' => $period2Months,
            'prices' => $prices // Return prices for further use
        ];
    }

    /**
     * Generate data for tax calculation
     * 
     * @return array
     */
    private function generateTaxCalculationData()
    {
        $contracts = LandRentalContract::with(['landRentalPrices', 'paymentHistories'])->get();
        $taxCalculationData = [];
        $mainIndex = 1;
        
        foreach ($contracts as $contract) {
            $periods = $this->calculatePeriodMonths($contract, $this->year);
            $prices = $periods['prices'];
            
            if (!$contract->area || !isset($contract->area['value']) || $prices->isEmpty()) {
                continue;
            }
            
            $area = (float)$contract->area['value'];
            
            // Define period info based on selection
            $periodInfo = [
                'start' => $this->period == 1 ? Carbon::create($this->year, 1, 1) : Carbon::create($this->year, 7, 1),
                'end' => $this->period == 1 ? Carbon::create($this->year, 6, 30) : Carbon::create($this->year, 12, 31),
            ];
            
            $contractHasData = false;
            $subIndex = 1;
            
            foreach ($prices as $price) {
                $priceStart = Carbon::parse($price->price_period['start']);
                $priceEnd = Carbon::parse($price->price_period['end']);
                
                // Calculate intersection between price period and selected period
                $segmentStart = max($priceStart, $periodInfo['start']);
                $segmentEnd = min($priceEnd, $periodInfo['end']);
                
                $months = 0;
                if ($segmentStart <= $segmentEnd) {
                    $current = $segmentStart->copy()->startOfMonth();
                    while ($current <= $segmentEnd) {
                        // Calculate days in this month for this price period
                        $monthStart = max($segmentStart, $current->copy()->startOfMonth());
                        $monthEnd = min($segmentEnd, $current->copy()->endOfMonth());
                        $daysInMonth = $monthStart->diffInDays($monthEnd) + 1;
                        
                        // Count month if >= 15 days
                        if ($daysInMonth >= 15) {
                            $months++;
                        }
                        
                        $current->addMonth();
                    }
                }
                
                if ($months > 0 && $price->rental_price) {
                    // Calculate amounts for this price segment
                    $totalAmount = $area * $price->rental_price;
                    $periodAmount = ($totalAmount / 12) * $months;
                    
                    // Get paid amount for this contract and period
                    $paidAmount = $contract->paymentHistories
                        ->filter(function ($payment) {
                            return Carbon::parse($payment->payment_date)->year == $this->year 
                                && $payment->period == $this->period;
                        })
                        ->sum('amount');
                    
                    // For multiple prices in same contract, only show paid amount for first entry
                    $displayPaidAmount = $subIndex == 1 ? $paidAmount : 0;
                    $remainingAmount = $periodAmount - $displayPaidAmount;
                    
                    // Determine index display
                    $indexDisplay = $subIndex == 1 ? $mainIndex : $mainIndex . '.' . $subIndex;
                    
                    $taxCalculationData[] = [
                        'index' => $indexDisplay,
                        'location' => $contract->rental_location,
                        'area' => $area,
                        'unit_price' => (float)$price->rental_price,
                        'total_amount' => $totalAmount,
                        'months' => $months,
                        'period_amount' => $periodAmount,
                        'paid_amount' => $displayPaidAmount,
                        'remaining_amount' => $remainingAmount,
                        'notes' => $price->price_decision ?? ''
                    ];
                    
                    $contractHasData = true;
                    $subIndex++;
                }
            }
            
            if ($contractHasData) {
                $mainIndex++;
            }
        }
        
        return $taxCalculationData;
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
                (float)$row['total_amount'],
                (int)$row['months'], // New column
                (float)$row['period_amount'],
                (float)$row['paid_amount'],
                (float)$row['remaining_amount'],
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
        return 'Tiền thuê đất kỳ ' . $this->period . ' năm ' . $this->year;
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A9'; // Dời xuống dòng 9 vì header bắt đầu từ dòng 7 và 8
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
            'B' => 30,
            'C' => 15,
            'D' => 15,
            'E' => 20,
            'F' => 10, // New column: months
            'G' => 25,
            'H' => 25,
            'I' => 20,
            'J' => 20,
        ];
    }

    /**
     * Chuyển đổi số thành chữ tiếng Việt
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
     * Đọc nhóm 3 chữ số
     * 
     * @param int $number
     * @return string
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
     * 
     * @param string $result
     * @return string
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
        // Add title and header
        $sheet->mergeCells('A4:J4'); // Updated to include J column

        // Set title content
        $sheet->setCellValue('A1', 'CÔNG TY CỔ PHẦN');
        $sheet->setCellValue('A2', 'NHIỆT ĐIỆN QUẢNG NINH');
        $sheet->setCellValue('A4', 'BẢNG TÍNH TIỀN THUÊ ĐẤT PHẢI NỘP KỲ ' . $this->period . ' NĂM ' . $this->year);

        // Apply styles to headers
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11);
        
        // Chỉ căn giữa cho A4, không căn giữa A1 và A2
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set the first header row - row 7
        $firstHeaderRow = 7;
        $sheet->setCellValue('A'.$firstHeaderRow, 'Stt');
        $sheet->setCellValue('B'.$firstHeaderRow, 'Vị trí đất thuê');
        $sheet->setCellValue('C'.$firstHeaderRow, 'Diện tích (m2)');
        $sheet->setCellValue('D'.$firstHeaderRow, 'Đơn giá (đ/m2/năm)');
        $sheet->setCellValue('E'.$firstHeaderRow, 'Thành tiền (đồng)');
        $sheet->setCellValue('F'.$firstHeaderRow, 'Số tháng tính tiền');
        $sheet->setCellValue('G'.$firstHeaderRow, 'Số phải nộp kỳ ' . $this->period . '/' . $this->year . ' (đồng)');
        $sheet->setCellValue('H'.$firstHeaderRow, 'Số đã nộp/được miễn, giảm (đồng)');
        $sheet->setCellValue('I'.$firstHeaderRow, 'Số còn phải nộp (đồng)');
        $sheet->setCellValue('J'.$firstHeaderRow, 'Ghi chú');

        // Set column labels (A, B, (1), etc) - row 8
        $secondHeaderRow = 8;
        $sheet->setCellValue('A'.$secondHeaderRow, 'A');
        $sheet->setCellValue('B'.$secondHeaderRow, 'B');
        $sheet->setCellValue('C'.$secondHeaderRow, '(1)');
        $sheet->setCellValue('D'.$secondHeaderRow, '(2)');
        $sheet->setCellValue('E'.$secondHeaderRow, '(3)=(1)x(2)');
        $sheet->setCellValue('F'.$secondHeaderRow, '(4)');
        $sheet->setCellValue('G'.$secondHeaderRow, '(5)=((3)/12)x(4)');
        $sheet->setCellValue('H'.$secondHeaderRow, '(6)');
        $sheet->setCellValue('I'.$secondHeaderRow, '(7)=(5-6)');
        $sheet->setCellValue('J'.$secondHeaderRow, '(8)');

        // Style both header rows
        $sheet->getStyle('A'.$firstHeaderRow.':J'.$secondHeaderRow)->applyFromArray([
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

        // Apply height to header rows
        $sheet->getRowDimension($firstHeaderRow)->setRowHeight(30);
        $sheet->getRowDimension($secondHeaderRow)->setRowHeight(25);

        // Style the data rows
        $dataStartRow = 9;
        $dataEndRow = 8 + count($this->data);
        
        if ($dataEndRow >= $dataStartRow) {
            // Style all data cells
            $sheet->getStyle('A'.$dataStartRow.':J'.$dataEndRow)->applyFromArray([
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
            
            // Center align specific columns
            $sheet->getStyle('A'.$dataStartRow.':A'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$dataStartRow.':C'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F'.$dataStartRow.':F'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Right align number columns
            $sheet->getStyle('D'.$dataStartRow.':I'.$dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            // Format currency cells
            $sheet->getStyle('C'.$dataStartRow.':I'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
            
            // Add total row
            $totalRow = $dataEndRow + 1;
            $sheet->setCellValue('A'.$totalRow, 'Tổng');
            $sheet->mergeCells('A'.$totalRow.':E'.$totalRow);
            
            // Update formulas for the total row
            $sheet->setCellValue('F'.$totalRow, '=SUM(F'.$dataStartRow.':F'.$dataEndRow.')');
            $sheet->setCellValue('G'.$totalRow, '=SUM(G'.$dataStartRow.':G'.$dataEndRow.')');
            $sheet->setCellValue('H'.$totalRow, '=SUM(H'.$dataStartRow.':H'.$dataEndRow.')');
            $sheet->setCellValue('I'.$totalRow, '=SUM(I'.$dataStartRow.':I'.$dataEndRow.')');
            
            // Style total row
            $sheet->getStyle('A'.$totalRow.':J'.$totalRow)->applyFromArray([
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
            
            $sheet->getStyle('A'.$totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F'.$totalRow.':I'.$totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F'.$totalRow.':I'.$totalRow)->getNumberFormat()->setFormatCode('#,##0');
            
            // Thêm dòng bằng chữ
            $wordsRow = $totalRow + 1;
            $totalRemainingAmount = $sheet->getCell('I'.$totalRow)->getCalculatedValue();
            $amountInWords = $this->convertNumberToWords((int)$totalRemainingAmount) . ' đồng';
            
            $sheet->setCellValue('B'.$wordsRow, 'Bằng chữ:');
            $sheet->setCellValue('C'.$wordsRow, ucfirst($amountInWords));
            $sheet->mergeCells('C'.$wordsRow.':J'.$wordsRow);
            
            // Style dòng bằng chữ
            $sheet->getStyle('A'.$wordsRow.':J'.$wordsRow)->applyFromArray([
                'font' => ['bold' => true, 'italic' => true, 'size' => 11],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'F9F9F9',
                    ],
                ],
            ]);
            $sheet->getStyle('A'.$wordsRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B'.$wordsRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Add signature section - adjust row number
            $signatureRow = $wordsRow + 2;
            $sheet->setCellValue('A'.$signatureRow, 'Người lập bảng');
            $sheet->setCellValue('D'.$signatureRow, 'Kế toán trưởng');
            $sheet->setCellValue('G'.$signatureRow, 'Giám đốc');
            
            $sheet->mergeCells('A'.$signatureRow.':C'.$signatureRow);
            $sheet->mergeCells('D'.$signatureRow.':F'.$signatureRow);
            $sheet->mergeCells('G'.$signatureRow.':J'.$signatureRow);
            
            $sheet->getStyle('A'.$signatureRow.':J'.$signatureRow)->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle('A'.$signatureRow.':J'.$signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Add space for signatures
            $signatureSpaceRow = $signatureRow + 1;
            $sheet->setCellValue('A'.$signatureSpaceRow, '(Ký, họ tên)');
            $sheet->setCellValue('D'.$signatureSpaceRow, '(Ký, họ tên)');
            $sheet->setCellValue('G'.$signatureSpaceRow, '(Ký, họ tên, đóng dấu)');
            
            $sheet->mergeCells('A'.$signatureSpaceRow.':C'.$signatureSpaceRow);
            $sheet->mergeCells('D'.$signatureSpaceRow.':F'.$signatureSpaceRow);
            $sheet->mergeCells('G'.$signatureSpaceRow.':J'.$signatureSpaceRow);
            
            $sheet->getStyle('A'.$signatureSpaceRow.':J'.$signatureSpaceRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A'.$signatureSpaceRow.':J'.$signatureSpaceRow)->getFont()->setItalic(true)->setSize(11);
            
            // Add empty rows for actual signatures
            $sheet->getRowDimension($signatureRow + 2)->setRowHeight(40);
            $sheet->getRowDimension($signatureRow + 3)->setRowHeight(40);
        }

        return $sheet;
    }
}
