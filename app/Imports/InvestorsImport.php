<?php

namespace App\Imports;

use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeSheet;

class InvestorsImport implements OnEachRow, WithEvents, WithMultipleSheets, WithTitle
{
    private $sheetName;
    private $config;
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

    public function registerEvents(): array
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

        return [
            BeforeSheet::class => function(BeforeSheet $event) use ($normalize) {
                $sheet = $event->getSheet()->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $allData = $sheet->rangeToArray("A1:" . $highestColumn . $highestRow);

                // Danh sách các trường cần tìm
                $fields = [
                    'full_name', 'sid', 'investor_code', 'registration_number', 'issue_date', 'address', 'email', 'phone', 'nationality',
                    'not_deposited_quantity', 'deposited_quantity', 'total_quantity',
                    'pre_tax_payment_not_deposited', 'pre_tax_payment_deposited', 'pre_tax_payment_total',
                    'pit_tax_not_deposited', 'pit_tax_deposited', 'pit_tax_total',
                    'post_tax_payment_not_deposited', 'post_tax_payment_deposited', 'post_tax_payment_total',
                    'bank_account', 'bank_name', 'bank_branch', 'notes', 'status'
                ];

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

                $start_end = [
                    'moi_gioi_ca_nhan_trong_nuoc' => [
                        'start' => isset($result['investor_type_individual_domestic'][0]) ? $result['investor_type_individual_domestic'][0] + 1 : null,
                        'end' => isset($result['investor_type_organization_domestic'][0]) ? $result['investor_type_organization_domestic'][0] - 1 : null
                    ],
                    'to_chuc_trong_nuoc' => [
                        'start' => isset($result['investor_type_organization_domestic'][0]) ? $result['investor_type_organization_domestic'][0] + 1 : null,
                        'end' => isset($result['investor_type_individual_foreign'][0]) ? $result['investor_type_individual_foreign'][0] - 1 : null
                    ],
                    'moi_gioi_ca_nhan_nuoc_ngoai' => [
                        'start' => isset($result['investor_type_individual_foreign'][0]) ? $result['investor_type_individual_foreign'][0] + 1 : null,
                        'end' => isset($result['investor_type_organization_foreign'][0]) ? $result['investor_type_organization_foreign'][0] - 1 : null
                    ],
                    'to_chuc_nuoc_ngoai' => [
                        'start' => isset($result['investor_type_organization_foreign'][0]) ? $result['investor_type_organization_foreign'][0] + 1 : null,
                        'end' => null // Nếu có block tiếp theo thì lấy điểm đầu block đó - 1
                    ]
                ];
                // return $start_end;
                dd($start_end);
            }
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
