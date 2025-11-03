<?php

namespace App\Http\Controllers\Admin\LandRental;

use App\Http\Controllers\Controller;
use App\Models\LandRentalContract;
use App\Models\LandRentalPaymentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class LandRentalPaymentHistoryController extends Controller
{
    /**
     * Display a listing of payment histories for a specific contract.
     */
    public function index(Request $request, LandRentalContract $landRentalContract)
    {
        if ($request->ajax()) {
            $paymentHistories = LandRentalPaymentHistory::where('land_rental_contract_id', $landRentalContract->id)
                ->orderBy('id', 'desc')
                ->select([
                    'id',
                    'period',
                    'payment_type',
                    'amount',
                    'payment_date',
                    'notes',
                    'created_at'
                ]);
            
            // Lấy ID của thanh toán mới nhất
            $latestPaymentId = LandRentalPaymentHistory::where('land_rental_contract_id', $landRentalContract->id)
                ->max('id');

            return DataTables::of($paymentHistories)
                ->addIndexColumn()
                ->editColumn('period', function ($item) use ($latestPaymentId) {
                    $periodBadge = $item->period_name;
                    if ($item->id == $latestPaymentId) {
                        $periodBadge .= ' <span class="badge bg-info badge-info ml-1">Mới nhất</span>';
                    }
                    return $periodBadge;
                })
                ->editColumn('payment_type', function ($item) {
                    $badge = '';
                    switch ($item->payment_type) {
                        case LandRentalPaymentHistory::PAYMENT_TYPE_ADVANCE:
                            $badge = '<span class="badge bg-success badge-success">' . $item->payment_type_name . '</span>';
                            break;
                        case LandRentalPaymentHistory::PAYMENT_TYPE_ON_TIME:
                            $badge = '<span class="badge bg-primary badge-primary">' . $item->payment_type_name . '</span>';
                            break;
                        case LandRentalPaymentHistory::PAYMENT_TYPE_EXEMPTION:
                            $badge = '<span class="badge bg-warning badge-warning">' . $item->payment_type_name . '</span>';
                            break;
                        default:
                            $badge = '<span class="badge bg-secondary badge-secondary">' . $item->payment_type_name . '</span>';
                    }
                    return $badge;
                })
                ->editColumn('amount', function ($item) {
                    return number_format($item->amount, 0, ',', '.') . ' VND';
                })
                ->editColumn('payment_date', function ($item) {
                    return $item->payment_date->format('d/m/Y');
                })
                ->editColumn('notes', function ($item) {
                    return $item->notes ? Str::limit($item->notes, 50) : 'Không có ghi chú';
                })
                ->addColumn('action', function ($item) use ($landRentalContract, $latestPaymentId) {
                    $showBtn = '<a href="' . route('admin.land-rental.payment-histories.show', [$landRentalContract, $item]) . '" class="btn btn-info btn-sm" title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                    </a>';
                    
                    $editBtn = '';
                    $deleteBtn = '';
                    
                    // Chỉ cho phép sửa và xóa thanh toán mới nhất
                    if ($item->id == $latestPaymentId) {
                        $editBtn = '<a href="' . route('admin.land-rental.payment-histories.edit', [$landRentalContract, $item]) . '" class="btn btn-warning btn-sm" title="Sửa">
                            <i class="fas fa-edit"></i>
                        </a>';
                        
                        $deleteBtn = '<form method="POST" action="' . route('admin.land-rental.payment-histories.destroy', [$landRentalContract, $item]) . '" style="display:inline-block;" onsubmit="return confirm(\'Bạn có chắc chắn muốn xóa lịch sử thanh toán này?\')">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>';
                    } else {
                        // Hiển thị nút bị vô hiệu hóa cho các record cũ
                        $editBtn = '<button class="btn btn-secondary btn-sm" disabled title="Chỉ được sửa thanh toán mới nhất">
                            <i class="fas fa-edit"></i>
                        </button>';
                        
                        $deleteBtn = '<button class="btn btn-secondary btn-sm" disabled title="Chỉ được xóa thanh toán mới nhất">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return '<div class="btn-group" role="group">' . $showBtn . ' ' . $editBtn . ' ' . $deleteBtn . '</div>';
                })
                ->rawColumns(['period', 'payment_type', 'action'])
                ->make(true);
        }

        return view('admin.land-rental.histories.index', compact('landRentalContract'));
    }

    /**
     * Show the form for creating a new payment history.
     */
    public function create(LandRentalContract $landRentalContract)
    {
        return view('admin.land-rental.histories.create', compact('landRentalContract'));
    }

    /**
     * Store a newly created payment history in storage.
     */
    public function store(Request $request, LandRentalContract $landRentalContract)
    {
        $request->validate([
            'period' => 'required|integer|in:1,2',
            'payment_type' => 'required|integer|in:1,2,3',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        LandRentalPaymentHistory::create([
            'land_rental_contract_id' => $landRentalContract->id,
            'period' => $request->period,
            'payment_type' => $request->payment_type,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.land-rental.payment-histories.index', $landRentalContract)
            ->with('success', 'Thêm lịch sử thanh toán thành công!');
    }

    /**
     * Display the specified payment history.
     */
    public function show(LandRentalContract $landRentalContract, LandRentalPaymentHistory $landRentalPaymentHistory)
    {
        return view('admin.land-rental.histories.show', compact('landRentalContract', 'landRentalPaymentHistory'));
    }

    /**
     * Show the form for editing the specified payment history.
     */
    public function edit(LandRentalContract $landRentalContract, LandRentalPaymentHistory $landRentalPaymentHistory)
    {
        // Kiểm tra xem có phải là thanh toán mới nhất không
        $latestPaymentId = LandRentalPaymentHistory::where('land_rental_contract_id', $landRentalContract->id)
            ->max('id');
            
        if ($landRentalPaymentHistory->id != $latestPaymentId) {
            return redirect()->route('admin.land-rental.payment-histories.index', $landRentalContract)
                ->with('error', 'Chỉ được phép sửa thanh toán mới nhất!');
        }
        
        return view('admin.land-rental.histories.edit', compact('landRentalContract', 'landRentalPaymentHistory'));
    }

    /**
     * Update the specified payment history in storage.
     */
    public function update(Request $request, LandRentalContract $landRentalContract, LandRentalPaymentHistory $landRentalPaymentHistory)
    {
        // Kiểm tra xem có phải là thanh toán mới nhất không
        $latestPaymentId = LandRentalPaymentHistory::where('land_rental_contract_id', $landRentalContract->id)
            ->max('id');
            
        if ($landRentalPaymentHistory->id != $latestPaymentId) {
            return redirect()->route('admin.land-rental.payment-histories.index', $landRentalContract)
                ->with('error', 'Chỉ được phép sửa thanh toán mới nhất!');
        }
        
        $request->validate([
            'period' => 'required|integer|in:1,2',
            'payment_type' => 'required|integer|in:1,2,3',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $landRentalPaymentHistory->update([
            'period' => $request->period,
            'payment_type' => $request->payment_type,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.land-rental.payment-histories.index', $landRentalContract)
            ->with('success', 'Cập nhật lịch sử thanh toán thành công!');
    }

    /**
     * Remove the specified payment history from storage.
     */
    public function destroy(LandRentalContract $landRentalContract, LandRentalPaymentHistory $landRentalPaymentHistory)
    {
        // Kiểm tra xem có phải là thanh toán mới nhất không
        $latestPaymentId = LandRentalPaymentHistory::where('land_rental_contract_id', $landRentalContract->id)
            ->max('id');
            
        if ($landRentalPaymentHistory->id != $latestPaymentId) {
            return redirect()->route('admin.land-rental.payment-histories.index', $landRentalContract)
                ->with('error', 'Chỉ được phép xóa thanh toán mới nhất!');
        }
        
        $landRentalPaymentHistory->delete();

        return redirect()->route('admin.land-rental.payment-histories.index', $landRentalContract)
            ->with('success', 'Xóa lịch sử thanh toán thành công!');
    }
}
