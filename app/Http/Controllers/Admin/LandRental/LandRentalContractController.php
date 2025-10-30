<?php

namespace App\Http\Controllers\Admin\LandRental;

use App\Http\Controllers\Controller;
use App\Models\LandRentalContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Exports\LandRentalContractsExport;
use App\Exports\LandTaxCalculationExport;
use App\Exports\LandRentalPlanExport;
use App\Exports\LandTaxPlanExport;
use App\Exports\LandNonAgriTaxCalculationExport; // Thêm dòng này
use Maatwebsite\Excel\Facades\Excel;

class LandRentalContractController extends Controller
{
    /**
     * Calculate period months for a contract's prices in a given year
     */
    private function calculatePeriodMonths($contract, $currentYear)
    {
        $period1Months = 0; // January - June
        $period2Months = 0; // July - December

        $period1Start = Carbon::create($currentYear, 1, 1);
        $period1End = Carbon::create($currentYear, 6, 30);
        $period2Start = Carbon::create($currentYear, 7, 1);
        $period2End = Carbon::create($currentYear, 12, 31);

        // Get prices for the current year
        $prices = $contract->landRentalPrices
            ->filter(function ($price) use ($currentYear) {
                $start = Carbon::parse($price->price_period['start']);
                $end = Carbon::parse($price->price_period['end']);
                $yearStart = Carbon::create($currentYear, 1, 1);
                $yearEnd = Carbon::create($currentYear, 12, 31);
                
                return $start->lessThanOrEqualTo($yearEnd) && $end->greaterThanOrEqualTo($yearStart);
            })
            ->sortBy('price_period.start');

        foreach ($prices as $price) {
            $priceStart = Carbon::parse($price->price_period['start']);
            $priceEnd = Carbon::parse($price->price_period['end']);

            // Kỳ 1 (January - June)
            $segmentStart1 = $priceStart->copy()->max($period1Start);
            $segmentEnd1 = $priceEnd->copy()->min($period1End);
            if ($segmentStart1 <= $segmentEnd1) {
                $months = $this->calculateSimpleMonths($segmentStart1, $segmentEnd1);
                $period1Months += $months;
            }

            // Kỳ 2 (July - December)
            $segmentStart2 = $priceStart->copy()->max($period2Start);
            $segmentEnd2 = $priceEnd->copy()->min($period2End);
            if ($segmentStart2 <= $segmentEnd2) {
                $months = $this->calculateSimpleMonths($segmentStart2, $segmentEnd2);
                $period2Months += $months;
            }
        }

        $currentMonths = $period1Months + $period2Months;

        return [
            'current_months' => $currentMonths,
            'period1_months' => $period1Months,
            'period2_months' => $period2Months,
            'prices' => $prices
        ];
    }

    /**
     * Calculate months using simplified logic
     */
    private function calculateSimpleMonths($segmentStart, $segmentEnd)
    {
        $startMonth = $segmentStart->month;
        $startYear = $segmentStart->year;
        $endMonth = $segmentEnd->month;
        $endYear = $segmentEnd->year;
        
        // If start day >= 15, don't count start month
        if ($segmentStart->day >= 15) {
            if ($startMonth == 12) {
                $startMonth = 1;
                $startYear++;
            } else {
                $startMonth++;
            }
        }
        
        // If end day < 15, don't count end month
        if ($segmentEnd->day < 15) {
            if ($endMonth == 1) {
                $endMonth = 12;
                $endYear--;
            } else {
                $endMonth--;
            }
        }
        
        // Calculate months
        $months = 0;
        if ($startYear < $endYear || ($startYear == $endYear && $startMonth <= $endMonth)) {
            $months = ($endYear - $startYear) * 12 + ($endMonth - $startMonth) + 1;
        }
        
        return $months;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $contracts = LandRentalContract::with(['landRentalPrices', 'paymentHistories'])->select([
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
                ->editColumn('contract_and_decision', function ($item) {
                    $result = '<div class="contract-decision-info">';
                    $result .= '<strong>📄 ' . $item->contract_number . '</strong><br/>';

                    if ($item->contract_file_path) {
                        $contractUrl = asset('storage/' . str_replace('public/', '', $item->contract_file_path));
                        $result .= '<a href="' . $contractUrl . '" target="_blank" class="btn btn-outline-primary btn-sm mb-1" title="Xem file hợp đồng">
                            <i class="fas fa-file-pdf"></i> File HĐ
                        </a><br/>';
                    }

                    $result .= '<small><strong>🏛️ ' . ($item->rental_decision ?: 'Chưa có QĐ') . '</strong></small>';

                    if ($item->rental_decision_file_path) {
                        $decisionUrl = asset('storage/' . str_replace('public/', '', $item->rental_decision_file_path));
                        $result .= '<br/><a href="' . $decisionUrl . '" target="_blank" class="btn btn-outline-info btn-sm" title="Xem file quyết định">
                            <i class="fas fa-file-alt"></i> File QĐ
                        </a>';
                    }

                    $result .= '</div>';
                    return $result;
                })
                ->editColumn('rental_zone', function ($item) {
                    $result = '<div class="location-info">';
                    if ($item->rental_zone) {
                        $result .= '<strong>🗺️ Khu vực:</strong> ' . $item->rental_zone . '<br/>';
                    } else {
                        $result .= '<strong>🗺️ Khu vực:</strong> <em class="text-muted">Chưa có thông tin</em><br/>';
                    }

                    if ($item->rental_location) {
                        $result .= '<strong>📍 Vị trí:</strong> ' . $item->rental_location;
                    } else {
                        $result .= '<strong>📍 Vị trí:</strong> <em class="text-muted">Chưa có thông tin</em>';
                    }

                    $result .= '</div>';
                    return $result;
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
                    $prices = $periods['prices'];
                    $currentMonths = $periods['current_months'];
                    $period1Months = $periods['period1_months'];
                    $period2Months = $periods['period2_months'];

                    $areaValue = $item->area['value'] ?? 0;
                    $result = '<strong>' . $areaInfo . '</strong><br/>';
                    $result .= '<small class="text-info">Số tháng năm ' . $currentYear . ': ' . $currentMonths . ' tháng</small><br/>';
                    $result .= '<small class="text-primary">Kỳ 1: ' . $period1Months . ' tháng</small><br/>';
                    $result .= '<small class="text-secondary">Kỳ 2: ' . $period2Months . ' tháng</small><br/>';

                    // Define periods
                    $periodsArr = [
                        1 => [
                            'start' => Carbon::create($currentYear, 1, 1),
                            'end' => Carbon::create($currentYear, 6, 30),
                            'label' => 'Kỳ I',
                            'months' => $period1Months,
                            'color' => 'text-warning'
                        ],
                        2 => [
                            'start' => Carbon::create($currentYear, 7, 1),
                            'end' => Carbon::create($currentYear, 12, 31),
                            'label' => 'Kỳ II',
                            'months' => $period2Months,
                            'color' => 'text-warning'
                        ]
                    ];

                    foreach ($periodsArr as $periodKey => $periodInfo) {
                        $periodTotal = 0;
                        $periodDetail = '';
                        foreach ($prices as $price) {
                            $priceStart = \Carbon\Carbon::parse($price->price_period['start']);
                            $priceEnd = \Carbon\Carbon::parse($price->price_period['end']);
                            // Calculate intersection between price period and current period
                            $segmentStart = max($priceStart, $periodInfo['start']);
                            $segmentEnd = min($priceEnd, $periodInfo['end']);
                            
                            if ($segmentStart <= $segmentEnd) {
                                // Simplified month calculation
                                $months = $this->calculateSimpleMonths($segmentStart, $segmentEnd);
                                
                                if ($months > 0 && $price->rental_price && $areaValue) {
                                    $fee = ($price->rental_price * $areaValue / 12) * $months;
                                    $periodTotal += $fee;
                                    // Only show detail if months > 0 for this period
                                    $periodDetail .= '<div style="font-size:0.9em;">- ' . $months . ' tháng (' . $segmentStart->format('d/m/Y') . ' - ' . $segmentEnd->format('d/m/Y') . '): <strong>' . number_format($fee, 0, ',', '.') . ' VND</strong></div>';
                                }
                            }
                        }
                        // Always show period summary, even if periodTotal = 0
                        $result .= '<small class="' . $periodInfo['color'] . '">' . $periodInfo['label'] . ': <strong>' . number_format($periodTotal, 0, ',', '.') . ' VND</strong></small>';
                        if ($periodDetail) $result .= '<br/>' . $periodDetail;
                        $result .= '<br/>';
                    }

                    // Tổng tiền thuê/năm
                    $yearTotal = 0;
                    $yearStart = \Carbon\Carbon::create($currentYear, 1, 1);
                    $yearEnd = \Carbon\Carbon::create($currentYear, 12, 31);
                    
                    // Calculate year total by going month by month to avoid double counting
                    $current = $yearStart->copy()->startOfMonth();
                    while ($current <= $yearEnd) {
                        $monthStart = $current->copy();
                        $monthEnd = $current->copy()->endOfMonth();
                        
                        // Find the applicable price for this month
                        $monthPrice = null;
                        foreach ($prices as $price) {
                            $priceStart = \Carbon\Carbon::parse($price->price_period['start']);
                            $priceEnd = \Carbon\Carbon::parse($price->price_period['end']);
                            
                            // Check if this price period covers this month
                            if ($priceStart <= $monthEnd && $priceEnd >= $monthStart) {
                                // Calculate intersection
                                $segmentStart = max($priceStart, $monthStart);
                                $segmentEnd = min($priceEnd, $monthEnd);
                                
                                // Check if >= 15 days in this month
                                $months = $this->calculateSimpleMonths($segmentStart, $segmentEnd);
                                if ($months > 0) {
                                    $monthPrice = $price->rental_price;
                                    break; // Use first applicable price for this month
                                }
                            }
                        }
                        
                        if ($monthPrice && $areaValue) {
                            $monthlyFee = ($monthPrice * $areaValue) / 12;
                            $yearTotal += $monthlyFee;
                        }
                        
                        $current->addMonth();
                    }
                    if ($yearTotal > 0) {
                        $result .= '<small class="text-success">Tiền thuê/năm: <strong>' . number_format($yearTotal, 0, ',', '.') . ' VND</strong></small>';
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
                ->editColumn('payment', function ($item) {
                    $currentYear = date('Y');
                    $result = '<div class="payment-info">';

                    // Lấy tất cả thanh toán của hợp đồng này trong năm hiện tại
                    $payments = $item->paymentHistories()
                        ->whereYear('payment_date', $currentYear)
                        ->get();
                    
                    // Tính toán số tiền cần thanh toán cho mỗi kỳ dựa trên từng mức giá
                    $periods = $this->calculatePeriodMonths($item, $currentYear);
                    $prices = $periods['prices'];
                    $period1Months = $periods['period1_months'];
                    $period2Months = $periods['period2_months'];

                    $areaValue = $item->area['value'] ?? 0;


                    $periodsArr = [
                        1 => [
                            'start' => \Carbon\Carbon::create($currentYear, 1, 1),
                            'end' => \Carbon\Carbon::create($currentYear, 6, 30),
                            'months' => $period1Months,
                            'label' => 'Kỳ 1',
                        ],
                        2 => [
                            'start' => \Carbon\Carbon::create($currentYear, 7, 1),
                            'end' => \Carbon\Carbon::create($currentYear, 12, 31),
                            'months' => $period2Months,
                            'label' => 'Kỳ 2',
                        ]
                    ];

                    // Ngày hiện tại để kiểm tra deadline
                    $currentDate = now();
                    $currentMonth = $currentDate->month;
                    $currentDay = $currentDate->day;

                    foreach ($periodsArr as $periodKey => $periodInfo) {
                        $requiredAmount = 0;
                        // Tính tổng tiền phải nộp cho kỳ này dựa trên từng mức giá
                        foreach ($prices as $price) {
                            $priceStart = \Carbon\Carbon::parse($price->price_period['start']);
                            $priceEnd = \Carbon\Carbon::parse($price->price_period['end']);
                            $segmentStart = max($priceStart, $periodInfo['start']);
                            $segmentEnd = min($priceEnd, $periodInfo['end']);
                            $months = 0;
                            if ($segmentStart <= $segmentEnd) {
                                $months = $this->calculateSimpleMonths($segmentStart, $segmentEnd);
                            }
                            if ($months > 0 && $price->rental_price && $areaValue) {
                                $requiredAmount += ($price->rental_price * $areaValue / 12) * $months;
                            }
                        }

                        $paidAmount = $payments->where('period', $periodKey)->sum('amount');
                        $remainingAmount = $requiredAmount - $paidAmount;

                        $result .= '<strong>' . $periodInfo['label'] . ' (' . $periodInfo['months'] . ' tháng):</strong><br/>';
                        if ($requiredAmount > 0) {
                            $result .= '<small class="text-info">Cần: ' . number_format($requiredAmount, 0, ',', '.') . ' VND</small><br/>';
                            $result .= '<small class="text-success">Đã trả: ' . number_format($paidAmount, 0, ',', '.') . ' VND</small><br/>';
                            if ($remainingAmount > 0) {
                                $result .= '<span class="payment-status partial">Còn lại: ' . number_format($remainingAmount, 0, ',', '.') . ' VND</span><br/>';
                                // Cảnh báo deadline
                                $showWarning = false;
                                $warningClass = '';
                                $warningText = '';
                                $warningIcon = '';
                                if ($periodKey == 1) {
                                    // Kỳ 1: deadline 31/5
                                    if ($currentMonth <= 5) {
                                        if ($currentMonth == 5 && $currentDay >= 25) {
                                            $showWarning = true;
                                            $warningClass = 'deadline-warning critical';
                                            $warningIcon = '🚨';
                                            $warningText = 'KHẨN CẤP! Phải nộp trước 31/5/' . $currentYear;
                                        } else if ($currentMonth == 5) {
                                            $showWarning = true;
                                            $warningClass = 'deadline-warning danger';
                                            $warningIcon = '⚠️';
                                            $warningText = 'SẮP HẾT HẠN! Phải nộp trước 31/5/' . $currentYear;
                                        } else if ($currentMonth >= 4) {
                                            $showWarning = true;
                                            $warningClass = 'deadline-warning warning';
                                            $warningIcon = '⏰';
                                            $warningText = 'Cảnh báo: Phải nộp trước 31/5/' . $currentYear;
                                        }
                                    } else if ($currentMonth > 5) {
                                        $showWarning = true;
                                        $warningClass = 'deadline-warning danger';
                                        $warningIcon = '🚨';
                                        $warningText = 'QUÁ HẠN! Đã quá 31/5/' . $currentYear;
                                    }
                                } else {
                                    // Kỳ 2: deadline 31/10
                                    if ($currentMonth <= 10) {
                                        if ($currentMonth == 10 && $currentDay >= 25) {
                                            $showWarning = true;
                                            $warningClass = 'deadline-warning critical';
                                            $warningIcon = '🚨';
                                            $warningText = 'KHẨN CẤP! Phải nộp trước 31/10/' . $currentYear;
                                        } else if ($currentMonth == 10) {
                                            $showWarning = true;
                                            $warningClass = 'deadline-warning danger';
                                            $warningIcon = '⚠️';
                                            $warningText = 'SẮP HẾT HẠN! Phải nộp trước 31/10/' . $currentYear;
                                        } else if ($currentMonth >= 9) {
                                            $showWarning = true;
                                            $warningClass = 'deadline-warning warning';
                                            $warningIcon = '⏰';
                                            $warningText = 'Cảnh báo: Phải nộp trước 31/10/' . $currentYear;
                                        }
                                    } else if ($currentMonth > 10) {
                                        $showWarning = true;
                                        $warningClass = 'deadline-warning danger';
                                        $warningIcon = '🚨';
                                        $warningText = 'QUÁ HẠN! Đã quá 31/10/' . $currentYear;
                                    }
                                }
                                if ($showWarning) {
                                    $result .= '<span class="' . $warningClass . '">' . $warningIcon . ' ' . $warningText . '</span>';
                                }
                            } else if ($remainingAmount < 0) {
                                $result .= '<span class="payment-status surplus">Thừa: ' . number_format(abs($remainingAmount), 0, ',', '.') . ' VND</span>';
                            } else {
                                $result .= '<span class="payment-status paid">✓ Đã thanh toán đủ</span>';
                            }
                        } else {
                            $result .= '<small class="text-muted">Chưa có thông tin giá thuê</small>';
                        }

                        // Thêm ghi chú deadline chi tiết cho mỗi kỳ
                        $deadline = $periodKey == 1 ? '31/5' : '31/10';
                        $deadlineNote = '<div style="margin-top: 5px; padding: 3px 6px; background-color: #f8f9fa; border-left: 3px solid ';
                        if ($periodKey == 1) {
                            $deadlineNote .= '#17a2b8;';
                        } else {
                            $deadlineNote .= '#ffc107;';
                        }
                        $deadlineNote .= ' font-size: 0.75em;">';
                        $deadlineNote .= '<strong>📅 Deadline ' . $periodInfo['label'] . ':</strong> Phải nộp tiền thuê đất trước <strong>' . $deadline . '/' . $currentYear . '</strong><br/>';
                        $deadlineNote .= '<em>Hệ thống sẽ cảnh báo từ tháng ';
                        if ($periodKey == 1) {
                            $deadlineNote .= '4 và khẩn cấp từ ngày 25/5';
                        } else {
                            $deadlineNote .= '9 và khẩn cấp từ ngày 25/10';
                        }
                        $deadlineNote .= '</em></div>';
                        $result .= $deadlineNote;

                        if ($periodKey == 1) {
                            $result .= '<hr/>';
                        }
                    }

                    $result .= '</div>';
                    return $result;
                })
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
                    $deleteBtn = '<form action="' . route('admin.land-rental-contracts.destroy', $item) . '" method="POST" class="d-inline mb-0" onsubmit="return confirm(\'Bạn có chắc chắn muốn xóa hợp đồng này?\')">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>';
                    return '<div class="btn-group" role="group">' . $showBtn . ' ' . $priceBtn . ' ' . $paymentBtn . ' ' . $editBtn . ' ' . $deleteBtn . '</div>';
                })
                ->rawColumns(['contract_and_decision', 'rental_zone', 'area', 'land_tax_price', 'payment', 'rental_period', 'action'])
                ->make(true);
        }

        return view('admin.land-rental.contracts.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.land-rental.contracts.create');
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
            'rental_purpose' => 'nullable|string|max:255',
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
            'rental_purpose' => $request->rental_purpose,
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
        return view('admin.land-rental.contracts.show', compact('landRentalContract'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LandRentalContract $landRentalContract)
    {
        return view('admin.land-rental.contracts.edit', compact('landRentalContract'));
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
            'rental_purpose' => 'nullable|string|max:255',
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
            'rental_purpose' => $request->rental_purpose,
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

    /**
     * Export land rental contracts to Excel
     */
    public function export()
    {
        $filename = 'danh-sach-hop-dong-thue-dat-' . Carbon::now()->format('dmY') . '.xlsx';
        return Excel::download(new LandRentalContractsExport(), $filename);
    }

    /**
     * Export land tax calculation table for a specific period and year
     */
    public function exportTaxCalculation(Request $request)
    {
        $period = $request->input('period', 1); // Default to period 1
        $year = $request->input('year', date('Y')); // Default to current year

        $filename = 'bang-tinh-tien-thue-dat-ky-' . $period . '-nam-' . $year . '-' . Carbon::now()->format('dmY') . '.xlsx';
        return Excel::download(new LandTaxCalculationExport($period, $year), $filename);
    }

    /**
     * Export land rental plan for a specific year
     */
    public function exportRentalPlan(Request $request)
    {
        $year = $request->input('year', date('Y')); // Lấy năm từ request, nếu không có thì lấy năm hiện tại

        $filename = 'ke-hoach-nop-tien-thue-dat-nam-' . $year . '-' . Carbon::now()->format('dmY') . '.xlsx';
        return Excel::download(new LandRentalPlanExport($year), $filename);
    }

    /**
     * Export land tax plan for a specific year
     */
    public function exportTaxPlan(Request $request)
    {
        $year = $request->input('year', date('Y')); // Lấy năm từ request, nếu không có thì lấy năm hiện tại

        $filename = 'ke-hoach-nop-thue-pnn-nam-' . $year . '-' . Carbon::now()->format('dmY') . '.xlsx';
        return Excel::download(new LandTaxPlanExport($year), $filename);
    }

    /**
     * Export non-agricultural land tax calculation for a specific year
     */
    public function exportNonAgriTax(Request $request)
    {
        $year = $request->input('year', date('Y')); // Lấy năm từ request, nếu không có thì lấy năm hiện tại

        $filename = 'bang-tinh-thue-sdd-pnn-nam-' . $year . '-' . Carbon::now()->format('dmY') . '.xlsx';
        return Excel::download(new LandNonAgriTaxCalculationExport($year), $filename);
    }


}
