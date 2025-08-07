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
                    $btn .= '<a href="' . route('admin.securities-management.show', $row->id) . '" class="btn btn-info btn-sm" title="Xem chi tiết">';
                    $btn .= '<i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.securities-management.edit', $row->id) . '" class="btn btn-warning btn-sm" title="Sửa">';
                    $btn .= '<i class="fas fa-edit"></i></a>';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord(' . $row->id . ')" title="Xóa">';
                    $btn .= '<i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'deposit_badge', 'quantities', 'action'])
                ->make(true);
        }

        return view('admin.securities-management.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.securities-management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'sid' => 'nullable|string|max:255',
            'investor_code' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:255|unique:securities_management,registration_number',
            'issue_date' => 'nullable|date',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'not_deposited_quantity' => 'nullable|integer|min:0',
            'deposited_quantity' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|integer|in:0,1'
        ]);

        SecuritiesManagement::create($request->all());

        return redirect()->route('admin.securities-management.index')
            ->with('success', 'Thêm thông tin quản lý chứng khoán thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SecuritiesManagement $securitiesManagement)
    {
        return view('admin.securities-management.show', compact('securitiesManagement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SecuritiesManagement $securitiesManagement)
    {
        return view('admin.securities-management.edit', compact('securitiesManagement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SecuritiesManagement $securitiesManagement)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'sid' => 'nullable|string|max:255',
            'investor_code' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:255|unique:securities_management,registration_number,' . $securitiesManagement->id,
            'issue_date' => 'nullable|date',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'not_deposited_quantity' => 'nullable|integer|min:0',
            'deposited_quantity' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|integer|in:0,1'
        ]);

        $securitiesManagement->update($request->all());

        return redirect()->route('admin.securities-management.index')
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
}
