<?php

namespace App\Exports;

use App\Models\LandRentalContract;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LandRentalContractsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents, WithCustomStartCell
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $contracts = LandRentalContract::with('landRentalPrices')->get();
        
        // Create a flat collection that includes all contracts with their prices
        $flattenedData = new Collection();
        
        foreach ($contracts as $contract) {
            // If contract has no prices, add it with null price
            if ($contract->landRentalPrices->isEmpty()) {
                $flattenedData->push([
                    'contract' => $contract,
                    'price' => null,
                    'is_main' => true,
                    'price_index' => 0
                ]);
                continue;
            }
            
            // Add contract with all its prices
            foreach ($contract->landRentalPrices as $index => $price) {
                $flattenedData->push([
                    'contract' => $contract,
                    'price' => $price,
                    'is_main' => $index === 0, // First price is main
                    'price_index' => $index
                ]);
            }
        }
        
        return $flattenedData;
    }
    
    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A6';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'STT',
            'Số hợp đồng',
            'Quyết định thuê đất',
            'Đơn vị tính',
            'Diện tích',
            'Thời gian thuê',
            'Mục đích thuê',
            'Khu vực thuê',
            'Đơn giá thuê đất',
            'Quyết định đơn giá thuê đất/ ngày tháng năm',
            'Thời gian ổn định đơn giá thuê đất',
            'Ghi chú'
        ];
    }

    /**
     * Register events for sheet customization
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set font size for entire sheet
                $sheet->getStyle($sheet->calculateWorksheetDimension())->getFont()->setSize(11);
                
                // Company name in A1
                $sheet->setCellValue('A1', 'Công ty cổ phần nhiệt điện Quảng Ninh');
                $sheet->getStyle('A1')->getFont()->setBold(true);
                
                // Title in row 3, merged cells A3:L3
                $sheet->mergeCells('A3:L3');
                $sheet->setCellValue('A3', 'BẢNG THEO DÕI CÁC HỢP ĐỒNG THUÊ ĐẤT');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
                
                // Borders for header and data
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A6:L' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                // Format specific columns
                // Set column A as Text format
                $sheet->getStyle('A7:A' . $lastRow)->getNumberFormat()->setFormatCode('@');
                
                // Format columns with numbers
                $sheet->getStyle('E7:E' . $lastRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('I7:I' . $lastRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                
                // Text wrapping for all data cells
                $sheet->getStyle('A6:L' . $lastRow)->getAlignment()->setWrapText(true);
                
                // Set vertical alignment to middle for all cells
                $sheet->getStyle('A6:L' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                
                // Set specific column widths based on the image
                $columnWidths = [
                    'A' => 5,   // STT (narrow)
                    'B' => 20,  // Số hợp đồng
                    'C' => 20,  // Quyết định cho thuê đất
                    'D' => 8,   // Đơn vị tính (narrow)
                    'E' => 12,  // Diện tích
                    'F' => 18,  // Thời gian thuê
                    'G' => 20,  // Mục đích thuê
                    'H' => 15,  // Khu vực thuê
                    'I' => 12,  // Đơn giá thuê đất
                    'J' => 18,  // Quyết định đơn giá
                    'K' => 25,  // Thời gian ổn định đơn giá
                    'L' => 15,  // Ghi chú
                ];
                
                foreach ($columnWidths as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }
                
                // Set height for the header row to accommodate multi-line headers
                $sheet->getRowDimension(6)->setRowHeight(40);
            },
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        static $mainCount = 0;

        // Extract data from the row
        $contract = $row['contract'];
        $price = $row['price'];
        $isMain = $row['is_main'];
        $priceIndex = $row['price_index'];

        // Increment main counter only for main rows
        if ($isMain) {
            $mainCount++;
            $rowNumber = (string)$mainCount; // Convert to string to ensure it's treated as text
        } else {
            // Use decimal notation with period (.) not comma (,) for additional prices (e.g., 1.1, 1.2, etc.)
            $rowNumber = $mainCount . '.' . $priceIndex.' '; // Ensure correct decimal point format
        }

        // Format thời gian thuê
        $rentalPeriodText = '';
        if (!empty($contract->rental_period) && isset($contract->rental_period['years']) && isset($contract->rental_period['start_date'])) {
            $startDate = Carbon::parse($contract->rental_period['start_date'])->format('d/m/Y');
            $rentalPeriodText = $contract->rental_period['years'] . ' năm kể từ ngày ' . $startDate;
        }

        // Rental price information
        $rentalPrice = '';
        $priceDecision = '';
        $pricePeriod = '';
        $priceNotes = '';

        if ($price) {
            // Format rental price
            $rentalPrice = $price->rental_price;

            // Format price decision
            $priceDecision = $price->price_decision ?? '';

            // Get price notes
            $priceNotes = $price->note ?? '';

            // Format price period
            if (!empty($price->price_period)) {
                $year = isset($price->price_period['years']) ? 
                    $price->price_period['years'] : '';
                $startDate = isset($price->price_period['start']) ? 
                    Carbon::parse($price->price_period['start'])->format('d/m/Y') : '';
                $endDate = isset($price->price_period['end']) ? 
                    Carbon::parse($price->price_period['end'])->format('d/m/Y') : '';
                
                if ($startDate && $endDate) {
                    $pricePeriod = "Đơn giá ổn định $year năm 1 lần (từ $startDate đến $endDate)";
                }
            }
        }

        return [
            $rowNumber, // STT now as string to ensure text format
            $contract->contract_number,
            $contract->rental_decision ?? '',
            $contract->area['unit'] ?? 'm2',
            $contract->area['value'] ?? 0,
            $rentalPeriodText,
            $contract->rental_purpose ?? '',
            $contract->rental_zone ?? '',
            $rentalPrice,
            $priceDecision,
            $pricePeriod,
            $priceNotes // Use the price's notes instead of contract's notes
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style the header row
        return [
            6 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Hợp đồng thuê đất';
    }
}
