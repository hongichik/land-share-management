<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\InvestorsImport;
use App\Models\SecuritiesManagement;
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
                // Cột 5: Phân loại
                ->addColumn('group5_classification', function ($row) {
                    return '<div class="group-header group-classification" style="margin-bottom: 5px;">🏷️ Phân loại</div>' .
                        '<div class="group-content">' .
                        '<strong>CNTC:</strong> ' . ($row->cntc == '1' ? 'Cá nhân (CN)' : ($row->cntc == '2' ? 'Tổ chức (TC)' : ($row->cntc ?? 'N/A'))) . '<br>' .
                        '<strong>TXNUM:</strong> ' . ($row->txnum ?? 'N/A') . 
                        '</div>';
                })
                // Cột 6: Thông tin ngân hàng
                ->addColumn('group6_bank', function ($row) {
                    return '<div class="group-header group-bank" style="margin-bottom: 5px;">🏦 Ngân hàng</div>' .
                        '<div class="group-content">' .
                        '<strong>Tài khoản:</strong> ' . ($row->bank_account ?? 'N/A') . '<br>' .
                        '<strong>Ngân hàng:</strong> ' . ($row->bank_name ?? 'N/A') . '<br>' .
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
                    $btn .= '<button type="button" class="btn btn-info btn-sm" onclick="editBankInfo(' . $row->id . ', \'' . addslashes($row->full_name) . '\', \'' . addslashes($row->bank_name ?? '') . '\', \'' . addslashes($row->bank_account ?? '') . '\')" title="Sửa ngân hàng">';
                    $btn .= '<i class="fas fa-edit"></i></button>';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord(' . $row->id . ')" title="Xóa">';
                    $btn .= '<i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['group1_personal', 'group2_investor', 'group3_deposited', 'group4_options', 'group5_classification', 'group6_bank', 'group7_notes', 'action'])
                ->make(true);
        }

        return view('admin.securities.dividend.index');
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
     * Update bank information for a securities record
     */
    public function updateBank(Request $request, SecuritiesManagement $securitiesManagement)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_account' => 'required|string|max:255',
        ]);

        try {
            $securitiesManagement->update([
                'bank_name' => $request->input('bank_name'),
                'bank_account' => $request->input('bank_account'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin ngân hàng thành công!',
                'data' => [
                    'bank_name' => $securitiesManagement->bank_name,
                    'bank_account' => $securitiesManagement->bank_account,
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
            $result = $importer->executeImport($rows, $blockPositions);

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
}

