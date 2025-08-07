<?php

namespace App\Exports;

use App\Models\LandRentalContract;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LandRentalContractsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
            // Add contract with first price (or empty price if none exists)
            $flattenedData->push([
                'contract' => $contract,
                'price' => $contract->landRentalPrices->first(),
                'is_main' => true,
                'price_index' => 0
            ]);

            // Add additional entries for any other prices (starting from index 1)
            if ($contract->landRentalPrices->count() > 1) {
                foreach ($contract->landRentalPrices->slice(1) as $index => $price) {
                    $flattenedData->push([
                        'contract' => $contract,
                        'price' => $price,
                        'is_main' => false,
                        'price_index' => $index + 1
                    ]);
                }
            }
        }

        return $flattenedData;
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
            $rowNumber = $mainCount;
        } else {
            // Use decimal notation for additional prices (e.g., 1.1, 1.2, etc.)
            $rowNumber = $mainCount . '.' . $priceIndex;
        }

        // Format thời gian thuê - only for main rows
        $rentalPeriodText = '';
        if (!empty($contract->rental_period) && isset($contract->rental_period['years']) && isset($contract->rental_period['start_date'])) {
            $startDate = Carbon::parse($contract->rental_period['start_date'])->format('d/m/Y');
            $rentalPeriodText = $contract->rental_period['years'] . ' năm kể từ ngày ' . $startDate;
        }

        // Rental price information
        $rentalPrice = '';
        $priceDecision = '';
        $pricePeriod = '';

        if ($price) {
            // Format rental price with thousands separator and preserve 2 decimal places
            $rentalPrice = number_format($price->rental_price, 0, '', '');

            // Format price decision with date
            $priceDecision = $price->price_decision ?? '';

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

        // For additional prices, only include price-related information
        if (!$isMain) {
            return [
                $rowNumber, // STT with decimal notation
                $contract->contract_number,
                $contract->rental_decision ?? '',
                $contract->area['unit'] ?? 'm2',
                $contract->area['value'] ?? 0,
                $rentalPeriodText,
                $contract->rental_purpose ?? '',
                $contract->rental_zone ?? '',
                $rentalPrice, // Price information
                $priceDecision, // Price decision
                $pricePeriod, // Price period
                '' // Empty notes
            ];
        }

        // For main rows, include all contract information
        return [
            $rowNumber,
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
            $contract->notes ?? ''
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (headings)
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
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
