<?php

namespace App\Http\Controllers\Admin\Securities;

use App\Http\Controllers\Controller;
use App\Imports\InvestorsImport;
use App\Models\Dividend;
use App\Models\DividendRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class DividendController extends Controller
{
    /**
     * Get summary statistics for dashboard
     */
    public function getSummaryStats(Request $request)
    {
        $filter = $request->input('filter', 'all');
        
        $query = Dividend::query();
        
        $totalInvestors = $query->count();

        return response()->json([
            'total_investors' => number_format($totalInvestors),
            'active_investors' => number_format($totalInvestors),
            'not_deposited' => '0',
            'deposited' => '0',
            'active_percentage' => $totalInvestors > 0 ? round(($totalInvestors / $totalInvestors) * 100, 1) : 0,
            'deposited_percentage' => 0
        ]);
    }

    /**
     * Get list of Vietnamese banks
     */
    public function getBanksList(Request $request)
    {
        $search = $request->input('search', '');
        
        $banks = [
            ['id' => 'ACB', 'text' => 'ACB - Ngân hàng Á Châu'],
            ['id' => 'AGRIBANK', 'text' => 'AGRIBANK - Ngân hàng Nông nghiệp'],
            ['id' => 'BIDV', 'text' => 'BIDV - Ngân hàng Đầu tư và Phát triển'],
            ['id' => 'CTG', 'text' => 'CTG - Ngân hàng Công Thương'],
            ['id' => 'EXIMBANK', 'text' => 'EXIMBANK - Ngân hàng Xuất Nhập khẩu'],
            ['id' => 'LPB', 'text' => 'LPB - Ngân hàng Kienlongbank'],
            ['id' => 'MBB', 'text' => 'MBB - Ngân hàng Quân Đội'],
            ['id' => 'SACOMBANK', 'text' => 'SACOMBANK - Ngân hàng SACOM'],
            ['id' => 'SHB', 'text' => 'SHB - Ngân hàng SHB'],
            ['id' => 'TECHCOMBANK', 'text' => 'TECHCOMBANK - Ngân hàng Kỹ Thương'],
            ['id' => 'TPB', 'text' => 'TPB - Ngân hàng Tiên Phong'],
            ['id' => 'VIB', 'text' => 'VIB - Ngân hàng VIB'],
            ['id' => 'VIETCOMBANK', 'text' => 'VIETCOMBANK - Ngân hàng Ngoại Thương Việt Nam'],
            ['id' => 'VIETINBANK', 'text' => 'VIETINBANK - Ngân hàng Công nghiệp Việt Nam'],
            ['id' => 'VPBANK', 'text' => 'VPBANK - Ngân hàng VP'],
            ['id' => 'OCB', 'text' => 'OCB - Ngân hàng Phương Đông'],
            ['id' => 'SEABANK', 'text' => 'SEABANK - Ngân hàng Biển'],
            ['id' => 'HDBANK', 'text' => 'HDBANK - Ngân hàng Phát triển'],
            ['id' => 'ABBANK', 'text' => 'ABBANK - Ngân hàng AB'],
            ['id' => 'ANBANK', 'text' => 'ANBANK - Ngân hàng An Bình'],
            ['id' => 'KIENLONGBANK', 'text' => 'KIENLONGBANK - Ngân hàng Kiên Long'],
            ['id' => 'SCB', 'text' => 'SCB - Ngân hàng Sài Gòn'],
            ['id' => 'VCCB', 'text' => 'VCCB - Ngân hàng VCC'],
            ['id' => 'BAO_VIET_BANK', 'text' => 'Bảo Việt Bank'],
            ['id' => 'LIENVIET', 'text' => 'LienVietPostBank'],
        ];
        
        // Filter based on search
        if (!empty($search)) {
            $banks = array_filter($banks, function($bank) use ($search) {
                return stripos($bank['text'], $search) !== false || stripos($bank['id'], $search) !== false;
            });
        }
        
        return response()->json([
            'results' => array_values($banks)
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
            
            $securities = Dividend::select([
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
                'cntc',
                'txnum',
                'bank_account',
                'bank_name',
                'notes',
                'created_at'
            ]);

            // Áp dụng bộ lọc
            $filters = explode(',', $filter);
            $filters = array_map('trim', $filters);
            $filters = array_filter($filters); // Remove empty values
            
            foreach ($filters as $f) {
                switch ($f) {
                    case 'signed':
                        // Hiển thị những người đã lưu ký: có deposited_shares_quantity > 0
                        $securities = $securities->whereHas('dividendRecords', function($query) {
                            $query->where('deposited_shares_quantity', '>', 0);
                        });
                        break;
                    case 'unsigned':
                        // Hiển thị những người chưa lưu ký: có non_deposited_shares_quantity > 0
                        $securities = $securities->whereHas('dividendRecords', function($query) {
                            $query->where('non_deposited_shares_quantity', '>', 0);
                        });
                        break;
                    case 'unpaid':
                        // Hiển thị những người chưa thanh toán
                        $securities = $securities->where(function($query) {
                            $query->doesntHave('dividendRecords')
                                  ->orWhereDoesntHave('dividendRecords', function($subquery) {
                                      $subquery->whereIn('payment_status', ['paid_not_deposited', 'paid_deposited', 'paid_both']);
                                  });
                        });
                        break;
                    case 'paid_not_deposited':
                        // Hiển thị những người đã thanh toán cho chưa lưu ký
                        $securities = $securities->whereHas('dividendRecords', function($query) {
                            $query->where('payment_status', 'paid_not_deposited');
                        });
                        break;
                    case 'paid_deposited':
                        // Hiển thị những người đã thanh toán cho đã lưu ký
                        $securities = $securities->whereHas('dividendRecords', function($query) {
                            $query->where('payment_status', 'paid_deposited');
                        });
                        break;
                    case 'paid_both':
                        // Hiển thị những người đã thanh toán cho cả hai
                        $securities = $securities->whereHas('dividendRecords', function($query) {
                            $query->where('payment_status', 'paid_both');
                        });
                        break;
                }
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
                    return '<div class="group-content">' .
                        '<strong>Tên:</strong> ' . $row->full_name . '<br>' .
                        '<strong>Địa chỉ:</strong> ' . $row->address . '<br>' .
                        '<strong>Điện thoại:</strong> ' . ($row->phone ?? 'N/A') . '<br>' .
                        '<strong>Email:</strong> ' . ($row->email ?? 'N/A') . '<br>' .
                        '<strong>Quốc tịch:</strong> ' . ($row->nationality ?? 'N/A') . 
                        '</div>';
                })
                // Cột 2: Thông tin đầu tư
                ->addColumn('group2_investor', function ($row) {
                    return '<div class="group-content">' .
                        '<strong>SID:</strong> ' . ($row->sid ?? 'N/A') . '<br>' .
                        '<strong>Mã NĐT:</strong> ' . ($row->investor_code ?? 'N/A') . '<br>' .
                        '<strong>Số ĐK:</strong> ' . ($row->registration_number ?? 'N/A') . '<br>' .
                        '<strong>Ngày PH:</strong> ' . ($row->issue_date ? $row->issue_date->format('d/m/Y') : 'N/A') . '<br>' .
                        '</div>';
                })
                // Cột 3: Cổ tức chưa nhận
                ->addColumn('group3_unpaid_dividend', function ($row) use ($filter, $request) {
                    // Lấy tất cả bản ghi cổ tức
                    $records = DividendRecord::where('dividend_id', $row->id)->get();
                    
                    // Kiểm tra filter hiện tại
                    $showUndeposited = true;  // Mặc định hiển thị cả hai
                    $showDeposited = true;
                    
                    $filters = explode(',', $filter);
                    $filters = array_map('trim', $filters);
                    $filters = array_filter($filters);
                    
                    if (in_array('signed', $filters)) {
                        $showUndeposited = false;  // Chỉ hiển thị deposited
                    } elseif (in_array('unsigned', $filters)) {
                        $showDeposited = false;    // Chỉ hiển thị undeposited
                    }
                    
                    // Tính tổng tiền đã nhận
                    $totalPaidNotDeposited = 0;
                    $totalPaidDeposited = 0;
                    $totalUnpaidNotDeposited = 0;
                    $totalUnpaidDeposited = 0;
                    
                    foreach ($records as $record) {
                        // Tính tiền đã thanh toán dựa trên payment_status
                        if (in_array($record->payment_status, ['paid_not_deposited', 'paid_both'])) {
                            $totalPaidNotDeposited += (float)$record->non_deposited_amount_before_tax;
                        } else {
                            $totalUnpaidNotDeposited += (float)$record->non_deposited_amount_before_tax;
                        }
                        
                        if (in_array($record->payment_status, ['paid_deposited', 'paid_both'])) {
                            $totalPaidDeposited += (float)$record->deposited_amount_before_tax;
                        } else {
                            $totalUnpaidDeposited += (float)$record->deposited_amount_before_tax;
                        }
                    }
                    
                    $html = '<div class="group-content" style="font-size: 12px;">';
                    
                    // Hiển thị phần chưa lưu ký nếu cần
                    if ($showUndeposited) {
                        $totalPaidUndeposited = $totalPaidNotDeposited;
                        $totalUnpaidUndeposited = $totalUnpaidNotDeposited;
                        $html .= '<strong style="color: #28a745;">Đã nhận (Chưa LK):</strong> ' . number_format($totalPaidUndeposited, 0, ',', '.') . ' đ<br>';
                        $html .= '<strong style="color: #dc3545;">Chưa nhận (Chưa LK):</strong> ' . number_format($totalUnpaidUndeposited, 0, ',', '.') . ' đ<br>';
                    }
                    
                    // Hiển thị phần đã lưu ký nếu cần
                    if ($showDeposited) {
                        $totalPaidDep = $totalPaidDeposited;
                        $totalUnpaidDep = $totalUnpaidDeposited;
                        $html .= '<strong style="color: #28a745;">Đã nhận (Đã LK):</strong> ' . number_format($totalPaidDep, 0, ',', '.') . ' đ<br>';
                        $html .= '<strong style="color: #dc3545;">Chưa nhận (Đã LK):</strong> ' . number_format($totalUnpaidDep, 0, ',', '.') . ' đ';
                    }
                    
                    $html .= '</div>';
                    return $html;
                })
                // Cột 4: Thông tin ngân hàng
                ->addColumn('group5_bank', function ($row) {
                    return '<div class="group-content">' .
                        '<strong>Tài khoản:</strong> ' . ($row->bank_account ?? 'N/A') . '<br>' .
                        '<strong>Ngân hàng:</strong> ' . ($row->bank_name ?? 'N/A') . '<br>' .
                        '</div>';
                })
                // Cột 5: Ghi chú
                ->addColumn('group6_notes', function ($row) {
                    $notes = $row->notes ?? 'N/A';
                    $shortNotes = strlen($notes) > 50 ? substr($notes, 0, 50) . '...' : $notes;
                    return '<div class="group-content" title="' . htmlspecialchars($notes) . '">' . 
                        htmlspecialchars($shortNotes) . 
                        '</div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<button type="button" class="btn btn-primary btn-sm" onclick="viewDividendDetails(' . $row->id . ', \'' . addslashes($row->full_name) . '\')" title="Chi tiết cổ tức">';
                    $btn .= '<i class="fas fa-file-invoice-dollar"></i></button>';
                    $btn .= '<button type="button" class="btn btn-info btn-sm" onclick="editBankInfo(' . $row->id . ', \'' . addslashes($row->full_name) . '\', \'' . addslashes($row->bank_name ?? '') . '\', \'' . addslashes($row->bank_account ?? '') . '\')" title="Sửa ngân hàng">';
                    $btn .= '<i class="fas fa-edit"></i></button>';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord(' . $row->id . ')" title="Xóa">';
                    $btn .= '<i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['group1_personal', 'group2_investor', 'group3_unpaid_dividend', 'group5_bank', 'group6_notes', 'action'])
                ->make(true);
        }

        return view('admin.securities.dividend.index');
    }


    /**
     * Get dividend details for a specific investor
     */
    public function dividendDetails(Dividend $dividend)
    {
        $dividendRecords = DividendRecord::where('dividend_id', $dividend->id)
            ->orderBy('payment_date', 'desc')
            ->get();

        return view('admin.securities.dividend.details', [
            'investor' => $dividend,
            'dividendRecords' => $dividendRecords
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dividend $dividend)
    {
        $dividend->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa thông tin cổ tức thành công!'
        ]);
    }

    /**
     * Update bank information for a securities record
     */
    public function updateBank(Request $request, Dividend $dividend)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_account' => 'required|string|max:255',
        ]);

        try {
            $dividend->update([
                'bank_name' => $request->input('bank_name'),
                'bank_account' => $request->input('bank_account'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin ngân hàng thành công!',
                'data' => [
                    'bank_name' => $dividend->bank_name,
                    'bank_account' => $dividend->bank_account,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Update bank info error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thông tin ngân hàng!'
            ], 422);
        }
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
        
        $query = Dividend::query();
        
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

            // Lấy dữ liệu từ file Excel
            $allSheets = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\InvestorsImport(), $file) ?? [];
            
            // Lấy sheet đầu tiên
            $rows = reset($allSheets) ?: [];

            $importer = new InvestorsImport();
            $blockPositions = $importer->getInvestorBlockPositions($rows);
            $checkResults = $importer->getPreviewData($rows, $blockPositions);

           return response()->json([
                'success' => true,
                'preview' => $checkResults['preview'],
                'insertCount' => $checkResults['insertCount'],
                'updateCount' => $checkResults['updateCount'],
                'totalRows' => $checkResults['totalRows'],
                'message' => 'Tìm thấy ' . $checkResults['insertCount'] . ' nhà đầu tư mới và ' . $checkResults['updateCount'] . ' nhà đầu tư cần cập nhật'
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
            
            // Validate file type
            if (!in_array($file->getClientOriginalExtension(), ['xlsx', 'xls', 'csv'])) {
                return response()->json(['success' => false, 'error' => 'File phải có định dạng .xlsx, .xls hoặc .csv'], 400);
            }

            // Lấy dữ liệu từ file Excel
            $allSheets = \Maatwebsite\Excel\Facades\Excel::toArray(new InvestorsImport(), $file) ?? [];
            
            // Lấy sheet đầu tiên
            $rows = reset($allSheets) ?: [];

            $importer = new InvestorsImport();
            $blockPositions = $importer->getInvestorBlockPositions($rows);
            
            // Get dividend parameters from request
            $paymentDate = $request->input('payment_date');
            $dividendPricePerShare = $request->input('dividend_price_per_share');
            
            $result = $importer->executeImport($rows, $blockPositions, $paymentDate, $dividendPricePerShare);

            return response()->json([
                'success' => true,
                'message' => 'Import dữ liệu thành công!',
                'processedRows' => $result['processedRows'],
                'errors' => $result['errors']
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import confirm error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Lỗi xử lý file: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Display dividend payment page
     */
    public function paymentPage()
    {
        return view('admin.securities.dividend.payment');
    }

    /**
     * Search investors for payment
     */
    public function searchInvestors(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $searchBy = $request->input('search_by', 'all');
            $page = $request->input('page', 1);
            $perPage = 10;

            $query = Dividend::query();

            // Build search conditions
            if (!empty($searchTerm)) {
                if ($searchBy === 'all') {
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('sid', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('registration_number', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('investor_code', 'LIKE', "%{$searchTerm}%");
                    });
                } elseif ($searchBy === 'phone') {
                    $query->where('phone', 'LIKE', "%{$searchTerm}%");
                } elseif ($searchBy === 'full_name') {
                    $query->where('full_name', 'LIKE', "%{$searchTerm}%");
                } elseif ($searchBy === 'sid') {
                    $query->where('sid', 'LIKE', "%{$searchTerm}%");
                } elseif ($searchBy === 'registration_number') {
                    $query->where('registration_number', 'LIKE', "%{$searchTerm}%");
                }
            }

            $total = $query->count();
            
            $investors = $query->select([
                'id',
                'full_name',
                'phone',
                'sid',
                'registration_number',
                'investor_code',
                'address',
                'email',
                'bank_account',
                'bank_name'
            ])
            ->orderBy('full_name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(function($investor) {
                // Get unpaid dividend amount - tính tổng tiền chưa nhận
                $records = DividendRecord::where('dividend_id', $investor->id)->get();
                
                $totalUnpaidNotDeposited = 0;
                $totalUnpaidDeposited = 0;
                
                foreach ($records as $record) {
                    // Nếu chưa thanh toán cho chưa lưu ký
                    if (!in_array($record->payment_status, ['paid_not_deposited', 'paid_both'])) {
                        $totalUnpaidNotDeposited += (float)$record->non_deposited_amount_before_tax;
                    }
                    
                    // Nếu chưa thanh toán cho đã lưu ký
                    if (!in_array($record->payment_status, ['paid_deposited', 'paid_both'])) {
                        $totalUnpaidDeposited += (float)$record->deposited_amount_before_tax;
                    }
                }
                
                $unpaidDividend = $totalUnpaidNotDeposited + $totalUnpaidDeposited;

                $investor->unpaid_dividend = $unpaidDividend;
                $investor->unpaid_not_deposited = $totalUnpaidNotDeposited;
                $investor->unpaid_deposited = $totalUnpaidDeposited;
                $investor->can_select = $unpaidDividend > 0; // Chỉ cho phép chọn nếu có tiền chưa nhận
                return $investor;
            });

            return response()->json([
                'success' => true,
                'data' => $investors,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => ceil($total / $perPage)
            ]);

        } catch (\Exception $e) {
            Log::error('Search investors error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tìm kiếm: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Process dividend payment for selected investors
     */
    public function processPayment(Request $request)
    {
        try {
            $investorIds = $request->input('investor_ids', []);
            $transferDate = $request->input('transfer_date');
            $notes = $request->input('notes', '');

            if (empty($investorIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ít nhất một nhà đầu tư!'
                ], 422);
            }

            // Update dividend records to mark as paid
            $updated = DividendRecord::whereIn('dividend_id', $investorIds)
                ->whereIn('payment_status', ['unpaid', 'paid_not_deposited', 'paid_deposited'])
                ->update([
                    'payment_status' => 'paid_both', // Khi thanh toán từ giao diện -> đã trả đã lưu ký
                    'transfer_date' => $transferDate ? date('Y-m-d H:i:s', strtotime($transferDate)) : now(),
                    'notes' => $notes
                ]);


            return response()->json([
                'success' => true,
                'message' => "Thanh toán cổ tức cho {$updated} hồ sơ thành công!",
                'updated_count' => $updated
            ]);

        } catch (\Exception $e) {
            Log::error('Process payment error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xử lý thanh toán: ' . $e->getMessage()
            ], 422);
        }
    }
}
