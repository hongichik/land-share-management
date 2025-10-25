<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{

    // Danh sách các trường column_name cho bảng securities_management
    private $securitiesColumns = [
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
        'bank_account',
        'bank_name',
        'bank_branch',
        'notes',
        'status',
    ];

    /**
     * Display a listing of the settings for securities_management.
     */
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->input('settings', []);
            foreach ($data as $id => $item) {
                $setting = Setting::find($id);
                if ($setting && $setting->table === 'securities_management' && in_array($setting->column_name, $this->securitiesColumns)) {
                    $setting->update([
                        'title_excel' => $item['title_excel'] ?? '',
                        'des' => $item['des'] ?? '',
                    ]);
                }
            }
            return redirect()->route('admin.settings.index')->with('success', 'Cập nhật thành công!');
        }
        $settings = Setting::where('table', 'securities_management')
            ->whereIn('column_name', $this->securitiesColumns)
            ->orderByRaw("FIELD(column_name, '" . implode("','", $this->securitiesColumns) . "')")
            ->get();
        return view('admin.settings.index', [
            'settings' => $settings,
            'columns' => $this->securitiesColumns
        ]);
    }


    /**
     * Show the form for editing the specified setting.
     */
    public function edit(Setting $setting)
    {
        // Chỉ cho phép sửa các trường thuộc securities_management
        if ($setting->table !== 'securities_management' || !in_array($setting->column_name, $this->securitiesColumns)) {
            abort(404);
        }
        return view('admin.settings.edit', compact('setting'));
    }


    /**
     * Update the specified setting in storage.
     */
    public function update(Request $request, Setting $setting)
    {
        // Chỉ cho phép sửa các trường thuộc securities_management
        if ($setting->table !== 'securities_management' || !in_array($setting->column_name, $this->securitiesColumns)) {
            abort(404);
        }
        $request->validate([
            'title_excel' => 'nullable|string|max:255',
            'des' => 'nullable|string',
        ]);
        $setting->update([
            'title_excel' => $request->input('title_excel'),
            'des' => $request->input('des'),
        ]);
        return redirect()->route('admin.settings.index')->with('success', 'Cập nhật cấu hình thành công!');
    }

    // Không cho phép thêm mới hay xoá setting ở đây
}
