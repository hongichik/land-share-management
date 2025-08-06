<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandRentalContract;
use App\Models\LandRentalPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class LandRentalPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, LandRentalContract $landRentalContract)
    {
        if ($request->ajax()) {
            $prices = $landRentalContract->landRentalPrices()->select([
                'id',
                'price_decision', 
                'price_decision_file_path',
                'price_period',
                'rental_price',
                'created_at'
            ])->orderBy('id', 'desc');

            return DataTables::of($prices)
                ->addIndexColumn()
                ->editColumn('price_decision', function ($item) {
                    if ($item->price_decision_file_path) {
                        $url = asset('storage/' . str_replace('public/', '', $item->price_decision_file_path));
                        return '<strong>' . ($item->price_decision ?: 'Chưa có thông tin') . '</strong> <br/><a href="' . $url . '" target="_blank" class="btn btn-sm btn-info">Xem file</a>';
                    } else {
                        return '<strong>' . ($item->price_decision ?: 'Chưa có thông tin') . '</strong>';
                    }
                })
                ->editColumn('price_period', function ($item) {
                    if ($item->price_period) {
                        $period = $item->price_period;
                        $display = [];
                        
                        if (isset($period['start']) && isset($period['end'])) {
                            $display[] = 'Từ ' . date('d/m/Y', strtotime($period['start'])) . ' đến ' . date('d/m/Y', strtotime($period['end']));
                        }
                        
                        if (isset($period['years'])) {
                            $display[] = $period['years'] . ' năm';
                        }
                        
                        return implode('<br/>', $display);
                    }
                    return 'Chưa có thông tin';
                })
                ->editColumn('rental_price', function ($item) {
                    return number_format($item->rental_price, 0, ',', '.') . ' VND';
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at->format('d/m/Y H:i');
                })
                ->addColumn('action', function ($item) use ($landRentalContract) {
                    // Kiểm tra có phải là bản ghi mới nhất không
                    $latestPrice = $landRentalContract->landRentalPrices()
                        ->orderBy('id', 'desc')
                        ->first();
                    $isLatest = $latestPrice && $item->id === $latestPrice->id;
                    
                    $actions = '';
                    
                    if ($isLatest) {
                        $actions .= '<a href="' . route('admin.land-rental-prices.edit', [$landRentalContract, $item]) . '" class="btn btn-warning btn-sm" title="Sửa">
                            <i class="fas fa-edit"></i>
                        </a> ';
                        $actions .= '<form action="' . route('admin.land-rental-prices.destroy', [$landRentalContract, $item]) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Bạn có chắc chắn muốn xóa giá thuê này?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>';
                    } else {
                        $actions .= '<span class="btn btn-secondary btn-sm disabled" title="Chỉ được phép sửa/xóa giá thuê mới nhất">
                            <i class="fas fa-lock"></i> Đã khóa
                        </span>';
                    }
                    
                    return '<div class="btn-group" role="group">' . $actions . '</div>';
                })
                ->rawColumns(['price_decision', 'price_period', 'action'])
                ->make(true);
        }

        return view('admin.land-rental-prices.index', compact('landRentalContract'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(LandRentalContract $landRentalContract)
    {
        // Lấy giá thuê cuối cùng để xác định thời gian bắt đầu
        $lastPrice = $landRentalContract->landRentalPrices()->latest('created_at')->first();
        
        $defaultStartDate = null;
        if ($lastPrice && isset($lastPrice->price_period['end'])) {
            // Nếu có giá thuê trước đó, thời gian bắt đầu là ngày sau ngày kết thúc của giá thuê cuối
            $defaultStartDate = date('Y-m-d', strtotime($lastPrice->price_period['end'] . ' +1 day'));
        } elseif ($landRentalContract->rental_period && isset($landRentalContract->rental_period['start_date'])) {
            // Nếu chưa có giá thuê nào, lấy thời gian bắt đầu của hợp đồng
            $defaultStartDate = $landRentalContract->rental_period['start_date'];
        }
        
        return view('admin.land-rental-prices.create', compact('landRentalContract', 'defaultStartDate'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, LandRentalContract $landRentalContract)
    {
        // Lấy giá thuê cuối cùng để xác định thời gian bắt đầu
        $lastPrice = $landRentalContract->landRentalPrices()->latest('created_at')->first();
        
        $startDate = null;
        if ($lastPrice && isset($lastPrice->price_period['end'])) {
            // Nếu có giá thuê trước đó, thời gian bắt đầu là ngày sau ngày kết thúc của giá thuê cuối
            $startDate = date('Y-m-d', strtotime($lastPrice->price_period['end'] . ' +1 day'));
        } elseif ($landRentalContract->rental_period && isset($landRentalContract->rental_period['start_date'])) {
            // Nếu chưa có giá thuê nào, lấy thời gian bắt đầu của hợp đồng
            $startDate = $landRentalContract->rental_period['start_date'];
        }

        $request->validate([
            'price_decision' => 'nullable|string|max:255',
            'price_decision_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'price_period' => 'required|array',
            'price_period.end' => 'required|date|after:' . $startDate,
            'price_period.years' => 'nullable|numeric|min:0',
            'rental_price' => 'required|numeric|min:0',
        ]);

        $decisionFilePath = null;
        if ($request->hasFile('price_decision_file')) {
            $file = $request->file('price_decision_file');
            $filename = 'price_decision_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $decisionFilePath = $file->storeAs('public/land_rental_prices', $filename);
        }

        // Tự động gán thời gian bắt đầu
        $pricePeriod = [
            'start' => $startDate,
            'end' => $request->price_period['end'],
            'years' => $request->price_period['years']
        ];

        $landRentalContract->landRentalPrices()->create([
            'price_decision' => $request->price_decision,
            'price_decision_file_path' => $decisionFilePath,
            'price_period' => $pricePeriod,
            'rental_price' => $request->rental_price,
        ]);

        return redirect()->route('admin.land-rental-prices.index', $landRentalContract)->with('success', 'Thêm giá thuê đất thành công!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LandRentalContract $landRentalContract, LandRentalPrice $landRentalPrice)
    {
        // Chỉ cho phép sửa bản ghi mới nhất (có id cao nhất)
        $latestPrice = $landRentalContract->landRentalPrices()
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$latestPrice || $landRentalPrice->id !== $latestPrice->id) {
            return redirect()->route('admin.land-rental-prices.index', $landRentalContract)
                ->with('error', 'Chỉ được phép sửa giá thuê mới nhất để tránh thay đổi lịch sử!');
        }
        
        return view('admin.land-rental-prices.edit', compact('landRentalContract', 'landRentalPrice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LandRentalContract $landRentalContract, LandRentalPrice $landRentalPrice)
    {
        $request->validate([
            'price_decision' => 'nullable|string|max:255',
            'price_decision_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'price_period' => 'required|array',
            'price_period.start' => 'required|date',
            'price_period.end' => 'required|date|after:price_period.start',
            'rental_price' => 'required|numeric|min:0',
        ]);

        $decisionFilePath = $landRentalPrice->price_decision_file_path;
        if ($request->hasFile('price_decision_file')) {
            if ($decisionFilePath && Storage::exists($decisionFilePath)) {
                Storage::delete($decisionFilePath);
            }
            $file = $request->file('price_decision_file');
            $filename = 'price_decision_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $decisionFilePath = $file->storeAs('public/land_rental_prices', $filename);
        }

        $landRentalPrice->update([
            'price_decision' => $request->price_decision,
            'price_decision_file_path' => $decisionFilePath,
            'price_period' => $request->price_period,
            'rental_price' => $request->rental_price,
        ]);

        return redirect()->route('admin.land-rental-prices.index', $landRentalContract)->with('success', 'Cập nhật giá thuê đất thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LandRentalContract $landRentalContract, LandRentalPrice $landRentalPrice)
    {
        // Chỉ cho phép xóa bản ghi mới nhất (có id cao nhất)
        $latestPrice = $landRentalContract->landRentalPrices()
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$latestPrice || $landRentalPrice->id !== $latestPrice->id) {
            return redirect()->route('admin.land-rental-prices.index', $landRentalContract)
                ->with('error', 'Chỉ được phép xóa giá thuê mới nhất để tránh thay đổi lịch sử!');
        }

        if ($landRentalPrice->price_decision_file_path && Storage::exists($landRentalPrice->price_decision_file_path)) {
            Storage::delete($landRentalPrice->price_decision_file_path);
        }

        $landRentalPrice->delete();

        return redirect()->route('admin.land-rental-prices.index', $landRentalContract)->with('success', 'Xóa giá thuê đất thành công!');
    }
}
