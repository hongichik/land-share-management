<?php

namespace App\Imports;

use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\SecuritiesManagement;
use App\Models\DividendRecord;
use Illuminate\Support\Facades\Log;

class InvestorsImport implements OnEachRow, WithEvents, WithMultipleSheets, WithTitle
{
    private $sheetName;
    private $config;
    private $allSheetData = []; // Lưu trữ dữ liệu sheet
    // Tạo thuộc tính public cho tất cả các trường trong column_mappings
    public $full_name;
    public $sid;
    public $investor_code;
    public $registration_number;
    public $issue_date;
    public $address;
    public $email;
    public $phone;
    public $nationality;
    public $not_deposited_quantity;
    public $deposited_quantity;
    public $total_quantity;
    public $pre_tax_payment_not_deposited;
    public $pre_tax_payment_deposited;
    public $pre_tax_payment_total;
    public $pit_tax_not_deposited;
    public $pit_tax_deposited;
    public $pit_tax_total;
    public $post_tax_payment_not_deposited;
    public $post_tax_payment_deposited;
    public $post_tax_payment_total;
    public $bank_account;
    public $bank_name;
    public $bank_branch;
    public $notes;
    public $status;

        /**
         * Trả về vị trí các cột (index) theo cấu hình column_mappings
         * @return array
         */
        // Xoá hàm getColumnIndexes vì không còn sử dụng

    public function __construct()
    {
        $configPath = base_path('app/Imports/import_settings.json');
        $json = file_get_contents($configPath);
        $this->config = json_decode($json, true);
        $this->sheetName = $this->config['file_config']['sheet_name'] ?? 'Sheet1';

        // Gán giá trị header_text cho từng thuộc tính từ column_mappings
        foreach ($this->config['column_mappings'] as $mapping) {
            $field = $mapping['internal_field'];
            if (property_exists($this, $field)) {
                $this->$field = $mapping['header_text'];
            }
        }
    }
    public function title(): string
    {
        return $this->sheetName;
    }

    public function sheets(): array
    {
        return [
            $this->sheetName => $this,
        ];
    }

    /**
     * Lấy vị trí bắt đầu và kết thúc của các block nhà đầu tư
     * @param array $allData Dữ liệu từ sheet
     * @return array
     */
    public function getInvestorBlockPositions(array $allData): array
    {
        $normalize = function($str) {
            $str = mb_strtolower($str, 'UTF-8');
            $str = str_replace(["\r", "\n"], ' ', $str); // loại bỏ xuống dòng
            $str = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/u', 'a', $str);
            $str = preg_replace('/[èéẹẻẽêềếệểễ]/u', 'e', $str);
            $str = preg_replace('/[ìíịỉĩ]/u', 'i', $str);
            $str = preg_replace('/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $str);
            $str = preg_replace('/[ùúụủũưừứựửữ]/u', 'u', $str);
            $str = preg_replace('/[ỳýỵỷỹ]/u', 'y', $str);
            $str = preg_replace('/[đ]/u', 'd', $str);
            $str = preg_replace('/[^a-z0-9 ]/u', '', $str); // loại ký tự đặc biệt
            $str = preg_replace('/\s+/', '_', $str); // thay dấu cách bằng _
            return $str;
        };

        $result = [];
        foreach ($this->config['column_mappings'] as $mapping) {
            $field = $mapping['internal_field'];
            $header = $mapping['header_text'];
            $parent = $mapping['parent_header_text'] ?? null;
            $normalizedHeader = $normalize($header);
            $positions = [];
            
            if ($parent) {
                $normalizedParent = $normalize($parent);
                foreach ($allData as $rowIdx => $row) {
                    foreach ($row as $colIdx => $cell) {
                        if ($normalize($cell) === $normalizedParent) {
                            // Tìm thẳng xuống các dòng bên dưới và kiểm tra 3 cột liên tiếp (col, col+1, col+2)
                            $found = false;
                            $maxRow = count($allData);
                            for ($targetRowIdx = $rowIdx + 1; $targetRowIdx < $maxRow; $targetRowIdx++) {
                                for ($j = 0; $j < 3; $j++) {
                                    $targetColIdx = $colIdx + $j;
                                    if (isset($allData[$targetRowIdx][$targetColIdx])) {
                                        $targetCell = $allData[$targetRowIdx][$targetColIdx];
                                        if ($normalize($targetCell) === $normalizedHeader) {
                                            $positions = [($targetRowIdx + 1), ($targetColIdx + 1)];
                                            $found = true;
                                            break 2;
                                        }
                                    }
                                }
                                if ($found) break;
                            }
                        }
                    }
                }

                // Đặc biệt cho các trường investor_type_xxx_domestic/foreign
                if (strpos($field, 'investor_type_') === 0) {
                    // parent là investor_domain_xxx
                    $parentField = null;
                    if (strpos($field, 'domestic') !== false) {
                        $parentField = 'investor_domain_domestic';
                    } elseif (strpos($field, 'foreign') !== false) {
                        $parentField = 'investor_domain_foreign';
                    }
                    if ($parentField) {
                        $parentHeader = $this->$parentField ?? null;
                        if ($parentHeader) {
                            $normalizedParentHeader = $normalize($parentHeader);
                            foreach ($allData as $rowIdx => $row) {
                                foreach ($row as $colIdx => $cell) {
                                    if ($normalize($cell) === $normalizedParentHeader) {
                                        // Kiểm tra 3 dòng bên dưới và đúng cột parent
                                        for ($i = 1; $i <= 3; $i++) {
                                            $targetRowIdx = $rowIdx + $i;
                                            if (isset($allData[$targetRowIdx][$colIdx])) {
                                                $targetCell = $allData[$targetRowIdx][$colIdx];
                                                if ($normalize($targetCell) === $normalizedHeader) {
                                                    $positions[] = '(' . ($targetRowIdx + 1) . ',' . ($colIdx + 1) . ')';
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // Tìm như cũ trên toàn bộ sheet
                foreach ($allData as $rowIdx => $row) {
                    foreach ($row as $colIdx => $cell) {
                        if ($normalize($cell) === $normalizedHeader) {
                            $positions = [($rowIdx + 1) , ($colIdx + 1)];
                        }
                    }
                }
            }
            $result[$field] = $positions;
        }

        // Tìm dòng cuối cùng có dữ liệu - dựa vào cột SID
        $lastRowWithData = null;
        $sidColIndex = null;
        
        // Tìm vị trí cột SID từ kết quả
        if (isset($result['sid']) && !empty($result['sid'])) {
            $sidColIndex = $result['sid'][1] - 1; // Convert to 0-based index
        }
        
        if ($sidColIndex !== null) {
            for ($i = count($allData) - 1; $i >= 0; $i--) {
                if (isset($allData[$i][$sidColIndex]) && !empty($allData[$i][$sidColIndex])) {
                    $lastRowWithData = $i + 1; // Convert to 1-based index
                    break;
                }
            }
        }

        // Lấy toạ độ cột của TẤT CẢ các cột
        $columnPositions = [];
        foreach ($result as $field => $position) {
            if ($field && !empty($position) && isset($position[1])) {
                $columnPositions[$field] = $position[1]; // Chỉ lấy column index
            }
        }

        $start_end = [
            'moi_gioi_ca_nhan_trong_nuoc' => [
                'start' => isset($result['investor_type_individual_domestic'][0]) ? $result['investor_type_individual_domestic'][0] + 1 : null,
                'end' => isset($result['investor_type_organization_domestic'][0]) ? $result['investor_type_organization_domestic'][0] - 1 : null
            ],
            'to_chuc_trong_nuoc' => [
                'start' => isset($result['investor_type_organization_domestic'][0]) ? $result['investor_type_organization_domestic'][0] + 1 : null,
                'end' => isset($result['investor_type_individual_foreign'][0]) ? $result['investor_type_individual_foreign'][0] - 2 : null
            ],
            'moi_gioi_ca_nhan_nuoc_ngoai' => [
                'start' => isset($result['investor_type_individual_foreign'][0]) ? $result['investor_type_individual_foreign'][0] + 1 : null,
                'end' => isset($result['investor_type_organization_foreign'][0]) ? $result['investor_type_organization_foreign'][0] - 1 : null
            ],
            'to_chuc_nuoc_ngoai' => [
                'start' => isset($result['investor_type_organization_foreign'][0]) ? $result['investor_type_organization_foreign'][0] + 1 : null,
                'end' => $lastRowWithData ?? count($allData) // Dòng cuối cùng có giá trị cột SID hoặc cuối sheet
            ],
            'column_positions' => $columnPositions
        ];

        return $start_end;
    }



    public function registerEvents(): array
    {
        return [];
    }

    /**
     * Generate preview data from Excel file - lấy dữ liệu từ các block nhà đầu tư
     * Returns array with insert and update information
     * 
     * @param array $allData Dữ liệu từ sheet
     * @param array $blockPositions Vị trí các block từ getInvestorBlockPositions
     * @return array
     */
    public function getPreviewData(array $allData, array $blockPositions): array
    {
        $changes = [];
        $insertCount = 0;
        $updateCount = 0;

        // Danh sách các cột dữ liệu cần duyệt
        $dataFields = [
            'full_name',
            'sid',
            'investor_code',
            'registration_number',
            'issue_date',
            'address',
            'email',
            'phone',
            'nationality',
            'bank_account',
            'bank_name',
            'bank_branch',
            'notes',
            'status'
        ];

        $columnPositions = $blockPositions['column_positions'] ?? [];

        // Xử lý từng block nhà đầu tư
        foreach (['moi_gioi_ca_nhan_trong_nuoc', 'to_chuc_trong_nuoc', 'moi_gioi_ca_nhan_nuoc_ngoai', 'to_chuc_nuoc_ngoai'] as $blockName) {
            if (!isset($blockPositions[$blockName])) continue;

            $block = $blockPositions[$blockName];
            $startRow = $block['start'] ?? null;
            $endRow = $block['end'] ?? null;

            if (!$startRow || !$endRow) continue;

            // Duyệt từng dòng trong block (convert 1-based to 0-based for array access)
            for ($rowIdx = $startRow - 1; $rowIdx < $endRow; $rowIdx++) { // $endRow là 1-based, nên < là đúng
                if (!isset($allData[$rowIdx])) continue;

                $row = $allData[$rowIdx];
                Log::info("Processing row " . ($rowIdx + 1) . " in block $blockName");

                // Lấy SID từ dòng này
                $sidColIndex = null;
                if (isset($columnPositions['sid'])) {
                    $sidColIndex = $columnPositions['sid'] - 1; // Convert to 0-based
                }

                $sid = $sidColIndex !== null && isset($row[$sidColIndex]) ? trim((string)$row[$sidColIndex]) : null;

                // Lấy registration_number từ dòng này
                $registrationNumberColIndex = null;
                if (isset($columnPositions['registration_number'])) {
                    $registrationNumberColIndex = $columnPositions['registration_number'] - 1; // Convert to 0-based
                }

                $registrationNumber = $registrationNumberColIndex !== null && isset($row[$registrationNumberColIndex]) ? trim((string)$row[$registrationNumberColIndex]) : null;

                // Cần ít nhất một trong hai: sid hoặc registration_number
                if (!$sid && !$registrationNumber) continue;

                // Lấy dữ liệu từ các cột được chỉ định
                $rowData = [];
                $hasExcelFormula = false;
                foreach ($dataFields as $field) {
                    if (isset($columnPositions[$field])) {
                        $colIdx = $columnPositions[$field] - 1; // Convert to 0-based (column_positions là 1-based)
                        if (isset($row[$colIdx])) {
                            $value = $row[$colIdx];
                            // Loại bỏ giá trị rỗng hoặc null
                            if ($value !== null && $value !== '') {
                                // Check if value is an Excel formula
                                if ($this->isExcelFormula($value)) {
                                    $hasExcelFormula = true;
                                    break; // Skip this row if it contains formulas
                                }
                                
                                // Parse date fields
                                if (in_array($field, ['issue_date'])) {
                                    $parsedDate = $this->parseDate($value);
                                    if ($parsedDate !== null) {
                                        $rowData[$field] = $parsedDate;
                                    }
                                } else {
                                    $rowData[$field] = $value;
                                }
                            }
                        }
                    }
                }
                
                // Skip rows with Excel formulas
                if ($hasExcelFormula) continue;
                
                Log::info("Processing row $rowIdx in block $blockName - SID: $sid, Registration: $registrationNumber");                
                // Kiểm tra xem có dữ liệu không
                if (empty(array_filter($rowData))) continue;

                // Tìm record trong database theo SID hoặc registration_number
                $existing = null;
                if ($sid) {
                    $existing = SecuritiesManagement::where('sid', $sid)->first();
                }
                
                // Nếu không tìm thấy theo SID, tìm theo registration_number
                if (!$existing && $registrationNumber) {
                    $existing = SecuritiesManagement::where('registration_number', $registrationNumber)->first();
                }

                if ($existing) {
                    // Chuẩn bị danh sách thay đổi
                    // Loại bỏ các field không được phép thay đổi (unique constraints) và dividend fields
                    $fieldsNotAllowedToUpdate = ['sid', 'investor_code', 'not_deposited_quantity', 'deposited_quantity', 'total_quantity',
                                                  'pre_tax_payment_not_deposited', 'pre_tax_payment_deposited', 'pre_tax_payment_total',
                                                  'pit_tax_not_deposited', 'pit_tax_deposited', 'pit_tax_total',
                                                  'post_tax_payment_not_deposited', 'post_tax_payment_deposited', 'post_tax_payment_total'];
                    
                    $changesList = [];
                    $dividendData = [];

                    foreach ($rowData as $field => $newValue) {
                        if (!$newValue) continue; // Bỏ qua giá trị trống
                        
                        // Tách dữ liệu dividend
                        $dividendFields = ['not_deposited_quantity', 'deposited_quantity', 
                                          'pre_tax_payment_not_deposited', 'pre_tax_payment_deposited', 'pre_tax_payment_total',
                                          'pit_tax_not_deposited', 'pit_tax_deposited', 'pit_tax_total',
                                          'post_tax_payment_not_deposited', 'post_tax_payment_deposited', 'post_tax_payment_total'];
                        
                        if (in_array($field, $dividendFields)) {
                            $dividendData[$field] = $newValue;
                            continue;
                        }
                        
                        // Bỏ qua các field có unique constraint
                        if (in_array($field, $fieldsNotAllowedToUpdate)) {
                            continue;
                        }

                        $oldValue = $existing->$field ?? null;

                        // Normalize để so sánh
                        $oldValueNorm = $this->normalizeValue($oldValue, $field);
                        $newValueNorm = $this->normalizeValue($newValue, $field);

                        if ($oldValueNorm !== $newValueNorm) {
                            $changesList[$field] = [
                                'old' => $oldValue,
                                'new' => $newValue
                            ];
                        }
                    }

                    if (!empty($changesList) || !empty($dividendData)) {
                        $previewItem = [
                            'type' => 'update',
                            'sid' => $sid,
                            'full_name' => $existing->full_name,
                            'id' => $existing->id,
                            'block' => $blockName,
                            'row' => $rowIdx + 1, // Convert to 1-based
                        ];
                        
                        // Thêm thay đổi securities
                        if (!empty($changesList)) {
                            $previewItem['changes'] = $changesList;
                        }
                        
                        // Thêm thông tin DividendRecord sẽ tạo
                        if (!empty($dividendData)) {
                            $previewItem['dividend_record'] = [
                                'non_deposited_shares_quantity' => $dividendData['not_deposited_quantity'] ?? 0,
                                'deposited_shares_quantity' => $dividendData['deposited_quantity'] ?? 0,
                                'non_deposited_amount_before_tax' => $dividendData['pre_tax_payment_not_deposited'] ?? 0,
                                'deposited_amount_before_tax' => $dividendData['pre_tax_payment_deposited'] ?? 0,
                                'non_deposited_personal_income_tax' => $dividendData['pit_tax_not_deposited'] ?? 0,
                                'deposited_personal_income_tax' => $dividendData['pit_tax_deposited'] ?? 0,
                            ];
                        }
                        
                        $changes[] = $previewItem;
                        $updateCount++;
                    }
                } else {
                    // Record mới
                    $full_name = $rowData['full_name'] ?? 'N/A';
                    
                    // Tách dữ liệu dividend
                    $dividendFields = ['not_deposited_quantity', 'deposited_quantity', 
                                      'pre_tax_payment_not_deposited', 'pre_tax_payment_deposited', 'pre_tax_payment_total',
                                      'pit_tax_not_deposited', 'pit_tax_deposited', 'pit_tax_total',
                                      'post_tax_payment_not_deposited', 'post_tax_payment_deposited', 'post_tax_payment_total'];
                    
                    $dividendData = [];
                    $securitiesData = [];
                    
                    foreach ($rowData as $field => $value) {
                        if (in_array($field, $dividendFields)) {
                            $dividendData[$field] = $value;
                        } else {
                            $securitiesData[$field] = $value;
                        }
                    }
                    
                    $previewItem = [
                        'type' => 'insert',
                        'sid' => $sid,
                        'full_name' => $full_name,
                        'block' => $blockName,
                        'row' => $rowIdx + 1, // Convert to 1-based
                        'data' => $securitiesData
                    ];
                    
                    // Thêm thông tin DividendRecord sẽ tạo
                    if (!empty($dividendData)) {
                        $previewItem['dividend_record'] = [
                            'non_deposited_shares_quantity' => $dividendData['not_deposited_quantity'] ?? 0,
                            'deposited_shares_quantity' => $dividendData['deposited_quantity'] ?? 0,
                            'non_deposited_amount_before_tax' => $dividendData['pre_tax_payment_not_deposited'] ?? 0,
                            'deposited_amount_before_tax' => $dividendData['pre_tax_payment_deposited'] ?? 0,
                            'non_deposited_personal_income_tax' => $dividendData['pit_tax_not_deposited'] ?? 0,
                            'deposited_personal_income_tax' => $dividendData['pit_tax_deposited'] ?? 0,
                        ];
                    }
                    
                    $changes[] = $previewItem;
                    $insertCount++;
                }
            }
        }

        return [
            'preview' => $changes,
            'insertCount' => $insertCount,
            'updateCount' => $updateCount,
            'totalRows' => $insertCount + $updateCount
        ];
    }

    /**
     * Parse and convert dates to Y-m-d format
     * @param $value
     * @return string|null
     */
    private function parseDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            // If it's a DateTime object
            if ($value instanceof \DateTime) {
                return $value->format('Y-m-d');
            }
            
            // If it's a string, try various formats
            if (is_string($value)) {
                // Handle ISO format with time: 2015-12-29T00:00:00.000000Z
                if (strpos($value, 'T') !== false) {
                    $date = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $value);
                    if (!$date) {
                        $date = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $value);
                    }
                    if ($date) {
                        return $date->format('Y-m-d');
                    }
                }
                
                // Handle d/m/Y format: 29/12/2015
                if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                    $date = \DateTime::createFromFormat('d/m/Y', $value);
                    if ($date) {
                        return $date->format('Y-m-d');
                    }
                }
                
                // Handle Y-m-d format
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    return $value;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse date: ' . $value);
        }
        
        return null;
    }

    /**
     * Check if value is an Excel formula
     * @param $value
     * @return bool
     */
    private function isExcelFormula($value)
    {
        if (is_string($value)) {
            return strpos(trim($value), '=') === 0;
        }
        return false;
    }

    /**
     * Normalize value for comparison
     * 
     * @param mixed $value
     * @param string $field
     * @return string
     */
    private function normalizeValue($value, $field): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Handle date fields - convert to Y-m-d format for comparison
        if (in_array($field, ['issue_date', 'created_at', 'updated_at'])) {
            try {
                // Try to parse the value as a date
                if ($value instanceof \DateTime) {
                    return $value->format('Y-m-d');
                }
                
                // If it's a string, try various formats
                if (is_string($value)) {
                    // Handle ISO format with time: 2015-12-29T00:00:00.000000Z
                    if (strpos($value, 'T') !== false) {
                        $date = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $value);
                        if (!$date) {
                            $date = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $value);
                        }
                        if ($date) {
                            return $date->format('Y-m-d');
                        }
                    }
                    
                    // Handle d/m/Y format: 29/12/2015
                    if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                        $date = \DateTime::createFromFormat('d/m/Y', $value);
                        if ($date) {
                            return $date->format('Y-m-d');
                        }
                    }
                    
                    // Handle Y-m-d format
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        return $value;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to parse date: ' . $value);
            }
        }

        // For numeric fields, convert to string for comparison
        return (string)$value;
    }

    /**
     * Thực thi import - thêm hoặc update dữ liệu từ Excel
     * Dựa trên kết quả từ getPreviewData
     * 
     * @param array $allData Dữ liệu từ sheet
     * @param array $blockPositions Vị trí các block từ getInvestorBlockPositions
     * @return array Kết quả import (processedRows, errors)
     */
    public function executeImport(array $allData, array $blockPositions, ?string $paymentDate = null, ?float $dividendPricePerShare = null): array
    {
        $processedRows = [
            'inserted' => 0,
            'updated' => 0,
            'failed' => 0
        ];
        $errors = [];

        // Danh sách các cột dữ liệu cần duyệt
        $dataFields = [
            'full_name',
            'sid',
            'investor_code',
            'registration_number',
            'issue_date',
            'address',
            'email',
            'phone',
            'nationality',
            'not_deposited_quantity',
            'deposited_quantity',
            'total_quantity',
            'pre_tax_payment_not_deposited',
            'pre_tax_payment_deposited',
            'pre_tax_payment_total',
            'pit_tax_not_deposited',
            'pit_tax_deposited',
            'pit_tax_total',
            'post_tax_payment_not_deposited',
            'post_tax_payment_deposited',
            'post_tax_payment_total',
            'bank_account',
            'bank_name',
            'bank_branch',
            'notes',
            'status'
        ];

        $columnPositions = $blockPositions['column_positions'] ?? [];

        // Xử lý từng block nhà đầu tư
        foreach (['moi_gioi_ca_nhan_trong_nuoc', 'to_chuc_trong_nuoc', 'moi_gioi_ca_nhan_nuoc_ngoai', 'to_chuc_nuoc_ngoai'] as $blockName) {
            if (!isset($blockPositions[$blockName])) continue;

            $block = $blockPositions[$blockName];
            $startRow = $block['start'] ?? null;
            $endRow = $block['end'] ?? null;

            if (!$startRow || !$endRow) continue;

            // Duyệt từng dòng trong block
            for ($rowIdx = $startRow - 1; $rowIdx < $endRow; $rowIdx++) {
                if (!isset($allData[$rowIdx])) continue;

                $row = $allData[$rowIdx];

                try {
                    // Lấy SID từ dòng này
                    $sidColIndex = null;
                    if (isset($columnPositions['sid'])) {
                        $sidColIndex = $columnPositions['sid'] - 1;
                    }

                    $sid = $sidColIndex !== null && isset($row[$sidColIndex]) ? trim((string)$row[$sidColIndex]) : null;

                    // Lấy registration_number từ dòng này
                    $registrationNumberColIndex = null;
                    if (isset($columnPositions['registration_number'])) {
                        $registrationNumberColIndex = $columnPositions['registration_number'] - 1;
                    }

                    $registrationNumber = $registrationNumberColIndex !== null && isset($row[$registrationNumberColIndex]) ? trim((string)$row[$registrationNumberColIndex]) : null;

                    // Cần ít nhất một trong hai: sid hoặc registration_number
                    if (!$sid && !$registrationNumber) continue;

                    // Lấy dữ liệu từ các cột được chỉ định
                    $rowData = [];
                    $hasExcelFormula = false;
                    foreach ($dataFields as $field) {
                        if (isset($columnPositions[$field])) {
                            $colIdx = $columnPositions[$field] - 1;
                            if (isset($row[$colIdx])) {
                                $value = $row[$colIdx];
                                if ($value !== null && $value !== '') {
                                    // Check if value is an Excel formula
                                    if ($this->isExcelFormula($value)) {
                                        $hasExcelFormula = true;
                                        break; // Skip this row if it contains formulas
                                    }
                                    
                                    // Parse date fields
                                    if (in_array($field, ['issue_date'])) {
                                        $parsedDate = $this->parseDate($value);
                                        if ($parsedDate !== null) {
                                            $rowData[$field] = $parsedDate;
                                        }
                                    } else {
                                        $rowData[$field] = $value;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Skip rows with Excel formulas
                    if ($hasExcelFormula) continue;
                    
                    // Kiểm tra xem có dữ liệu không
                    if (empty(array_filter($rowData))) continue;

                    // Tìm record trong database theo SID hoặc registration_number
                    $existing = null;
                    if ($sid) {
                        $existing = SecuritiesManagement::where('sid', $sid)->first();
                    }
                    
                    // Nếu không tìm thấy theo SID, tìm theo registration_number
                    if (!$existing && $registrationNumber) {
                        $existing = SecuritiesManagement::where('registration_number', $registrationNumber)->first();
                    }

                    if ($existing) {
                        // Update record - chỉ update những field thay đổi
                        // Loại bỏ các field không được phép thay đổi (unique constraints) và dividend fields
                        $fieldsNotAllowedToUpdate = ['not_deposited_quantity', 'deposited_quantity', 'total_quantity',
                                                      'pre_tax_payment_not_deposited', 'pre_tax_payment_deposited', 'pre_tax_payment_total',
                                                      'pit_tax_not_deposited', 'pit_tax_deposited', 'pit_tax_total',
                                                      'post_tax_payment_not_deposited', 'post_tax_payment_deposited', 'post_tax_payment_total'];
                        
                        $dataToUpdate = [];
                        $dividendData = [
                            'securities_management_id' => $existing->id,
                            'non_deposited_shares_quantity' => 0,
                            'deposited_shares_quantity' => 0,
                            'non_deposited_amount_before_tax' => 0,
                            'deposited_amount_before_tax' => 0,
                            'non_deposited_personal_income_tax' => 0,
                            'deposited_personal_income_tax' => 0,
                            'payment_status' => 'unpaid'
                        ];
                        
                        foreach ($rowData as $field => $newValue) {
                            if (!$newValue) continue; // Bỏ qua giá trị trống
                            
                            // Bỏ qua các field có unique constraint hoặc dividend fields
                            if (in_array($field, $fieldsNotAllowedToUpdate)) {
                                continue;
                            }
                            
                            $oldValue = $existing->$field ?? null;
                            $oldValueNorm = $this->normalizeValue($oldValue, $field);
                            $newValueNorm = $this->normalizeValue($newValue, $field);
                            
                            // Chỉ update nếu giá trị thay đổi
                            if ($oldValueNorm !== $newValueNorm) {
                                $dataToUpdate[$field] = $newValue;
                            }
                        }
                        
                        // Xử lý dữ liệu dividend từ rowData
                        if (isset($rowData['not_deposited_quantity'])) {
                            $dividendData['non_deposited_shares_quantity'] = $rowData['not_deposited_quantity'];
                        }
                        if (isset($rowData['deposited_quantity'])) {
                            $dividendData['deposited_shares_quantity'] = $rowData['deposited_quantity'];
                        }
                        if (isset($rowData['pre_tax_payment_not_deposited'])) {
                            $dividendData['non_deposited_amount_before_tax'] = $rowData['pre_tax_payment_not_deposited'];
                        }
                        if (isset($rowData['pre_tax_payment_deposited'])) {
                            $dividendData['deposited_amount_before_tax'] = $rowData['pre_tax_payment_deposited'];
                        }
                        if (isset($rowData['pit_tax_not_deposited'])) {
                            $dividendData['non_deposited_personal_income_tax'] = $rowData['pit_tax_not_deposited'];
                        }
                        if (isset($rowData['pit_tax_deposited'])) {
                            $dividendData['deposited_personal_income_tax'] = $rowData['pit_tax_deposited'];
                        }
                        
                        // Add payment_date and dividend_price_per_share from parameters
                        if ($paymentDate) {
                            $dividendData['payment_date'] = $paymentDate;
                        }
                        if ($dividendPricePerShare !== null) {
                            $dividendData['dividend_price_per_share'] = $dividendPricePerShare;
                            
                            // Tính phần trăm cổ tức (dividend_percentage)
                            // Công thức: giá cổ tức / (tiền / số cổ phiếu)
                            $nonDepositedAmount = (float)($dividendData['non_deposited_amount_before_tax'] ?? 0);
                            $nonDepositedShares = (float)($dividendData['non_deposited_shares_quantity'] ?? 0);
                            $depositedAmount = (float)($dividendData['deposited_amount_before_tax'] ?? 0);
                            $depositedShares = (float)($dividendData['deposited_shares_quantity'] ?? 0);
                            $pricePerShare = (float)$dividendPricePerShare;
                            
                            Log::info("===== TÍNH DIVIDEND PERCENTAGE =====");
                            Log::info("Dữ liệu đầu vào:");
                            Log::info("  - Giá cổ tức (pricePerShare): " . $pricePerShare);
                            Log::info("  - Tiền chưa lưu ký (nonDepositedAmount): " . $nonDepositedAmount);
                            Log::info("  - Số cổ phiếu chưa lưu ký (nonDepositedShares): " . $nonDepositedShares);
                            Log::info("  - Tiền đã lưu ký (depositedAmount): " . $depositedAmount);
                            Log::info("  - Số cổ phiếu đã lưu ký (depositedShares): " . $depositedShares);
                            
                            // Tính phần trăm cho cổ phiếu chưa lưu ký
                            if ($nonDepositedShares > 0 && $nonDepositedAmount > 0) {
                                $pricePerShareCalc = $nonDepositedAmount / $nonDepositedShares;
                                $percentage = ($pricePerShareCalc / $pricePerShare) * 100;
                                
                                Log::info("Trường hợp: CHƯA LƯU KÝ");
                                Log::info("  - Tiền/Cổ phiếu = " . $nonDepositedAmount . " / " . $nonDepositedShares . " = " . $pricePerShareCalc);
                                Log::info("  - Công thức: " . $pricePerShareCalc . " / " . $pricePerShare . " = " . $percentage);

                                $dividendData['dividend_percentage'] = round($percentage, 4);
                                Log::info("  - Kết quả (làm tròn 4 chữ số): " . $dividendData['dividend_percentage']);
                            } elseif ($depositedShares > 0 && $depositedAmount > 0) {
                                // Nếu không có cổ phiếu chưa lưu ký, dùng cổ phiếu đã lưu ký
                                $pricePerShareCalc = $depositedAmount / $depositedShares;
                                $percentage = ($pricePerShareCalc / $pricePerShare) * 100;
                                
                                Log::info("Trường hợp: ĐÃ LƯU KÝ");
                                Log::info("  - Tiền/Cổ phiếu = " . $depositedAmount . " / " . $depositedShares . " = " . $pricePerShareCalc);
                                Log::info("  - Công thức: " . $pricePerShareCalc . " / " . $pricePerShare . " = " . $percentage);
                                
                                $dividendData['dividend_percentage'] = round($percentage, 4);
                                Log::info("  - Kết quả (làm tròn 4 chữ số): " . $dividendData['dividend_percentage']);
                            } else {
                                Log::info("Không thể tính toán: Thiếu dữ liệu");
                            }
                            Log::info("=====================================");
                        }
                        
                        if (!empty($dataToUpdate)) {
                            $existing->update($dataToUpdate);
                            Log::info("Updated investor: SID=$sid, Registration=$registrationNumber, Fields: " . implode(', ', array_keys($dataToUpdate)));
                        }
                        
                        // Tạo bản ghi DividendRecord mới
                        DividendRecord::create($dividendData);
                        Log::info("Created dividend record for investor: SID=$sid, Registration=$registrationNumber");
                        
                        $processedRows['updated']++;
                    } else {
                        // Insert record mới trong SecuritiesManagement
                        // Loại bỏ các field dividend khỏi dữ liệu insert
                        $dividendFields = ['not_deposited_quantity', 'deposited_quantity', 'total_quantity',
                                         'pre_tax_payment_not_deposited', 'pre_tax_payment_deposited', 'pre_tax_payment_total',
                                         'pit_tax_not_deposited', 'pit_tax_deposited', 'pit_tax_total',
                                         'post_tax_payment_not_deposited', 'post_tax_payment_deposited', 'post_tax_payment_total'];
                        
                        $securitiesData = array_diff_key($rowData, array_flip($dividendFields));
                        
                        $newSecurities = SecuritiesManagement::create($securitiesData);
                        Log::info("Created new SecuritiesManagement: SID=$sid, Registration=$registrationNumber");
                        
                        // Tạo bản ghi DividendRecord cho investor mới
                        $dividendData = [
                            'securities_management_id' => $newSecurities->id,
                            'non_deposited_shares_quantity' => isset($rowData['not_deposited_quantity']) ? $rowData['not_deposited_quantity'] : 0,
                            'deposited_shares_quantity' => isset($rowData['deposited_quantity']) ? $rowData['deposited_quantity'] : 0,
                            'non_deposited_amount_before_tax' => isset($rowData['pre_tax_payment_not_deposited']) ? $rowData['pre_tax_payment_not_deposited'] : 0,
                            'deposited_amount_before_tax' => isset($rowData['pre_tax_payment_deposited']) ? $rowData['pre_tax_payment_deposited'] : 0,
                            'non_deposited_personal_income_tax' => isset($rowData['pit_tax_not_deposited']) ? $rowData['pit_tax_not_deposited'] : 0,
                            'deposited_personal_income_tax' => isset($rowData['pit_tax_deposited']) ? $rowData['pit_tax_deposited'] : 0,
                            'payment_status' => 'unpaid'
                        ];
                        
                        Log::info("===== CHUẨN BỊ DỮ LIỆU DIVIDEND (INSERT MỚI) =====");
                        Log::info("SID: " . $sid . ", Dòng: " . ($rowIdx + 1));
                        Log::info("Dữ liệu dividend từ Excel:");
                        Log::info("  - Số cổ phiếu chưa lưu ký: " . $dividendData['non_deposited_shares_quantity']);
                        Log::info("  - Số cổ phiếu đã lưu ký: " . $dividendData['deposited_shares_quantity']);
                        Log::info("  - Tiền chưa lưu ký: " . $dividendData['non_deposited_amount_before_tax']);
                        Log::info("  - Tiền đã lưu ký: " . $dividendData['deposited_amount_before_tax']);
                        
                        // Add payment_date and dividend_price_per_share from parameters
                        if ($paymentDate) {
                            $dividendData['payment_date'] = $paymentDate;
                            Log::info("  - Ngày thanh toán: " . $paymentDate);
                        }
                        if ($dividendPricePerShare !== null) {
                            $dividendData['dividend_price_per_share'] = $dividendPricePerShare;
                            Log::info("  - Giá cổ tức: " . $dividendPricePerShare);
                            
                            // Tính phần trăm cổ tức (dividend_percentage)
                            // Công thức: (tiền / số cổ phiếu) / giá cổ tức * 100
                            $nonDepositedAmount = (float)($dividendData['non_deposited_amount_before_tax'] ?? 0);
                            $nonDepositedShares = (float)($dividendData['non_deposited_shares_quantity'] ?? 0);
                            $depositedAmount = (float)($dividendData['deposited_amount_before_tax'] ?? 0);
                            $depositedShares = (float)($dividendData['deposited_shares_quantity'] ?? 0);
                            $pricePerShare = (float)$dividendPricePerShare;
                            
                            Log::info("Dữ liệu tính toán:");
                            Log::info("  - Giá cổ tức (pricePerShare): " . $pricePerShare);
                            Log::info("  - Tiền chưa lưu ký (nonDepositedAmount): " . $nonDepositedAmount);
                            Log::info("  - Số cổ phiếu chưa lưu ký (nonDepositedShares): " . $nonDepositedShares);
                            Log::info("  - Tiền đã lưu ký (depositedAmount): " . $depositedAmount);
                            Log::info("  - Số cổ phiếu đã lưu ký (depositedShares): " . $depositedShares);
                            
                            // Tính phần trăm cho cổ phiếu chưa lưu ký
                            if ($nonDepositedShares > 0 && $nonDepositedAmount > 0) {
                                $pricePerShareCalc = $nonDepositedAmount / $nonDepositedShares;
                                $percentage = ($pricePerShareCalc / $pricePerShare) * 100;
                                
                                Log::info("Trường hợp: CHƯA LƯU KÝ");
                                Log::info("  - Tiền/Cổ phiếu = " . $nonDepositedAmount . " / " . $nonDepositedShares . " = " . $pricePerShareCalc);
                                Log::info("  - Công thức: (" . $pricePerShareCalc . " / " . $pricePerShare . ") * 100 = " . $percentage);
                                
                                $dividendData['dividend_percentage'] = round($percentage, 4);
                                Log::info("  - Kết quả (làm tròn 4 chữ số): " . $dividendData['dividend_percentage']);
                            } elseif ($depositedShares > 0 && $depositedAmount > 0) {
                                // Nếu không có cổ phiếu chưa lưu ký, dùng cổ phiếu đã lưu ký
                                $pricePerShareCalc = $depositedAmount / $depositedShares;
                                $percentage = ($pricePerShareCalc / $pricePerShare) * 100;
                                
                                Log::info("Trường hợp: ĐÃ LƯU KÝ");
                                Log::info("  - Tiền/Cổ phiếu = " . $depositedAmount . " / " . $depositedShares . " = " . $pricePerShareCalc);
                                Log::info("  - Công thức: (" . $pricePerShareCalc . " / " . $pricePerShare . ") * 100 = " . $percentage);
                                
                                $dividendData['dividend_percentage'] = round($percentage, 4);
                                Log::info("  - Kết quả (làm tròn 4 chữ số): " . $dividendData['dividend_percentage']);
                            } else {
                                Log::info("Không thể tính toán: Thiếu dữ liệu");
                            }
                        } else {
                            Log::info("  - Giá cổ tức: KHÔNG CÓ (null)");
                        }
                        Log::info("=================================================");
                        
                        DividendRecord::create($dividendData);
                        Log::info("Created dividend record for new investor: SID=$sid, Registration=$registrationNumber");
                        
                        $processedRows['inserted']++;
                    }
                } catch (\Exception $e) {
                    $processedRows['failed']++;
                    $error = "Lỗi tại dòng " . ($rowIdx + 1) . " trong block $blockName: " . $e->getMessage();
                    $errors[] = $error;
                    Log::error($error);
                }
            }
        }

        return [
            'processedRows' => $processedRows,
            'errors' => $errors
        ];
    }

    // Loại bỏ xử lý dòng, chỉ giữ lại hàm getColumnIndexes
    public function onRow(Row $row)
    {
        // Không xử lý gì
    }

    // Đã thay thế bằng logic log dòng header trong registerEvents

    // Xoá hoàn toàn hàm mapHeaders và các biến liên quan
}
