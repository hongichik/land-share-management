<?php

namespace App\Imports;

use App\Models\SecuritiesManagement;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Throwable;

class CoDongImport implements ToModel, WithHeadingRow
{
    private $config;
    private $columnMappings = [];
    private $processedRows = 0;
    private $errors = [];
    
    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Load configuration from import_codong.json
     */
    private function loadConfig(): void
    {
        $configPath = base_path('app/Imports/import_codong.json');
        
        if (!file_exists($configPath)) {
            throw new \Exception("Configuration file not found: {$configPath}");
        }
        
        $json = file_get_contents($configPath);
        $this->config = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in configuration file: " . json_last_error_msg());
        }

        // Build column mappings
        foreach ($this->config['column_mappings'] as $mapping) {
            if ($mapping['internal_field'] !== null && $mapping['type'] !== 'skip') {
                $this->columnMappings[$mapping['internal_field']] = [
                    'header' => strtolower($mapping['header_text']),
                    'type' => $mapping['type'],
                    'required' => $mapping['required'] ?? false,
                    'default' => $mapping['default'] ?? null,
                ];
            }
        }
    }

    /**
     * Model transformation - process each row
     * 
     * @param array $row
     * @return SecuritiesManagement|null
     */
    public function model(array $row)
    {
        try {
            // Normalize header keys to lowercase
            $row = array_change_key_case($row, CASE_LOWER);

            // Extract data from row based on mappings
            $data = $this->extractRowData($row);

            // Validate required fields
            if (empty($data['full_name'])) {
                $this->addError($this->processedRows + 2, "Họ tên không được để trống");
                return null;
            }

            // Apply default values
            $data = $this->applyDefaults($data);

            // Xóa các bản ghi cũ dựa trên full_name và registration_number (nếu có)
            $query = SecuritiesManagement::where('full_name', $data['full_name']);

            if (!empty($data['registration_number'])) {
                $query = $query->orWhere('registration_number', $data['registration_number']);
            }

            $query->delete();

            // Tạo bản ghi mới
            return new SecuritiesManagement($data);

        } catch (Throwable $e) {
            $this->addError($this->processedRows + 2, $e->getMessage());
            Log::error("Error processing row", [
                'row_number' => $this->processedRows + 2,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Extract row data based on column mappings
     * 
     * @param array $row
     * @return array
     */
    private function extractRowData(array $row): array
    {
        $data = [];
        
        foreach ($this->columnMappings as $field => $mapping) {
            $value = $row[$mapping['header']] ?? null;
            
            // Check if value is truly empty (null or empty string, but allow 0, "0", false)
            $isEmpty = $value === null || $value === '';
            
            // Skip if value is truly empty and not required
            if ($isEmpty && !$mapping['required']) {
                // Set default value if specified
                if ($mapping['default'] !== null) {
                    $data[$field] = $mapping['default'];
                }
                continue;
            }

            // Type conversion - always attempt conversion if we have a value
            if (!$isEmpty) {
                $value = $this->convertType($value, $mapping['type']);
                
                if ($value !== null) {
                    $data[$field] = $value;
                } elseif ($mapping['default'] !== null) {
                    // Use default if conversion failed
                    $data[$field] = $mapping['default'];
                }
            } elseif ($mapping['default'] !== null) {
                // Use default for required fields that are empty
                $data[$field] = $mapping['default'];
            }
        }
        
        $this->processedRows++;
        return $data;
    }

    /**
     * Convert value based on type definition
     * 
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    private function convertType($value, string $type)
    {
        if ($value === null || $value === '') {
            return null;
        }

        switch ($type) {
            case 'integer':
                return intval($value);
                
            case 'date':
                return $this->parseDate($value);
                
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
                
            case 'string':
            default:
                return trim($value);
        }
    }

    /**
     * Parse date from multiple formats
     * 
     * @param mixed $value
     * @return string|null
     */
    private function parseDate($value)
    {
        if (is_numeric($value)) {
            // Excel date format
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y'];
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, trim($value));
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }
        
        return null;
    }

    /**
     * Apply default values from config
     * 
     * @param array $data
     * @return array
     */
    private function applyDefaults(array $data): array
    {
        // Ensure all required non-null fields have values
        $requiredFields = [
            'registration_number' => '',
            'bank_account' => '',
            'bank_name' => '',
        ];
        
        foreach ($requiredFields as $field => $default) {
            if (!isset($data[$field]) || $data[$field] === null) {
                $data[$field] = $default;
            }
        }

        if (isset($this->config['default_values'])) {
            foreach ($this->config['default_values'] as $field => $value) {
                if (!isset($data[$field])) {
                    if ($value === 'AUTO_GENERATE') {
                        $data[$field] = $this->generateInvestorCode();
                    } else {
                        $data[$field] = $value;
                    }
                }
            }
        }
        
        return $data;
    }

    /**
     * Generate unique investor code
     * 
     * @return string
     */
    private function generateInvestorCode(): string
    {
        $lastRecord = SecuritiesManagement::latest('id')->first();
        $nextId = ($lastRecord ? $lastRecord->id : 0) + 1;
        
        return 'CD' . date('Y') . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Add error message
     * 
     * @param int $row
     * @param string $message
     */
    private function addError(int $row, string $message): void
    {
        $this->errors[] = [
            'row' => $row,
            'message' => $message
        ];
    }

    /**
     * Get all errors
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get number of processed rows
     * 
     * @return int
     */
    public function getProcessedRows(): int
    {
        return $this->processedRows;
    }

    /**
     * Generate preview data from Excel file
     * Returns array with insert and update information
     * 
     * @param array $rows
     * @return array
     */
    public function getPreviewData(array $rows): array
    {
        $changes = [];
        $insertCount = 0;
        $updateCount = 0;

        foreach ($rows as $index => $row) {
            // Skip empty rows
            if (empty(array_filter($row))) continue;

            // Normalize row keys to lowercase
            $rowData = array_change_key_case(array_filter($row), CASE_LOWER);
            
            Log::info("Processing row $index", ['rowData' => $rowData]);
            
            // Extract full_name and registration_number using field mapping
            $fullName = $rowData[$this->columnMappings['full_name']['header']] ?? null;
            $regNum = isset($this->columnMappings['registration_number']) ? ($rowData[$this->columnMappings['registration_number']['header']] ?? null) : null;
            
            if (!$fullName) {
                Log::info('Row ' . $index . ' skipped - no full name');
                continue;
            }
            
            $fullName = trim($fullName);
            $regNum = trim($regNum ?? '');

            // Check if record exists
            $existing = SecuritiesManagement::where('full_name', $fullName)
                ->orWhere('registration_number', $regNum)
                ->first();

            if ($existing) {
                // Prepare changes
                $changesList = [];
                
                // Check each mapped field for changes
                foreach ($this->columnMappings as $dbField => $mapping) {
                    if (in_array($dbField, ['full_name', 'registration_number'])) continue; // Skip identifier fields
                    
                    $newValue = isset($rowData[$mapping['header']]) ? trim($rowData[$mapping['header']]) : null;
                    $oldValue = $existing->$dbField;
                    
                    // Skip if new value is empty
                    if (!$newValue) continue;
                    
                    // Normalize values for comparison
                    $oldValueNorm = $this->normalizeValue($oldValue, $dbField);
                    $newValueNorm = $this->normalizeValue($newValue, $dbField);
                    
                    // Compare normalized values
                    if ($oldValueNorm !== $newValueNorm) {
                        $changesList[$dbField] = [
                            'old' => $oldValue,
                            'new' => $newValue
                        ];
                    }
                }

                if (!empty($changesList)) {
                    Log::info("Preview UPDATE row", [
                        'full_name' => $fullName,
                        'registration_number' => $regNum,
                        'id' => $existing->id,
                        'changes' => $changesList
                    ]);
                    $changes[] = [
                        'type' => 'update',
                        'full_name' => $fullName,
                        'registration_number' => $regNum,
                        'id' => $existing->id,
                        'changes' => $changesList
                    ];
                    $updateCount++;
                }
            } else {
                // New record - collect all data
                $newData = [];
                foreach ($this->columnMappings as $dbField => $mapping) {
                    if (isset($rowData[$mapping['header']])) {
                        $value = trim($rowData[$mapping['header']]);
                        // Convert type for numeric fields
                        if ($mapping['type'] === 'integer' && !empty($value)) {
                            $value = intval($value);
                        }
                        $newData[$dbField] = $value;
                    } elseif ($mapping['type'] === 'integer' && isset($mapping['default'])) {
                        // Include default values for integer fields
                        $newData[$dbField] = $mapping['default'];
                    }
                }
                
                Log::info("Preview INSERT row", [
                    'full_name' => $fullName,
                    'registration_number' => $regNum,
                    'not_deposited_quantity' => $newData['not_deposited_quantity'] ?? null,
                    'deposited_quantity' => $newData['deposited_quantity'] ?? null,
                    'all_data' => $newData
                ]);
                
                $changes[] = [
                    'type' => 'insert',
                    'full_name' => $fullName,
                    'registration_number' => $regNum,
                    'data' => $newData
                ];
                $insertCount++;
            }
        }

        Log::info('Import preview complete', ['insertCount' => $insertCount, 'updateCount' => $updateCount, 'totalChanges' => count($changes)]);

        return [
            'preview' => $changes,
            'insertCount' => $insertCount,
            'updateCount' => $updateCount,
            'totalRows' => $insertCount + $updateCount
        ];
    }

    /**
     * Normalize value for comparison (handle different date formats)
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

}
