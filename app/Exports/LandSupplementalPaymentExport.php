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

class LandSupplementalPaymentExport implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithStyles, WithCustomStartCell
{
    protected $year;
    protected $data;
    protected $oldPriceDate;
    protected $newPriceDate;
    protected $old_price_decision;
    protected $new_price_decision;

    /**
     * LandSupplementalPaymentExport constructor
     *
     * @param int $year
     */
    public function __construct($year)
    {
        $this->year = $year;
        $this->data = $this->generateSupplementalPaymentData();


    }

    /**
     * Calculate months rounded based on remaining days in month
     * Days < 15 rounds down, >= 15 rounds up
     */
    private function calculateMonths($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $months = $start->diffInMonths($end);
        $remainingDays = $end->copy()->startOfMonth()->diffInDays($end);
        
        if ($remainingDays >= 15) {
            $months++;
        }
        
        return $months;
    }

    /**
     * Calculate months within the current year for a price period
     */
    private function calculateMonthsInYear($pricePeriod, $year)
    {
        $yearStart = Carbon::create($year, 1, 1);
        $yearEnd = Carbon::create($year, 12, 31);
        
        $priceStart = Carbon::parse($pricePeriod['start']);
        $priceEnd = Carbon::parse($pricePeriod['end']);
        
        // Calculate overlap with the current year
        $segmentStart = $priceStart->copy()->max($yearStart);
        $segmentEnd = $priceEnd->copy()->min($yearEnd);
        
        if ($segmentStart > $segmentEnd) {
            return 0;
        }
        
        // Calculate months with proper day handling
        return $this->calculateMonths($segmentStart, $segmentEnd);
    }

    /**
     * Generate data for supplemental payment calculation
     * 
     * @return array
     */
    private function generateSupplementalPaymentData()
    {
        // Lấy tất cả hợp đồng với landRentalPrices và paymentHistories
        $contracts = LandRentalContract::with(['landRentalPrices', 'paymentHistories'])->get();
        
        $supplementalPaymentData = [];
        $index = 1;
        
        foreach ($contracts as $contract) {
            // Kiểm tra hợp đồng còn hạn theo năm hiện tại
            if ($contract->rental_period && isset($contract->rental_period['end_date'])) {
                $endDate = Carbon::parse($contract->rental_period['end_date']);
                
                // Chỉ lấy những hợp đồng có ngày kết thúc >= 1/1 của năm hiện tại
                if ($endDate->year >= $this->year) {
                    // Lấy giá cũ và giá mới từ landRentalPrices nằm trong khoảng của năm hiện tại
                    $oldUnitPrice = 0;
                    $newUnitPrice = 0;
                    $oldMonths = 0;
                    $newMonths = 0;
                    $oldPriceDate = null;
                    $newPriceDate = null;
                    
                    if ($contract->landRentalPrices->isNotEmpty()) {
                        $validPrices = $contract->landRentalPrices
                            ->filter(function ($price) {
                                $pricePeriod = $price->price_period;
                                if (!isset($pricePeriod['start']) || !isset($pricePeriod['end'])) {
                                    return false;
                                }
                                $startDate = Carbon::parse($pricePeriod['start']);
                                $endDate = Carbon::parse($pricePeriod['end']);
                                $yearStart = Carbon::create($this->year, 1, 1);
                                $yearEnd = Carbon::create($this->year, 12, 31);
                                
                                return $startDate->lte($yearEnd) && $endDate->gte($yearStart);
                            })
                            ->sortBy('updated_at');
                        
                        if ($validPrices->count() >= 2) {
                            // Có 2 giá: lấy giá cũ (first) và giá mới (last)
                            $oldPrice = $validPrices->first();
                            $newPrice = $validPrices->last();
                            $this->old_price_decision = $oldPrice->price_decision;
                            $this->new_price_decision = $newPrice->price_decision;
                            
                            $oldUnitPrice = (float)$oldPrice->rental_price;
                            $newUnitPrice = (float)$newPrice->rental_price;
                            
                            $oldPricePeriod = $oldPrice->price_period;
                            $newPricePeriod = $newPrice->price_period;
                            
                            // Tính số tháng dựa trên thời gian thực tế của từng kỳ giá trong năm hiện tại
                            $oldMonths = $this->calculateMonthsInYear($oldPricePeriod, $this->year);
                            $newMonths = $this->calculateMonthsInYear($newPricePeriod, $this->year);

                            $this->oldPriceDate = Carbon::parse($oldPricePeriod['end'])->format('d/m/Y');
                            $this->newPriceDate = Carbon::parse($newPricePeriod['start'])->format('d/m/Y');
                        } elseif ($validPrices->count() == 1) {
                            // Chỉ có 1 giá - không hiển thị dòng này
                            continue;
                        }
                    }
                    
                    // Tính tiền thuê/năm theo giá cũ và giá mới
                    $area = isset($contract->area['value']) ? (float)$contract->area['value'] : 0;
                    $oldAnnualRental = $area * $oldUnitPrice;
                    $newAnnualRental = $area * $newUnitPrice;
                    
                    // Lấy tổng tiền đã nộp trong năm
                    $paidAmount = $contract->paymentHistories
                        ->filter(function ($payment) {
                            return Carbon::parse($payment->payment_date)->year == $this->year;
                        })
                        ->sum('amount');
                    
                    // Tính tiền phải nộp bổ sung = tiền thuê/năm mới - tiền đã nộp
                    $supplementalPayment = max(0, $newAnnualRental - $paidAmount);
                    
                    if ($supplementalPayment > 0 || $newAnnualRental > 0) {
                        $supplementalPaymentData[] = [
                            'index' => $index,
                            'location' => $contract->rental_purpose,
                            'contract_number' => $contract->contract_number,
                            'area' => $area,
                            'old_unit_price' => $oldUnitPrice,
                            'old_months' => $newMonths,
                            'old_annual_rental' => $oldAnnualRental,
                            'new_unit_price' => $newUnitPrice,
                            'new_months' => $newMonths,
                            'new_annual_rental' => $newAnnualRental,
                            'paid_amount' => $paidAmount,
                            'supplemental_payment' => $supplementalPayment,
                            'notes' => $contract->notes
                        ];
                        $index++;
                    }
                }
            }
        }
        
        return $supplementalPaymentData;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = $this->generateSupplementalPaymentData();
        $formattedData = [];
        
        foreach ($data as $row) {
            $formattedData[] = [
                $row['index'],                  // A: STT
                $row['location'],               // B: Khu vực thuê (Hạng mục)
                $row['contract_number'],        // C: Hợp đồng số
                (float)$row['area'],            // D: Diện tích
                (float)$row['old_unit_price'],  // E: Đơn giá cũ (đ/m2/năm)
                (float)$row['old_months'],      // F: Số tháng giá cũ
                null,                           // G: Thành tiền cũ (tính bằng công thức)
                (float)$row['new_unit_price'],  // H: Đơn giá mới (đ/m2/năm)
                (float)$row['new_months'],      // I: Số tháng giá mới
                null,                           // J: Thành tiền mới (tính bằng công thức)
                null,                           // K: Tiền phải nộp bổ sung (tính bằng công thức)
                null,                           // L: Trống
            ];
        }
        
        return new Collection($formattedData);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Tiền nộp bổ sung năm ' . $this->year;
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A9';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'STT',
            'Hạng mục',
            'Hợp đồng số',
            'Diện tích (m2)',
            'Theo đơn giá cũ',
            '  - Đơn giá (đ/m2/năm)',
            '  - Thành tiền (đồng)',
            'Theo đơn giá mới',
            '  - Đơn giá (đ/m2/năm)',
            '  - Thành tiền (đồng)',
            'Tiền đã nộp',
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
            'B' => 20,
            'C' => 20,
            'D' => 15,
            'E' => 20,
            'F' => 18,
            'G' => 18,
            'H' => 20,
            'I' => 18,
            'J' => 18,
            'K' => 15,
            'L' => 18,
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
        
        $number = (string)$number;
        $number = preg_replace('/[^0-9]/', '', $number);
        
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
        
        $words = array_reverse($words);
        $result = implode(' ', $words);
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
        $result = preg_replace('/\s+/', ' ', $result);
        $result = trim($result);
        $result = ucfirst($result);
        
        return $result;
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Hợp nhất header trước dòng tiêu đề các cột
        $sheet->mergeCells('A4:L4');

        $sheet->setCellValue('A1', 'CÔNG TY CỔ PHẦN');
        $sheet->setCellValue('A2', 'NHIỆT ĐIỆN QUẢNG NINH');
        $sheet->setCellValue('A4', 'BẢNG TÍNH TIỀN NỘP BỔ SUNG NĂM ' . $this->year);

        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $firstHeaderRow = 7;
        
        // Hợp nhất các cột cho các phần "Theo đơn giá cũ" và "Theo đơn giá mới"
        $sheet->mergeCells('E'.$firstHeaderRow.':G'.$firstHeaderRow);
        $sheet->mergeCells('H'.$firstHeaderRow.':J'.$firstHeaderRow);
        
        $sheet->setCellValue('A'.$firstHeaderRow, 'STT');
        $sheet->setCellValue('B'.$firstHeaderRow, 'Hạng mục');
        $sheet->setCellValue('C'.$firstHeaderRow, 'Hợp đồng số');
        $sheet->setCellValue('D'.$firstHeaderRow, 'Diện tích (m2)');
        $sheet->setCellValue('E'.$firstHeaderRow, 'Theo đơn giá thuê đất cũ '.$this->old_price_decision);
        $sheet->setCellValue('H'.$firstHeaderRow, 'Theo đơn giá thuê đất mới '.$this->new_price_decision);
        $sheet->setCellValue('K'.$firstHeaderRow, 'Số tiền phải nộp bổ sung');
        $sheet->setCellValue('L'.$firstHeaderRow, 'Ghi chú');

        $secondHeaderRow = 8;
        $sheet->setCellValue('A'.$secondHeaderRow, '');
        $sheet->setCellValue('B'.$secondHeaderRow, '');
        $sheet->setCellValue('C'.$secondHeaderRow, '');
        $sheet->setCellValue('D'.$secondHeaderRow, '');
        $sheet->setCellValue('E'.$secondHeaderRow, 'Đơn giá (đ/m2/năm)');
        $sheet->setCellValue('F'.$secondHeaderRow, 'Số tháng');
        $sheet->setCellValue('G'.$secondHeaderRow, 'Thành tiền (đồng)');
        $sheet->setCellValue('H'.$secondHeaderRow, 'Đơn giá (đ/m2/năm)');
        $sheet->setCellValue('I'.$secondHeaderRow, 'Số tháng');
        $sheet->setCellValue('J'.$secondHeaderRow, 'Thành tiền (đồng)');
        $sheet->setCellValue('K'.$secondHeaderRow, '');

        $thirdHeaderRow = 9;
        $sheet->setCellValue('A'.$thirdHeaderRow, 'A');
        $sheet->setCellValue('B'.$thirdHeaderRow, 'B');
        $sheet->setCellValue('C'.$thirdHeaderRow, 'C');
        $sheet->setCellValue('D'.$thirdHeaderRow, '(1)');
        $sheet->setCellValue('E'.$thirdHeaderRow, '(2)');
        $sheet->setCellValue('F'.$thirdHeaderRow, '(3)');
        $sheet->setCellValue('G'.$thirdHeaderRow, '(4)=(1)x(2)x(3)/12');
        $sheet->setCellValue('H'.$thirdHeaderRow, '(5)');
        $sheet->setCellValue('I'.$thirdHeaderRow, '(6)');
        $sheet->setCellValue('J'.$thirdHeaderRow, '(7)=(1)x(5)x(6)/12');
        $sheet->setCellValue('K'.$thirdHeaderRow, '(8)=(7)-(4)');

        // Định dạng header
        $sheet->getStyle('A'.$firstHeaderRow.':L'.$thirdHeaderRow)->applyFromArray([
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

        $sheet->getRowDimension($firstHeaderRow)->setRowHeight(30);
        $sheet->getRowDimension($secondHeaderRow)->setRowHeight(25);
        $sheet->getRowDimension($thirdHeaderRow)->setRowHeight(25);

        $dataStartRow = 10;
        $dataEndRow = 9 + count($this->generateSupplementalPaymentData());
        
        if ($dataEndRow >= $dataStartRow) {
            for ($r = $dataStartRow; $r <= $dataEndRow; $r++) {
                // (4) = (1) x (2) x (3) / 12  => G = D * E * F / 12
                $sheet->setCellValue('G'.$r, '=D'.$r.'*E'.$r.'*F'.$r.'/12');
                // (7) = (1) x (5) x (6) / 12  => J = D * H * I / 12
                $sheet->setCellValue('J'.$r, '=D'.$r.'*H'.$r.'*I'.$r.'/12');
                // (8) = (7) - (4)  => K = J - G
                $sheet->setCellValue('K'.$r, '=J'.$r.'-G'.$r);
            }
        }

        // Định dạng số
        $sheet->getStyle('D'.$dataStartRow.':D'.$dataEndRow)->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle('E'.$dataStartRow.':E'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F'.$dataStartRow.':F'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('G'.$dataStartRow.':G'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('I'.$dataStartRow.':I'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('J'.$dataStartRow.':J'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K'.$dataStartRow.':K'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L'.$dataStartRow.':L'.$dataEndRow)->getNumberFormat()->setFormatCode('#,##0');

        // Border cho dữ liệu
        $sheet->getStyle('A'.$dataStartRow.':L'.$dataEndRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Dòng tổng cộng
        $totalRow = $dataEndRow + 1;
        $sheet->setCellValue('A'.$totalRow, 'Tổng');
        $sheet->mergeCells('A'.$totalRow.':D'.$totalRow);
        
        $sheet->setCellValue('F'.$totalRow, '=SUM(F'.$dataStartRow.':F'.$dataEndRow.')');
        $sheet->setCellValue('G'.$totalRow, '=SUM(G'.$dataStartRow.':G'.$dataEndRow.')');
        $sheet->setCellValue('H'.$totalRow, '=SUM(H'.$dataStartRow.':H'.$dataEndRow.')');
        $sheet->setCellValue('I'.$totalRow, '=SUM(I'.$dataStartRow.':I'.$dataEndRow.')');
        $sheet->setCellValue('J'.$totalRow, '=SUM(J'.$dataStartRow.':J'.$dataEndRow.')');
        $sheet->setCellValue('K'.$totalRow, '=SUM(K'.$dataStartRow.':K'.$dataEndRow.')');
        $sheet->setCellValue('L'.$totalRow, '=SUM(L'.$dataStartRow.':L'.$dataEndRow.')');
        
        $sheet->getStyle('A'.$totalRow.':L'.$totalRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'F2F2F2',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        $sheet->getStyle('A'.$totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F'.$totalRow.':L'.$totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F'.$totalRow.':L'.$totalRow)->getNumberFormat()->setFormatCode('#,##0');

        // Dòng bằng chữ
        $wordsRow = $totalRow + 1;
        $totalRemainingAmount = $sheet->getCell('K'.$totalRow)->getCalculatedValue();
        $amountInWords = $this->convertNumberToWords((int)$totalRemainingAmount) . ' đồng';
        
        $sheet->setCellValue('B'.$wordsRow, 'Bằng chữ:');
        $sheet->setCellValue('C'.$wordsRow, ucfirst($amountInWords));
        $sheet->mergeCells('C'.$wordsRow.':L'.$wordsRow);
        
        $sheet->getStyle('A'.$wordsRow.':L'.$wordsRow)->applyFromArray([
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

        // Phần ký tên
        $signatureRow = $wordsRow + 2;
        $sheet->setCellValue('A'.$signatureRow, 'Người lập bảng');
        $sheet->setCellValue('D'.$signatureRow, 'Kế toán trưởng');
        $sheet->setCellValue('G'.$signatureRow, 'Giám đốc');
        
        $sheet->mergeCells('A'.$signatureRow.':C'.$signatureRow);
        $sheet->mergeCells('D'.$signatureRow.':F'.$signatureRow);
        $sheet->mergeCells('G'.$signatureRow.':L'.$signatureRow);
        
        $sheet->getStyle('A'.$signatureRow.':L'.$signatureRow)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A'.$signatureRow.':L'.$signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Dòng ghi chú ký tên
        $signatureSpaceRow = $signatureRow + 1;
        $sheet->setCellValue('A'.$signatureSpaceRow, '(Ký, họ tên)');
        $sheet->setCellValue('D'.$signatureSpaceRow, '(Ký, họ tên)');
        $sheet->setCellValue('G'.$signatureSpaceRow, '(Ký, họ tên, đóng dấu)');
        
        $sheet->mergeCells('A'.$signatureSpaceRow.':C'.$signatureSpaceRow);
        $sheet->mergeCells('D'.$signatureSpaceRow.':F'.$signatureSpaceRow);
        $sheet->mergeCells('G'.$signatureSpaceRow.':L'.$signatureSpaceRow);
        
        $sheet->getStyle('A'.$signatureSpaceRow.':L'.$signatureSpaceRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$signatureSpaceRow.':L'.$signatureSpaceRow)->getFont()->setItalic(true)->setSize(11);
        
        // Tăng chiều cao cho phần ký tên
        $sheet->getRowDimension($signatureRow + 2)->setRowHeight(40);
        $sheet->getRowDimension($signatureRow + 3)->setRowHeight(40);
    }
}
