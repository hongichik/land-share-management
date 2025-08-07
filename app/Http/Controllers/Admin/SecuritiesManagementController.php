<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecuritiesManagement;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SecuritiesManagementController extends Controller
{
    /**
     * Get summary statistics for dashboard
     */
    public function getSummaryStats()
    {
        
        $totalInvestors = SecuritiesManagement::count();
        $activeInvestors = SecuritiesManagement::where('status', 1)->count();
        $notDepositedTotal = SecuritiesManagement::sum('not_deposited_quantity');
        $depositedTotal = SecuritiesManagement::sum('deposited_quantity');

        return response()->json([
            'total_investors' => number_format($totalInvestors),
            'active_investors' => number_format($activeInvestors),
            'not_deposited' => number_format($notDepositedTotal),
            'deposited' => number_format($depositedTotal),
            'active_percentage' => $totalInvestors > 0 ? round(($activeInvestors / $totalInvestors) * 100, 1) : 0,
            'deposited_percentage' => ($notDepositedTotal + $depositedTotal) > 0 ? round(($depositedTotal / ($notDepositedTotal + $depositedTotal)) * 100, 1) : 0
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $securities = SecuritiesManagement::select([
                'id',
                'full_name',
                'sid',
                'investor_code',
                'registration_number',
                'issue_date',
                'not_deposited_quantity',
                'deposited_quantity',
                'status',
                'created_at'
            ]);

            return DataTables::of($securities)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    $badgeClass = $row->status == 1 ? 'success' : 'danger';
                    return '<span class="badge badge-' . $badgeClass . '">' . $row->status_text . '</span>';
                })
                ->addColumn('deposit_badge', function ($row) {
                    if ($row->not_deposited_quantity > 0) {
                        $badgeClass = 'warning';
                    } elseif ($row->deposited_quantity > 0) {
                        $badgeClass = 'success';
                    } else {
                        $badgeClass = 'secondary';
                    }
                    return '<span class="badge badge-' . $badgeClass . '">' . $row->deposit_status_text . '</span>';
                })
                ->addColumn('quantities', function ($row) {
                    return 'Chưa lưu ký: ' . number_format($row->not_deposited_quantity) .
                        '<br>Đã lưu ký: ' . number_format($row->deposited_quantity);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('admin.securities.management.show', $row->id) . '" class="btn btn-info btn-sm" title="Xem chi tiết">';
                    $btn .= '<i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.securities.management.edit', $row->id) . '" class="btn btn-warning btn-sm" title="Sửa">';
                    $btn .= '<i class="fas fa-edit"></i></a>';
                    $btn .= '<a href="' . route('admin.securities.management.dividend-histories', $row->id) . '" class="btn btn-success btn-sm" title="Lịch sử cổ tức">';
                    $btn .= '<i class="fas fa-money-bill"></i></a>';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord(' . $row->id . ')" title="Xóa">';
                    $btn .= '<i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'deposit_badge', 'quantities', 'action'])
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
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'not_deposited_quantity' => 'nullable|integer|min:0',
            'deposited_quantity' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|integer|in:0,1'
        ], [
            'full_name.required' => 'Tên đầy đủ là bắt buộc',
            'sid.required' => 'Mã SID là bắt buộc',
            'sid.unique' => 'Mã SID đã tồn tại trong hệ thống',
            'investor_code.required' => 'Mã nhà đầu tư là bắt buộc',
            'investor_code.unique' => 'Mã nhà đầu tư đã tồn tại trong hệ thống',
            'registration_number.required' => 'Số đăng ký là bắt buộc',
            'registration_number.unique' => 'Số đăng ký đã tồn tại trong hệ thống',
            'issue_date.required' => 'Ngày phát hành là bắt buộc',
            'address.required' => 'Địa chỉ là bắt buộc',
            'status.required' => 'Trạng thái là bắt buộc'
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
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'not_deposited_quantity' => 'nullable|integer|min:0',
            'deposited_quantity' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|integer|in:0,1'
        ], [
            'full_name.required' => 'Tên đầy đủ là bắt buộc',
            'sid.required' => 'Mã SID là bắt buộc',
            'sid.unique' => 'Mã SID đã tồn tại trong hệ thống',
            'investor_code.required' => 'Mã nhà đầu tư là bắt buộc',
            'investor_code.unique' => 'Mã nhà đầu tư đã tồn tại trong hệ thống',
            'registration_number.required' => 'Số đăng ký là bắt buộc',
            'registration_number.unique' => 'Số đăng ký đã tồn tại trong hệ thống',
            'issue_date.required' => 'Ngày phát hành là bắt buộc',
            'address.required' => 'Địa chỉ là bắt buộc',
            'status.required' => 'Trạng thái là bắt buộc'
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
}
