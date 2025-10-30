<?php

namespace App\Http\Controllers\Admin\Securities;

use App\Http\Controllers\Controller;
use App\Models\SecuritiesManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class SecuritiesManagementController extends Controller
{
    /**
     * Get summary statistics for dashboard
     */
    public function getSummaryStats(Request $request)
    {
        $filter = $request->input('filter', 'all');
        
        // Tính tổng số cổ phần của tất cả cổ đông
        $totalShares = SecuritiesManagement::selectRaw('SUM(COALESCE(not_deposited_quantity, 0) + COALESCE(deposited_quantity, 0)) as total')
            ->value('total') ?? 1;
        
        $query = SecuritiesManagement::query();
        
        // Áp dụng bộ lọc dựa trên tỷ lệ phần trăm
        if ($filter === 'large') {
            // Cổ đông lớn: tỷ lệ cổ phần >= 5%
            $query->whereRaw('((COALESCE(not_deposited_quantity, 0) + COALESCE(deposited_quantity, 0)) / ' . $totalShares . ' * 100) >= 5');
        } elseif ($filter === 'small') {
            // Cổ đông nhỏ: tỷ lệ cổ phần < 5%
            $query->whereRaw('((COALESCE(not_deposited_quantity, 0) + COALESCE(deposited_quantity, 0)) / ' . $totalShares . ' * 100) < 5');
        }
        
        $totalInvestors = $query->count();
        $notDepositedTotal = $query->sum('not_deposited_quantity');
        $depositedTotal = $query->sum('deposited_quantity');

        return response()->json([
            'total_investors' => number_format($totalInvestors),
            'active_investors' => number_format($totalInvestors),
            'not_deposited' => number_format($notDepositedTotal),
            'deposited' => number_format($depositedTotal),
            'active_percentage' => $totalInvestors > 0 ? round(($totalInvestors / $totalInvestors) * 100, 1) : 0,
            'deposited_percentage' => ($notDepositedTotal + $depositedTotal) > 0 ? round(($depositedTotal / ($notDepositedTotal + $depositedTotal)) * 100, 1) : 0
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $filter = $request->input('filter', 'all');
            
            // Handle search parameter - DataTables sends it as search[value]
            $searchParam = $request->input('search');
            if (is_array($searchParam)) {
                $search = trim($searchParam['value'] ?? '');
            } else {
                $search = trim($searchParam ?? '');
            }
            
            // Tính tổng số cổ phần của tất cả cổ đông
            $totalShares = SecuritiesManagement::selectRaw('SUM(COALESCE(not_deposited_quantity, 0) + COALESCE(deposited_quantity, 0)) as total')
                ->value('total') ?? 1;
            
            $securities = SecuritiesManagement::select([
                'id',
                'full_name',
                'address',
                'email',
                'phone',
                'nationality',
                'sid',
                'investor_code',
                'registration_number',
                'issue_date',
                'not_deposited_quantity',
                'deposited_quantity',
                'slqmpb_chualk',
                'slqmpb_dalk',
                'cntc',
                'txnum',
                'bank_account',
                'bank_name',
                'notes',
                'created_at'
            ]);

            // Áp dụng bộ lọc dựa trên tỷ lệ phần trăm
            if ($filter === 'large') {
                // Cổ đông lớn: tỷ lệ cổ phần >= 5%
                $securities = $securities->whereRaw('((COALESCE(not_deposited_quantity, 0) + COALESCE(deposited_quantity, 0)) / ' . $totalShares . ' * 100) >= 5');
            } elseif ($filter === 'small') {
                // Cổ đông nhỏ: tỷ lệ cổ phần < 5%
                $securities = $securities->whereRaw('((COALESCE(not_deposited_quantity, 0) + COALESCE(deposited_quantity, 0)) / ' . $totalShares . ' * 100) < 5');
            }

            // Áp dụng tìm kiếm
            if (!empty($search)) {
                $securities = $securities->where(function($query) use ($search) {
                    $query->where('full_name', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%")
                          ->orWhere('phone', 'LIKE', "%{$search}%")
                          ->orWhere('sid', 'LIKE', "%{$search}%")
                          ->orWhere('investor_code', 'LIKE', "%{$search}%")
                          ->orWhere('registration_number', 'LIKE', "%{$search}%");
                });
            }

            return DataTables::of($securities)
                ->addIndexColumn()
                // Cột 1: Thông tin cá nhân
                ->addColumn('group1_personal', function ($row) {
                    return '<div class="group-header group-personal" style="margin-bottom: 5px;">👤 Thông tin cá nhân</div>' .
                        '<div class="group-content">' .
                        '<strong>Tên:</strong> ' . $row->full_name . '<br>' .
                        '<strong>Địa chỉ:</strong> ' . $row->address . '<br>' .
                        '<strong>Điện thoại:</strong> ' . ($row->phone ?? 'N/A') . '<br>' .
                        '<strong>Email:</strong> ' . ($row->email ?? 'N/A') . '<br>' .
                        '<strong>Quốc tịch:</strong> ' . ($row->nationality ?? 'N/A') . 
                        '</div>';
                })
                // Cột 2: Thông tin đầu tư
                ->addColumn('group2_investor', function ($row) {
                    return '<div class="group-header group-investor" style="margin-bottom: 5px;">📊 Thông tin đầu tư</div>' .
                        '<div class="group-content">' .
                        '<strong>SID:</strong> ' . ($row->sid ?? 'N/A') . '<br>' .
                        '<strong>Mã NĐT:</strong> ' . ($row->investor_code ?? 'N/A') . '<br>' .
                        '<strong>Số ĐK:</strong> ' . ($row->registration_number ?? 'N/A') . '<br>' .
                        '<strong>Ngày PH:</strong> ' . ($row->issue_date ? $row->issue_date->format('d/m/Y') : 'N/A') . '<br>' .
                        '</div>';
                })
                // Cột 3: Số lượng lưu ký
                ->addColumn('group3_deposited', function ($row) {
                    $total = ($row->not_deposited_quantity ?? 0) + ($row->deposited_quantity ?? 0);
                    return '<div class="group-header group-deposited" style="margin-bottom: 5px;">📦 Số lượng lưu ký</div>' .
                        '<div class="group-content">' .
                        '<strong>Chưa LK:</strong> ' . number_format($row->not_deposited_quantity ?? 0) . '<br>' .
                        '<strong>Đã LK:</strong> ' . number_format($row->deposited_quantity ?? 0) . '<br>' .
                        '<strong style="color: #28a745;">Tổng:</strong> ' . number_format($total) . 
                        '</div>';
                })
                // Cột 4: Quyền mua chứng chỉ
                ->addColumn('group4_options', function ($row) {
                    $total = ($row->slqmpb_chualk ?? 0) + ($row->slqmpb_dalk ?? 0);
                    return '<div class="group-header group-options" style="margin-bottom: 5px; color: #000;">💳 Quyền mua CC</div>' .
                        '<div class="group-content">' .
                        '<strong>Chưa LK:</strong> ' . number_format($row->slqmpb_chualk ?? 0) . '<br>' .
                        '<strong>Đã LK:</strong> ' . number_format($row->slqmpb_dalk ?? 0) . '<br>' .
                        '<strong style="color: #ff9800;">Tổng:</strong> ' . number_format($total) . 
                        '</div>';
                })
                // Cột 5: Phân loại
                ->addColumn('group5_classification', function ($row) {
                    return '<div class="group-header group-classification" style="margin-bottom: 5px;">🏷️ Phân loại</div>' .
                        '<div class="group-content">' .
                        '<strong>CNTC:</strong> ' . ($row->cntc == '1' ? 'Cá nhân (CN)' : ($row->cntc == '2' ? 'Tổ chức (TC)' : ($row->cntc ?? 'N/A'))) . '<br>' .
                        '<strong>TXNUM:</strong> ' . ($row->txnum ?? 'N/A') . 
                        '</div>';
                })
                // Cột 7: Ghi chú
                ->addColumn('group7_notes', function ($row) {
                    $notes = $row->notes ?? 'N/A';
                    $shortNotes = strlen($notes) > 50 ? substr($notes, 0, 50) . '...' : $notes;
                    return '<div class="group-header group-notes" style="margin-bottom: 5px;">📝 Ghi chú</div>' .
                        '<div class="group-content" title="' . htmlspecialchars($notes) . '">' . 
                        htmlspecialchars($shortNotes) . 
                        '</div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('admin.securities.management.show', $row->id) . '" class="btn btn-info btn-sm" title="Xem chi tiết">';
                    $btn .= '<i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.securities.management.edit', $row->id) . '" class="btn btn-warning btn-sm" title="Sửa">';
                    $btn .= '<i class="fas fa-edit"></i></a>';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord(' . $row->id . ')" title="Xóa">';
                    $btn .= '<i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['group1_personal', 'group2_investor', 'group3_deposited', 'group4_options', 'group5_classification', 'group6_bank', 'group7_notes', 'action'])
                ->make(true);
        }

        return view('admin.securities.management.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.securities.management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'sid' => 'required|string|max:255|unique:securities_management,sid',
            'investor_code' => 'required|string|max:255|unique:securities_management,investor_code',
            'registration_number' => 'required|string|max:255|unique:securities_management,registration_number',
            'issue_date' => 'required|date',
            'address' => 'required|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'not_deposited_quantity' => 'nullable|integer|min:0',
            'deposited_quantity' => 'nullable|integer|min:0',
            'slqmpb_chualk' => 'nullable|integer|min:0',
            'slqmpb_dalk' => 'nullable|integer|min:0',
            'cntc' => 'nullable|string|max:10',
            'txnum' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string'
        ], [
            'full_name.required' => 'Tên đầy đủ là bắt buộc',
            'sid.required' => 'Mã SID là bắt buộc',
            'sid.unique' => 'Mã SID đã tồn tại trong hệ thống',
            'investor_code.required' => 'Mã nhà đầu tư là bắt buộc',
            'investor_code.unique' => 'Mã nhà đầu tư đã tồn tại trong hệ thống',
            'registration_number.required' => 'Số đăng ký là bắt buộc',
            'registration_number.unique' => 'Số đăng ký đã tồn tại trong hệ thống',
            'issue_date.required' => 'Ngày phát hành là bắt buộc',
            'address.required' => 'Địa chỉ là bắt buộc'
        ]);

        SecuritiesManagement::create($request->all());

        return redirect()->route('admin.securities.management.index')
            ->with('success', 'Thêm thông tin quản lý chứng khoán thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SecuritiesManagement $securitiesManagement)
    {
        return view('admin.securities.management.show', compact('securitiesManagement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SecuritiesManagement $securitiesManagement)
    {
        return view('admin.securities.management.edit', compact('securitiesManagement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SecuritiesManagement $securitiesManagement)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'sid' => 'required|string|max:255|unique:securities_management,sid,' . $securitiesManagement->id,
            'investor_code' => 'required|string|max:255|unique:securities_management,investor_code,' . $securitiesManagement->id,
            'registration_number' => 'required|string|max:255|unique:securities_management,registration_number,' . $securitiesManagement->id,
            'issue_date' => 'required|date',
            'address' => 'required|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'not_deposited_quantity' => 'nullable|integer|min:0',
            'deposited_quantity' => 'nullable|integer|min:0',
            'slqmpb_chualk' => 'nullable|integer|min:0',
            'slqmpb_dalk' => 'nullable|integer|min:0',
            'cntc' => 'nullable|string|max:10',
            'txnum' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string'
        ], [
            'full_name.required' => 'Tên đầy đủ là bắt buộc',
            'sid.required' => 'Mã SID là bắt buộc',
            'sid.unique' => 'Mã SID đã tồn tại trong hệ thống',
            'investor_code.required' => 'Mã nhà đầu tư là bắt buộc',
            'investor_code.unique' => 'Mã nhà đầu tư đã tồn tại trong hệ thống',
            'registration_number.required' => 'Số đăng ký là bắt buộc',
            'registration_number.unique' => 'Số đăng ký đã tồn tại trong hệ thống',
            'issue_date.required' => 'Ngày phát hành là bắt buộc',
            'address.required' => 'Địa chỉ là bắt buộc'
        ]);

        $securitiesManagement->update($request->all());

        return redirect()->route('admin.securities.management.index')
            ->with('success', 'Cập nhật thông tin quản lý chứng khoán thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SecuritiesManagement $securitiesManagement)
    {
        $securitiesManagement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa thông tin quản lý chứng khoán thành công!'
        ]);
    }

    /**
     * Get a list of investors for Select2 dropdown
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvestorsList(Request $request)
    {
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = 10;
        
        $query = SecuritiesManagement::query();
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('investor_code', 'LIKE', "%{$search}%")
                  ->orWhere('sid', 'LIKE', "%{$search}%");
            });
        }
        
        $total = $query->count();
        $investors = $query->orderBy('full_name')
                           ->offset(($page - 1) * $perPage)
                           ->limit($perPage)
                           ->get(['id', 'full_name', 'investor_code', 'sid']);
        
        return response()->json([
            'investors' => $investors,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Preview import data from Excel file
     */
    public function importPreview(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['success' => false, 'error' => 'Vui lòng chọn file'], 400);
            }

            $file = $request->file('file');
            
            // Validate file type
            if (!in_array($file->getClientOriginalExtension(), ['xlsx', 'xls', 'csv'])) {
                return response()->json(['success' => false, 'error' => 'File phải có định dạng .xlsx, .xls hoặc .csv'], 400);
            }

            // Read Excel file
            $rows = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\CoDongImport(), $file)[0] ?? [];
            
            Log::info('Import preview - Total rows read:', ['count' => count($rows)]);
            
            // Get preview data from CoDongImport
            $importer = new \App\Imports\CoDongImport();
            $result = $importer->getPreviewData($rows);

            return response()->json([
                'success' => true,
                'preview' => $result['preview'],
                'insertCount' => $result['insertCount'],
                'updateCount' => $result['updateCount'],
                'totalRows' => $result['totalRows'],
                'message' => 'Tìm thấy ' . $result['insertCount'] . ' nhà đầu tư mới và ' . $result['updateCount'] . ' nhà đầu tư cần cập nhật'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import preview error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'error' => 'Lỗi xử lý file: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Confirm and execute import
     */
    public function importConfirm(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['error' => 'Vui lòng chọn file'], 400);
            }

            $file = $request->file('file');
            
            $import = new \App\Imports\CoDongImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);

            return response()->json([
                'success' => true,
                'message' => 'Import dữ liệu thành công!',
                'processedRows' => $import->getProcessedRows(),
                'errors' => $import->getErrors()
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import confirm error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Lỗi xử lý file: ' . $e->getMessage()], 400);
        }
    }
}
