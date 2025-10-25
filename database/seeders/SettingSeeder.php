<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $columns = [
            'full_name' => 'Tên đầy đủ nhà đầu tư',
            'sid' => 'Mã SID nhà đầu tư',
            'investor_code' => 'Mã nhà đầu tư',
            'registration_number' => 'Số đăng ký',
            'issue_date' => 'Ngày phát hành',
            'address' => 'Địa chỉ nhà đầu tư',
            'email' => 'Email liên hệ',
            'phone' => 'Số điện thoại liên hệ',
            'nationality' => 'Quốc tịch',
            'not_deposited_quantity' => 'Số lượng chưa lưu ký',
            'deposited_quantity' => 'Số lượng đã lưu ký',
            'bank_account' => 'Số tài khoản ngân hàng',
            'bank_name' => 'Tên ngân hàng',
            'bank_branch' => 'Chi nhánh ngân hàng',
            'notes' => 'Ghi chú',
            'status' => 'Trạng thái hoạt động',
        ];
        foreach ($columns as $col => $desc) {
            Setting::updateOrCreate([
                'table' => 'securities_management',
                'column_name' => $col,
            ], [
                'title_excel' => '',
                'des' => $desc,
            ]);
        }
    }
}
