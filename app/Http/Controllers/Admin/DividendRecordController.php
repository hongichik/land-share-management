<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DividendRecord;
use App\Exports\DividendRecordExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class DividendRecordController extends Controller
{
    /**
     * Display a listing of the dividend records grouped by payment date
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Lấy dữ liệu grouped by payment_date - TẤT CẢ
            $dividendRecords = DividendRecord::selectRaw('
                payment_date,
                COUNT(DISTINCT securities_management_id) as investor_count,
                SUM(COALESCE(non_deposited_shares_quantity, 0) + COALESCE(deposited_shares_quantity, 0)) as total_shares,
                dividend_percentage,
                SUM(COALESCE(deposited_personal_income_tax, 0)) as total_deposited_tax,
                SUM(COALESCE(non_deposited_personal_income_tax, 0)) as total_non_deposited_tax,
                SUM(COALESCE(deposited_amount_before_tax, 0)) as total_deposited_amount,
                SUM(COALESCE(non_deposited_amount_before_tax, 0)) as total_non_deposited_amount,
                SUM(COALESCE(deposited_amount_before_tax, 0) + COALESCE(non_deposited_amount_before_tax, 0)) as total_amount_before_tax,
                MAX(created_at) as created_at
            ')
            ->whereNotNull('payment_date')
            ->groupBy('payment_date', 'dividend_percentage')
            ->orderBy('payment_date', 'desc');

            return DataTables::of($dividendRecords)
                ->addIndexColumn()
                ->addColumn('payment_date_formatted', function ($row) {
                    return $row->payment_date ? date('d/m/Y', strtotime($row->payment_date)) : 'N/A';
                })
                ->addColumn('total_shares_formatted', function ($row) {
                    return number_format($row->total_shares ?? 0);
                })
                ->addColumn('dividend_percentage_formatted', function ($row) {
                    return ($row->dividend_percentage ?? 0) . '%';
                })
                ->addColumn('tax_info', function ($row) {
                    $depositedTax = $row->total_deposited_tax ?? 0;
                    $nonDepositedTax = $row->total_non_deposited_tax ?? 0;
                    $totalTax = $depositedTax + $nonDepositedTax;
                    
                    return '<div class="tax-info-container">' .
                        '<div><strong>Tổng:</strong> ' . number_format($totalTax, 0, ',', '.') . ' đ</div>' .
                        '<div style="font-size: 12px; color: #666; margin-top: 4px;">' .
                            '<div>Đã lưu ký: ' . number_format($depositedTax, 0, ',', '.') . ' đ</div>' .
                            '<div>Chưa lưu ký: ' . number_format($nonDepositedTax, 0, ',', '.') . ' đ</div>' .
                        '</div>' .
                    '</div>';
                })
                ->addColumn('total_amount_formatted', function ($row) {
                    $depositedAmount = $row->total_deposited_amount ?? 0;
                    $nonDepositedAmount = $row->total_non_deposited_amount ?? 0;
                    $totalAmount = $depositedAmount + $nonDepositedAmount;
                    
                    return '<div class="amount-info-container">' .
                        '<div><strong>Tổng:</strong> ' . number_format($totalAmount, 0, ',', '.') . ' đ</div>' .
                        '<div style="font-size: 12px; color: #666; margin-top: 4px;">' .
                            '<div>Đã lưu ký: ' . number_format($depositedAmount, 0, ',', '.') . ' đ</div>' .
                            '<div>Chưa lưu ký: ' . number_format($nonDepositedAmount, 0, ',', '.') . ' đ</div>' .
                        '</div>' .
                    '</div>';
                })
                ->addColumn('investor_count_formatted', function ($row) {
                    return $row->investor_count ?? 0;
                })
                ->addColumn('action', function ($row) {
                    $paymentDateFormatted = $row->payment_date ? date('Y-m-d', strtotime($row->payment_date)) : '';
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('admin.securities.dividend-record.detail', ['paymentDate' => $paymentDateFormatted]) . '" class="btn btn-info btn-sm" title="Xem chi tiết">';
                    $btn .= '<i class="fas fa-eye"></i></a>';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord(\'' . $paymentDateFormatted . '\')" title="Xóa">';
                    $btn .= '<i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['tax_info', 'total_amount_formatted', 'action'])
                ->make(true);
        }

        return view('admin.securities.dividend-record.index');
    }

    /**
     * Display dividend records that have been paid
     */
    public function paid(Request $request)
    {
        if ($request->ajax()) {
            // Lấy dữ liệu grouped by transfer_date - CHỈ NHỮNG CÁI ĐÃ TRẢ
            $dividendRecords = DividendRecord::selectRaw('
                transfer_date,
                MAX(payment_date) as payment_date,
                COUNT(DISTINCT securities_management_id) as investor_count,
                SUM(COALESCE(non_deposited_shares_quantity, 0) + COALESCE(deposited_shares_quantity, 0)) as total_shares,
                dividend_percentage,
                SUM(COALESCE(deposited_personal_income_tax, 0)) as total_deposited_tax,
                SUM(COALESCE(non_deposited_personal_income_tax, 0)) as total_non_deposited_tax,
                SUM(COALESCE(deposited_amount_before_tax, 0)) as total_deposited_amount,
                SUM(COALESCE(non_deposited_amount_before_tax, 0)) as total_non_deposited_amount,
                SUM(COALESCE(deposited_amount_before_tax, 0) + COALESCE(non_deposited_amount_before_tax, 0)) as total_amount_before_tax,
                MAX(created_at) as created_at
            ')
            ->whereNotNull('transfer_date')
            ->where('payment_status', 'paid')
            ->groupBy('transfer_date', 'dividend_percentage')
            ->orderBy('transfer_date', 'desc');

            return DataTables::of($dividendRecords)
                ->addIndexColumn()
                ->addColumn('transfer_date_formatted', function ($row) {
                    return $row->transfer_date ? date('d/m/Y', strtotime($row->transfer_date)) : 'N/A';
                })
                ->addColumn('payment_date_formatted', function ($row) {
                    return $row->payment_date ? date('d/m/Y', strtotime($row->payment_date)) : 'N/A';
                })
                ->addColumn('total_shares_formatted', function ($row) {
                    return number_format($row->total_shares ?? 0);
                })
                ->addColumn('dividend_percentage_formatted', function ($row) {
                    return ($row->dividend_percentage ?? 0) . '%';
                })
                ->addColumn('tax_info', function ($row) {
                    $depositedTax = $row->total_deposited_tax ?? 0;
                    $nonDepositedTax = $row->total_non_deposited_tax ?? 0;
                    $totalTax = $depositedTax + $nonDepositedTax;
                    
                    return '<div class="tax-info-container">' .
                        '<div><strong>Tổng:</strong> ' . number_format($totalTax, 0, ',', '.') . ' đ</div>' .
                        '<div style="font-size: 12px; color: #666; margin-top: 4px;">' .
                            '<div>Đã lưu ký: ' . number_format($depositedTax, 0, ',', '.') . ' đ</div>' .
                            '<div>Chưa lưu ký: ' . number_format($nonDepositedTax, 0, ',', '.') . ' đ</div>' .
                        '</div>' .
                    '</div>';
                })
                ->addColumn('total_amount_formatted', function ($row) {
                    $depositedAmount = $row->total_deposited_amount ?? 0;
                    $nonDepositedAmount = $row->total_non_deposited_amount ?? 0;
                    $totalAmount = $depositedAmount + $nonDepositedAmount;
                    
                    return '<div class="amount-info-container">' .
                        '<div><strong>Tổng:</strong> ' . number_format($totalAmount, 0, ',', '.') . ' đ</div>' .
                        '<div style="font-size: 12px; color: #666; margin-top: 4px;">' .
                            '<div>Đã lưu ký: ' . number_format($depositedAmount, 0, ',', '.') . ' đ</div>' .
                            '<div>Chưa lưu ký: ' . number_format($nonDepositedAmount, 0, ',', '.') . ' đ</div>' .
                        '</div>' .
                    '</div>';
                })
                ->addColumn('investor_count_formatted', function ($row) {
                    return $row->investor_count ?? 0;
                })
                ->addColumn('action', function ($row) {
                    $transferDateFormatted = $row->transfer_date ? date('Y-m-d', strtotime($row->transfer_date)) : '';
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('admin.securities.dividend-record.paid.detail', ['transferDate' => $transferDateFormatted]) . '" class="btn btn-info btn-sm" title="Xem chi tiết">';
                    $btn .= '<i class="fas fa-eye"></i></a>';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord(\'' . $transferDateFormatted . '\')" title="Xóa">';
                    $btn .= '<i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['tax_info', 'total_amount_formatted', 'action'])
                ->make(true);
        }

        return view('admin.securities.dividend-record.paid');
    }

    /**
     * Display dividend records that are unpaid
     */
    public function unpaid(Request $request)
    {
        if ($request->ajax()) {
            // Lấy danh sách grouped by investor - NHỮNG CÁI CHƯA TRẢ
            $records = DividendRecord::selectRaw('
                securities_management_id,
                SUM(COALESCE(non_deposited_shares_quantity, 0) + COALESCE(deposited_shares_quantity, 0)) as total_shares,
                COUNT(*) as record_count,
                MAX(dividend_percentage) as dividend_percentage,
                SUM(COALESCE(deposited_personal_income_tax, 0)) as total_deposited_tax,
                SUM(COALESCE(non_deposited_personal_income_tax, 0)) as total_non_deposited_tax,
                SUM(COALESCE(deposited_amount_before_tax, 0)) as total_deposited_amount,
                SUM(COALESCE(non_deposited_amount_before_tax, 0)) as total_non_deposited_amount,
                SUM(COALESCE(deposited_amount_before_tax, 0) + COALESCE(non_deposited_amount_before_tax, 0)) as total_amount_before_tax,
                MAX(created_at) as created_at
            ')
            ->with('securitiesManagement')
            ->where(function ($query) {
                $query->where('payment_status', 'unpaid')
                      ->orWhereNull('payment_status');
            })
            ->groupBy('securities_management_id')
            ->orderBy('created_at', 'desc');

            return DataTables::of($records)
                ->addIndexColumn()
                ->addColumn('investor_name', function ($row) {
                    return $row->securitiesManagement->full_name ?? 'N/A';
                })
                ->addColumn('investor_code', function ($row) {
                    return $row->securitiesManagement->investor_code ?? 'N/A';
                })
                ->addColumn('total_shares_formatted', function ($row) {
                    return number_format($row->total_shares ?? 0);
                })
                ->addColumn('record_count_formatted', function ($row) {
                    return $row->record_count ?? 0;
                })
                ->addColumn('dividend_percentage_formatted', function ($row) {
                    return ($row->dividend_percentage ?? 0) . '%';
                })
                ->addColumn('tax_info', function ($row) {
                    $depositedTax = $row->total_deposited_tax ?? 0;
                    $nonDepositedTax = $row->total_non_deposited_tax ?? 0;
                    $totalTax = $depositedTax + $nonDepositedTax;
                    
                    return '<div class="tax-info-container">' .
                        '<div><strong>Tổng:</strong> ' . number_format($totalTax, 0, ',', '.') . ' đ</div>' .
                        '<div style="font-size: 12px; color: #666; margin-top: 4px;">' .
                            '<div>Đã lưu ký: ' . number_format($depositedTax, 0, ',', '.') . ' đ</div>' .
                            '<div>Chưa lưu ký: ' . number_format($nonDepositedTax, 0, ',', '.') . ' đ</div>' .
                        '</div>' .
                    '</div>';
                })
                ->addColumn('total_amount_after_tax_formatted', function ($row) {
                    $depositedAfterTax = ($row->total_deposited_amount ?? 0) - ($row->total_deposited_tax ?? 0);
                    $nonDepositedAfterTax = ($row->total_non_deposited_amount ?? 0) - ($row->total_non_deposited_tax ?? 0);
                    $totalAfterTax = $depositedAfterTax + $nonDepositedAfterTax;
                    
                    return '<div class="amount-info-container">' .
                        '<div><strong>Tổng:</strong> ' . number_format($totalAfterTax, 0, ',', '.') . ' đ</div>' .
                        '<div style="font-size: 12px; color: #666; margin-top: 4px;">' .
                            '<div>Đã lưu ký: ' . number_format($depositedAfterTax, 0, ',', '.') . ' đ</div>' .
                            '<div>Chưa lưu ký: ' . number_format($nonDepositedAfterTax, 0, ',', '.') . ' đ</div>' .
                        '</div>' .
                    '</div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('admin.securities.dividend-record.unpaid.detail', ['investorId' => $row->securities_management_id]) . '" class="btn btn-info btn-sm" title="Xem chi tiết">';
                    $btn .= '<i class="fas fa-eye"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['tax_info', 'total_amount_after_tax_formatted', 'action'])
                ->make(true);
        }

        return view('admin.securities.dividend-record.unpaid');
    }

    /**
     * Display detailed records for a specific transfer date (paid records)
     */
    public function paidDetail(Request $request, $transferDate)
    {
        if ($request->ajax()) {
            $records = DividendRecord::with('securitiesManagement')
                ->where('transfer_date', $transferDate)
                ->where('payment_status', 'paid')
                ->orderBy('created_at', 'desc');

            return DataTables::of($records)
                ->addIndexColumn()
                ->addColumn('investor_name', function ($row) {
                    return $row->securitiesManagement->full_name ?? 'N/A';
                })
                ->addColumn('investor_code', function ($row) {
                    return $row->securitiesManagement->investor_code ?? 'N/A';
                })
                ->addColumn('total_shares', function ($row) {
                    return number_format(($row->non_deposited_shares_quantity ?? 0) + ($row->deposited_shares_quantity ?? 0));
                })
                ->addColumn('deposited_shares', function ($row) {
                    return number_format($row->deposited_shares_quantity ?? 0);
                })
                ->addColumn('non_deposited_shares', function ($row) {
                    return number_format($row->non_deposited_shares_quantity ?? 0);
                })
                ->addColumn('deposited_amount', function ($row) {
                    return number_format($row->deposited_amount_before_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('non_deposited_amount', function ($row) {
                    return number_format($row->non_deposited_amount_before_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('deposited_tax', function ($row) {
                    return number_format($row->deposited_personal_income_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('non_deposited_tax', function ($row) {
                    return number_format($row->non_deposited_personal_income_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('dividend_price', function ($row) {
                    return number_format($row->dividend_price_per_share ?? 0, 0, ',', '.');
                })
                ->addColumn('total_amount_after_tax', function ($row) {
                    $depositedAfterTax = ($row->deposited_amount_before_tax ?? 0) - ($row->deposited_personal_income_tax ?? 0);
                    $nonDepositedAfterTax = ($row->non_deposited_amount_before_tax ?? 0) - ($row->non_deposited_personal_income_tax ?? 0);
                    $totalAfterTax = $depositedAfterTax + $nonDepositedAfterTax;
                    return number_format($totalAfterTax, 0, ',', '.');
                })
                ->addColumn('payment_status', function ($row) {
                    $status = $row->payment_status ?? 'unpaid';
                    $badge = $status === 'paid' ? 'badge-success' : 'badge-danger';
                    $text = $status === 'paid' ? 'Đã trả' : 'Chưa trả';
                    return '<span class="badge ' . $badge . '">' . $text . '</span>';
                })
                ->rawColumns(['payment_status'])
                ->make(true);
        }

        $transferDateFormatted = date('d/m/Y', strtotime($transferDate));
        return view('admin.securities.dividend-record.paid-detail', [
            'transferDate' => $transferDate,
            'transferDateFormatted' => $transferDateFormatted
        ]);
    }

    /**
     * Display detailed records for a specific investor (unpaid records)
     */
    public function unpaidDetail(Request $request, $investorId)
    {
        if ($request->ajax()) {
            $records = DividendRecord::with('securitiesManagement')
                ->where('securities_management_id', $investorId)
                ->where(function ($query) {
                    $query->where('payment_status', 'unpaid')
                          ->orWhereNull('payment_status');
                })
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc');

            return DataTables::of($records)
                ->addIndexColumn()
                ->addColumn('payment_date_formatted', function ($row) {
                    return $row->payment_date ? date('d/m/Y', strtotime($row->payment_date)) : 'N/A';
                })
                ->addColumn('investor_name', function ($row) {
                    return $row->securitiesManagement->full_name ?? 'N/A';
                })
                ->addColumn('investor_code', function ($row) {
                    return $row->securitiesManagement->investor_code ?? 'N/A';
                })
                ->addColumn('total_shares', function ($row) {
                    return number_format(($row->non_deposited_shares_quantity ?? 0) + ($row->deposited_shares_quantity ?? 0));
                })
                ->addColumn('deposited_shares', function ($row) {
                    return number_format($row->deposited_shares_quantity ?? 0);
                })
                ->addColumn('non_deposited_shares', function ($row) {
                    return number_format($row->non_deposited_shares_quantity ?? 0);
                })
                ->addColumn('deposited_amount', function ($row) {
                    return number_format($row->deposited_amount_before_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('non_deposited_amount', function ($row) {
                    return number_format($row->non_deposited_amount_before_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('deposited_tax', function ($row) {
                    return number_format($row->deposited_personal_income_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('non_deposited_tax', function ($row) {
                    return number_format($row->non_deposited_personal_income_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('dividend_price', function ($row) {
                    return number_format($row->dividend_price_per_share ?? 0, 0, ',', '.');
                })
                ->addColumn('total_amount_after_tax', function ($row) {
                    $depositedAfterTax = ($row->deposited_amount_before_tax ?? 0) - ($row->deposited_personal_income_tax ?? 0);
                    $nonDepositedAfterTax = ($row->non_deposited_amount_before_tax ?? 0) - ($row->non_deposited_personal_income_tax ?? 0);
                    $totalAfterTax = $depositedAfterTax + $nonDepositedAfterTax;
                    return number_format($totalAfterTax, 0, ',', '.');
                })
                ->addColumn('payment_status', function ($row) {
                    $status = $row->payment_status ?? 'unpaid';
                    $badge = $status === 'paid' ? 'badge-success' : 'badge-danger';
                    $text = $status === 'paid' ? 'Đã trả' : 'Chưa trả';
                    return '<span class="badge ' . $badge . '">' . $text . '</span>';
                })
                ->rawColumns(['payment_status'])
                ->make(true);
        }

        $investor = \App\Models\SecuritiesManagement::find($investorId);
        return view('admin.securities.dividend-record.unpaid-detail', [
            'investorId' => $investorId,
            'investorName' => $investor->full_name ?? 'N/A'
        ]);
    }

    /**
     * Display detailed records for a specific payment date
     */
    public function detail(Request $request, $paymentDate)
    {
        if ($request->ajax()) {
            $records = DividendRecord::with('securitiesManagement')
                ->where('payment_date', $paymentDate)
                ->orderBy('created_at', 'desc');

            return DataTables::of($records)
                ->addIndexColumn()
                ->addColumn('investor_name', function ($row) {
                    return $row->securitiesManagement->full_name ?? 'N/A';
                })
                ->addColumn('investor_code', function ($row) {
                    return $row->securitiesManagement->investor_code ?? 'N/A';
                })
                ->addColumn('total_shares', function ($row) {
                    return number_format(($row->non_deposited_shares_quantity ?? 0) + ($row->deposited_shares_quantity ?? 0));
                })
                ->addColumn('deposited_shares', function ($row) {
                    return number_format($row->deposited_shares_quantity ?? 0);
                })
                ->addColumn('non_deposited_shares', function ($row) {
                    return number_format($row->non_deposited_shares_quantity ?? 0);
                })
                ->addColumn('deposited_amount', function ($row) {
                    return number_format($row->deposited_amount_before_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('non_deposited_amount', function ($row) {
                    return number_format($row->non_deposited_amount_before_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('deposited_tax', function ($row) {
                    return number_format($row->deposited_personal_income_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('non_deposited_tax', function ($row) {
                    return number_format($row->non_deposited_personal_income_tax ?? 0, 0, ',', '.');
                })
                ->addColumn('dividend_price', function ($row) {
                    return number_format($row->dividend_price_per_share ?? 0, 0, ',', '.');
                })
                ->addColumn('payment_status', function ($row) {
                    $status = $row->payment_status ?? 'unpaid';
                    $badge = $status === 'paid' ? 'badge-success' : 'badge-danger';
                    $text = $status === 'paid' ? 'Đã trả' : 'Chưa trả';
                    return '<span class="badge ' . $badge . '">' . $text . '</span>';
                })
                ->rawColumns(['payment_status'])
                ->make(true);
        }

        $paymentDateFormatted = date('d/m/Y', strtotime($paymentDate));
        return view('admin.securities.dividend-record.detail', [
            'paymentDate' => $paymentDate,
            'paymentDateFormatted' => $paymentDateFormatted
        ]);
    }

    /**
     * Delete records by payment date
     */
    public function destroy($paymentDate)
    {
        try {
            DividendRecord::where('payment_date', $paymentDate)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa dữ liệu cổ tức thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete dividend records error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa dữ liệu cổ tức!'
            ], 422);
        }
    }

    /**
     * Export dividend records to Excel
     */
    public function export(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            
            $fileName = 'danh-sach-co-dong-nam-' . $year . '-' . Carbon::now()->format('dmY') . '.xlsx';
            
            return Excel::download(
                new DividendRecordExport($year),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Export dividend records error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất file!'
            ], 422);
        }
    }
}
