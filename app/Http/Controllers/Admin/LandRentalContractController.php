<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandRentalContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class LandRentalContractController extends Controller
{
    /**
     * Calculate period months for a contract
     */
    private function calculatePeriodMonths($item, $currentYear)
    {
        $period1Months = 0; // January - June
        $period2Months = 0; // July - December
        $currentMonths = 12; // Default for existing contracts

        if ($item->rental_period && isset($item->rental_period['start_date'])) {
            $startDate = \Carbon\Carbon::parse($item->rental_period['start_date']);
            $contractYear = $startDate->year;

            if ($contractYear == $currentYear) {
                $dayOfMonth = $startDate->day;

                // Apply rounding rules for the start month
                $effectiveStartMonth = $startDate->month;
                if ($dayOfMonth > 15) {
                    $effectiveStartMonth++;
                }

                // Calculate Period 1 (January - June)
                if ($effectiveStartMonth <= 6) {
                    $period1Months = 6 - $effectiveStartMonth + 1;
                    if ($dayOfMonth <= 15 && $dayOfMonth > 1 && $effectiveStartMonth == $startDate->month) {
                        $period1Months -= 0.5;
                    }
                }

                // Calculate Period 2 (July - December) 
                if ($effectiveStartMonth <= 12) {
                    if ($effectiveStartMonth <= 6) {
                        $period2Months = 6; // Full second half if started in first half
                    } else {
                        $period2Months = 12 - $effectiveStartMonth + 1;
                        if ($dayOfMonth <= 15 && $dayOfMonth > 1 && $effectiveStartMonth == $startDate->month) {
                            $period2Months -= 0.5;
                        }
                    }
                }

                // Calculate total months for rental fee calculation
                $endOfYear = \Carbon\Carbon::createFromDate($currentYear, 12, 31);
                if ($dayOfMonth <= 15) {
                    $adjustedStart = \Carbon\Carbon::createFromDate($currentYear, $startDate->month, 1);
                } else {
                    $adjustedStart = $startDate->copy()->addMonth()->startOfMonth();
                }

                if ($adjustedStart->year == $currentYear && $adjustedStart <= $endOfYear) {
                    $currentMonths = $adjustedStart->diffInMonths($endOfYear) + 1;
                    if ($dayOfMonth <= 15 && $dayOfMonth > 1) {
                        $currentMonths -= 0.5;
                    }
                } else {
                    $currentMonths = 0;
                }
            } else if ($contractYear < $currentYear) {
                // Existing contract from previous year - full periods
                $period1Months = 6;
                $period2Months = 6;
                $currentMonths = 12;
            }
        } else {
            // Default for contracts without start date
            $period1Months = 6;
            $period2Months = 6;
            $currentMonths = 12;
        }

        return [
            'current_months' => $currentMonths,
            'period1_months' => $period1Months,
            'period2_months' => $period2Months
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $contracts = LandRentalContract::with('landRentalPrices')->select([
                'id',
                'contract_number',
                'rental_zone',
                'rental_location',
                'rental_decision',
                'contract_file_path',
                'rental_decision_file_path',
                'export_tax',
                'land_tax_price',
                'area',
                'rental_period',
                'created_at'
            ]);

            return DataTables::of($contracts)
                ->addIndexColumn()
                ->editColumn('contract_number', function ($item) {
                    if ($item->contract_file_path) {
                        $url = asset('storage/' . str_replace('public/', '', $item->contract_file_path));
                        return '<strong>' . $item->contract_number . '</strong> <br/><a href="' . $url . '" target="_blank" class="btn btn-sm btn-info">Xem file</a>';
                    } else {
                        return '<strong>' . $item->contract_number . '</strong>';
                    }
                })
                ->editColumn('rental_decision', function ($item) {
                    if ($item->contract_file_path) {
                        $url = asset('storage/' . str_replace('public/', '', $item->rental_decision_file_path));
                        return '<strong>' . $item->rental_decision . '</strong> <br/><a href="' . $url . '" target="_blank" class="btn btn-sm btn-info">Xem file</a>';
                    } else {
                        return '<strong>' . $item->rental_decision . '</strong>';
                    }
                })
                ->editColumn('area', function ($item) {
                    $areaInfo = '';

                    // Display basic area information
                    if ($item->area && isset($item->area['value'])) {
                        $areaInfo = number_format($item->area['value'], 2) . ' ' . ($item->area['unit'] ?? 'm2');
                    } else {
                        $areaInfo = 'Chưa có thông tin diện tích';
                    }

                    // Calculate periods using helper function
                    $currentYear = date('Y');
                    $periods = $this->calculatePeriodMonths($item, $currentYear);
                    $currentMonths = $periods['current_months'];
                    $period1Months = $periods['period1_months'];
                    $period2Months = $periods['period2_months'];

                    // Calculate rental fee information
                    $yearlyRentalFee = 0;
                    $latestPrice = $item->landRentalPrices()
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($latestPrice && $latestPrice->rental_price) {
                        $yearlyRentalFee = $latestPrice->rental_price * $item->area['value'];
                    }

                    $result = '<strong>' . $areaInfo . '</strong><br/>';
                    $result .= '<small class="text-info">Số tháng năm ' . $currentYear . ': ' . $currentMonths . ' tháng</small><br/>';
                    $result .= '<small class="text-primary">Kỳ 1: ' . $period1Months . ' tháng</small><br/>';
                    $result .= '<small class="text-secondary">Kỳ 2: ' . $period2Months . ' tháng</small><br/>';

                    if ($yearlyRentalFee > 0) {
                        $result .= '<small class="text-success">Tiền thuê/năm: ' . number_format($yearlyRentalFee, 0, ',', '.') . ' VND</small><br/>';
                        $result .= '<small class="text-warning">Tiền thuê/kỳ I: ' . number_format(($yearlyRentalFee / 12) * $period1Months, 0, ',', '.') . ' VND</small><br/>';
                        $result .= '<small class="text-warning">Tiền thuê/kỳ II: ' . number_format(($yearlyRentalFee / 12) * $period2Months, 0, ',', '.') . ' VND</small>';
                    } else {
                        $result .= '<small class="text-muted">Chưa có giá thuê</small>';
                    }

                    return $result;
                })
                ->editColumn('rental_period', function ($item) {
                    if ($item->rental_period) {
                        $period = $item->rental_period;
                        $display = [];

                        if (isset($period['start_date']) && isset($period['end_date'])) {
                            $display[] = 'Từ ' . date('d/m/Y', strtotime($period['start_date'])) . ' đến ' . date('d/m/Y', strtotime($period['end_date']));
                        }

                        if (isset($period['years'])) {
                            $display[] = $period['years'] . ' năm';
                        }

                        return implode('<br/>', $display);
                    }
                    return 'Chưa có thông tin';
                })
                ->editColumn('rental_zone', function ($item) {
                    return $item->rental_zone ?: 'Chưa có thông tin';
                })
                ->editColumn('rental_location', function ($item) {
                    return $item->rental_location ?: 'Chưa có thông tin';
                })
                ->editColumn('land_tax_price', function ($item) {
                    $result = 'Thuế: ' . number_format($item->export_tax * 100, 2) . '%<br/>';
                    $result .= 'Đơn giá thuế: ' . ($item->land_tax_price ? number_format($item->land_tax_price, 0, ',', '.') . ' VND/m²' : 'Chưa có') . '<br/>';

                    // Calculate land tax amounts if all required data is available
                    if ($item->area && isset($item->area['value']) && $item->land_tax_price && $item->export_tax) {
                        $area = $item->area['value'];
                        
                        // Get periods using helper function
                        $currentYear = date('Y');
                        $periods = $this->calculatePeriodMonths($item, $currentYear);
                        $period1Months = $periods['period1_months'];
                        $period2Months = $periods['period2_months'];

                        // Calculate land tax for each period: (area × land_tax_price × export_tax × months) / 12
                        $period1Tax = ($area * $item->land_tax_price * $item->export_tax * $period1Months) / 12;
                        $period2Tax = ($area * $item->land_tax_price * $item->export_tax * $period2Months) / 12;

                        $result .= '<small class="text-success">Kỳ 1: ' . number_format($period1Tax, 0, ',', '.') . ' VND</small><br/>';
                        $result .= '<small class="text-warning">Kỳ 2: ' . number_format($period2Tax, 0, ',', '.') . ' VND</small>';
                    } else {
                        $result .= '<small class="text-muted">Chưa đủ dữ liệu tính thuế</small>';
                    }

                    return $result;
                })
                ->editColumn('contract_file_path', function ($item) {})
                ->addColumn('action', function ($item) {
                    $showBtn = '<a href="' . route('admin.land-rental-contracts.show', $item) . '" class="btn btn-info btn-sm" title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                    </a>';
                    $editBtn = '<a href="' . route('admin.land-rental-contracts.edit', $item) . '" class="btn btn-warning btn-sm" title="Sửa">
                        <i class="fas fa-edit"></i>
                    </a>';
                    $priceBtn = '<a href="' . route('admin.land-rental-prices.index', ['landRentalContract' => $item->id]) . '" class="btn btn-primary btn-sm" title="Quản lý giá thuê đất">
                        <i class="fas fa-dollar-sign"></i>
                    </a>';
                    $paymentBtn = '<a href="' . route('admin.land-rental-payment-histories.index', $item) . '" class="btn btn-success btn-sm" title="Lịch sử thanh toán">
                        <i class="fas fa-money-bill-wave"></i>
                    </a>';
                    $deleteBtn = '<form action="' . route('admin.land-rental-contracts.destroy', $item) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Bạn có chắc chắn muốn xóa hợp đồng này?\')">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>';
                    return '<div class="btn-group" role="group">' . $showBtn . ' ' . $priceBtn . ' ' . $paymentBtn . ' ' . $editBtn . ' ' . $deleteBtn . '</div>';
                })
                ->rawColumns(['contract_number', 'rental_decision', 'area', 'land_tax_price', 'rental_period', 'contract_file_path', 'action'])
                ->make(true);
        }

        return view('admin.land-rental-contracts.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.land-rental-contracts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contract_number' => 'required|string|max:255|unique:land_rental_contracts,contract_number',
            'rental_decision' => 'nullable|string|max:255',
            'rental_zone' => 'nullable|string|max:255',
            'rental_location' => 'nullable|string|max:255',
            'area_value' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|string|in:m2,ha,km2',
            'rental_start_date' => 'nullable|date',
            'rental_end_date' => 'nullable|date|after:rental_start_date',
            'rental_years' => 'nullable|numeric|min:0',
            'export_tax' => 'nullable|numeric|min:0|max:1',
            'land_tax_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'rental_decision_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $contractFilePath = null;
        if ($request->hasFile('contract_file')) {
            $file = $request->file('contract_file');
            $filename = 'contract_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $contractFilePath = $file->storeAs('public/land_contracts', $filename);
        }

        $decisionFilePath = null;
        $decisionFileName = null;
        if ($request->hasFile('rental_decision_file')) {
            $file = $request->file('rental_decision_file');
            $filename = 'decision_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $decisionFilePath = $file->storeAs('public/land_contracts', $filename);
            $decisionFileName = $file->getClientOriginalName();
        }

        // Chuẩn bị dữ liệu area và rental_period
        $area = null;
        if ($request->area_value) {
            $area = [
                'value' => $request->area_value,
                'unit' => $request->area_unit ?? 'm2'
            ];
        }

        $rental_period = null;
        if ($request->rental_start_date) {
            $rental_period = [
                'start_date' => $request->rental_start_date,
                'end_date' => $request->rental_end_date,
                'years' => $request->rental_years
            ];
        }

        LandRentalContract::create([
            'contract_number' => $request->contract_number,
            'contract_file_path' => $contractFilePath,
            'rental_decision' => $request->rental_decision,
            'rental_decision_file_name' => $decisionFileName,
            'rental_decision_file_path' => $decisionFilePath,
            'rental_zone' => $request->rental_zone,
            'rental_location' => $request->rental_location,
            'export_tax' => $request->export_tax ?? 0.03,
            'land_tax_price' => $request->land_tax_price,
            'area' => $area,
            'rental_period' => $rental_period,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.land-rental-contracts.index')->with('success', 'Thêm hợp đồng thuê đất thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(LandRentalContract $landRentalContract)
    {
        return view('admin.land-rental-contracts.show', compact('landRentalContract'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LandRentalContract $landRentalContract)
    {
        return view('admin.land-rental-contracts.edit', compact('landRentalContract'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LandRentalContract $landRentalContract)
    {
        $request->validate([
            'contract_number' => 'required|string|max:255|unique:land_rental_contracts,contract_number,' . $landRentalContract->id,
            'rental_decision' => 'nullable|string|max:255',
            'rental_zone' => 'nullable|string|max:255',
            'rental_location' => 'nullable|string|max:255',
            'area_value' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|string|in:m2,ha,km2',
            'rental_start_date' => 'nullable|date',
            'rental_end_date' => 'nullable|date|after:rental_start_date',
            'rental_years' => 'nullable|numeric|min:0',
            'export_tax' => 'nullable|numeric|min:0|max:1',
            'land_tax_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'rental_decision_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $contractFilePath = $landRentalContract->contract_file_path;
        if ($request->hasFile('contract_file')) {
            // Xóa file cũ nếu có
            if ($contractFilePath && Storage::exists($contractFilePath)) {
                Storage::delete($contractFilePath);
            }
            $file = $request->file('contract_file');
            $filename = 'contract_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $contractFilePath = $file->storeAs('public/land_contracts', $filename);
        }

        $decisionFilePath = $landRentalContract->rental_decision_file_path;
        $decisionFileName = $landRentalContract->rental_decision_file_name;
        if ($request->hasFile('rental_decision_file')) {
            // Xóa file cũ nếu có
            if ($decisionFilePath && Storage::exists($decisionFilePath)) {
                Storage::delete($decisionFilePath);
            }
            $file = $request->file('rental_decision_file');
            $filename = 'decision_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $decisionFilePath = $file->storeAs('public/land_contracts', $filename);
            $decisionFileName = $file->getClientOriginalName();
        }

        // Chuẩn bị dữ liệu area và rental_period
        $area = null;
        if ($request->area_value) {
            $area = [
                'value' => $request->area_value,
                'unit' => $request->area_unit ?? 'm2'
            ];
        }

        $rental_period = null;
        if ($request->rental_start_date) {
            $rental_period = [
                'start_date' => $request->rental_start_date,
                'end_date' => $request->rental_end_date,
                'years' => $request->rental_years
            ];
        }

        $landRentalContract->update([
            'contract_number' => $request->contract_number,
            'contract_file_path' => $contractFilePath,
            'rental_decision' => $request->rental_decision,
            'rental_decision_file_name' => $decisionFileName,
            'rental_decision_file_path' => $decisionFilePath,
            'rental_zone' => $request->rental_zone,
            'rental_location' => $request->rental_location,
            'export_tax' => $request->export_tax ?? $landRentalContract->export_tax,
            'land_tax_price' => $request->land_tax_price,
            'area' => $area,
            'rental_period' => $rental_period,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.land-rental-contracts.index')->with('success', 'Cập nhật hợp đồng thuê đất thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LandRentalContract $landRentalContract)
    {
        // Xóa các file nếu có
        if ($landRentalContract->contract_file_path && Storage::exists($landRentalContract->contract_file_path)) {
            Storage::delete($landRentalContract->contract_file_path);
        }
        if ($landRentalContract->rental_decision_file_path && Storage::exists($landRentalContract->rental_decision_file_path)) {
            Storage::delete($landRentalContract->rental_decision_file_path);
        }

        $landRentalContract->delete();

        return redirect()->route('admin.land-rental-contracts.index')->with('success', 'Xóa hợp đồng thuê đất thành công!');
    }
}
