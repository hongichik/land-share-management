<?php

namespace App\Http\Controllers\Admin\Securities;

use App\Http\Controllers\Controller;
use App\Models\DividendRecord;
use App\Exports\DividendRecordExport;
use App\Exports\DividendRecordPaymentDetailExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class DividendRecordPaymentController extends Controller
{
    /**
     * Display a listing of the dividend records grouped by transfer date
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Get filter parameters
            $year = $request->input('year');
            $month = $request->input('month');
            $day = $request->input('day');
            
            // Lấy dữ liệu grouped by transfer_date - hiển thị 1 dòng cho mỗi ngày
            $dividendRecords = DividendRecord::selectRaw('
                transfer_date,
                COUNT(DISTINCT dividend_id) as investor_count,
                SUM(COALESCE(non_deposited_shares_quantity, 0)) as total_shares,
                dividend_percentage,
                SUM(COALESCE(non_deposited_personal_income_tax, 0)) as total_tax,
                SUM(COALESCE(non_deposited_amount_before_tax, 0)) as total_amount_before_tax_tmp,
                MAX(created_at) as created_at
            ')
                ->whereIn('payment_status', ['paid_not_deposited', 'paid_both'])
                ->whereNotNull('transfer_date');
            
            // Apply year filter
            if ($year) {
                $dividendRecords->whereYear('transfer_date', $year);
            }
            
            // Apply month filter
            if ($month && $year) {
                $dividendRecords->whereMonth('transfer_date', $month);
            }
            
            // Apply day filter
            if ($day && $month && $year) {
                $dividendRecords->whereDay('transfer_date', $day);
            }
            
            $dividendRecords = $dividendRecords
                ->groupBy('transfer_date', 'dividend_percentage')
                ->orderBy('transfer_date', 'desc')
                ->get();

            return DataTables::of(collect($dividendRecords))
                ->addIndexColumn()
                ->addColumn('payment_date_formatted', function ($row) {
                    return $row->transfer_date ? date('d/m/Y', strtotime($row->transfer_date)) : 'N/A';
                })
                ->addColumn('total_shares_formatted', function ($row) {
                    return number_format($row->total_shares ?? 0);
                })
                ->addColumn('dividend_percentage_formatted', function ($row) {
                    return ($row->dividend_percentage ?? 0) . '%';
                })
                ->addColumn('total_amount_before_tax', function ($row) {
                    $amount = $row->total_amount_before_tax_tmp ?? 0;

                    return number_format($amount, 0, ',', '.') . ' đ';
                })
                ->addColumn('tax_info', function ($row) {
                    $tax = $row->total_tax ?? 0;

                    return number_format($tax, 0, ',', '.') . ' đ';
                })
                ->addColumn('investor_count_formatted', function ($row) {
                    return $row->investor_count ?? 0;
                })
                ->addColumn('action', function ($row) {
                    $transferDateFormatted = $row->transfer_date ? date('Y-m-d', strtotime($row->transfer_date)) : '';
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('admin.securities.dividend-record-payment.detail', ['paymentDate' => $transferDateFormatted]) . '" class="btn btn-info btn-sm" title="Xem chi tiết">';
                    $btn .= '<i class="fas fa-eye"></i></a>';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRecord(\'' . $transferDateFormatted . '\')" title="Xóa">';
                    $btn .= '<i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.securities.dividend-record-payment.index');
    }

    /**
     * Display detailed records for a specific transfer date
     */
    public function detail(Request $request, $paymentDate)
    {
        if ($request->ajax()) {
            $records = DividendRecord::with('dividend')
                ->where('transfer_date', $paymentDate)
                ->whereIn('payment_status', ['paid_not_deposited','paid_both'])
                ->orderBy('created_at', 'desc');

            return DataTables::of($records)
                ->addIndexColumn()
                ->addColumn('investor_name', function ($row) {
                    return $row->dividend->full_name ?? 'N/A';
                })
                ->addColumn('investor_code', function ($row) {
                    return $row->dividend->investor_code ?? 'N/A';
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
                    $statusMap = [
                        'unpaid' => ['badge' => 'badge-danger', 'text' => 'Chưa trả'],
                        'paid_not_deposited' => ['badge' => 'badge-warning', 'text' => 'Đã trả (Chưa LK)'],
                        'paid_deposited' => ['badge' => 'badge-info', 'text' => 'Đã trả (Đã LK)'],
                        'paid_both' => ['badge' => 'badge-success', 'text' => 'Đã trả (Cả 2)']
                    ];
                    $statusInfo = $statusMap[$status] ?? ['badge' => 'badge-secondary', 'text' => 'Không xác định'];
                    return '<span class="badge ' . $statusInfo['badge'] . '">' . $statusInfo['text'] . '</span>';
                })
                ->rawColumns(['payment_status'])
                ->make(true);
        }

        $paymentDateFormatted = date('d/m/Y', strtotime($paymentDate));
        return view('admin.securities.dividend-record-payment.detail', [
            'paymentDate' => $paymentDate,
            'paymentDateFormatted' => $paymentDateFormatted
        ]);
    }

    /**
     * Delete records by transfer date
     */
    public function destroy($paymentDate)
    {
        try {
            // Lấy các records cần xóa transfer_date
            $records = DividendRecord::where('transfer_date', $paymentDate)->get();
            
            foreach ($records as $record) {
                // Xác định trạng thái mới dựa trên deposited_shares_quantity
                if ($record->deposited_shares_quantity > 0) {
                    $newStatus = 'paid_deposited';
                } else {
                    $newStatus = 'unpaid';
                }
                
                // Cập nhật record: xóa transfer_date và cập nhật trạng thái
                $record->update([
                    'transfer_date' => null,
                    'payment_status' => $newStatus
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Xóa dữ liệu thanh toán thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete dividend records error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa dữ liệu thanh toán!'
            ], 422);
        }
    }

    /**
     * Export dividend records to Excel
     */
    public function export(Request $request)
    {
        try {
            $year = $request->input('year');
            
            // Kiểm tra xem có dữ liệu không trước khi export
            $recordCount = DividendRecord::whereIn('payment_status', ['paid_not_deposited', 'paid_both'])
                ->whereYear('transfer_date', $year)
                ->count();
            
            if ($recordCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có dữ liệu thanh toán cổ tức trong năm ' . $year . '!'
                ], 422);
            }
            
            $fileName = 'danh-sach-thanh-toan-' . date('dmY', strtotime($year)) . '-' . Carbon::now()->format('dmY') . '.xlsx';

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

    /**
     * Export dividend payment detail records to Excel by transfer date
     */
    public function exportDetail(Request $request, $transferDate)
    {
        try {
            // Kiểm tra xem có dữ liệu không trước khi export
            $recordCount = DividendRecord::where('transfer_date', $transferDate)
                ->whereIn('payment_status', ['paid_not_deposited', 'paid_both'])
                ->count();
            
            if ($recordCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có dữ liệu thanh toán cổ tức cho ngày ' . date('d/m/Y', strtotime($transferDate)) . '!'
                ], 422);
            }
            
            $fileName = 'chi-tiet-thanh-toan-' . date('dmY', strtotime($transferDate)) . '-' . Carbon::now()->format('dmY') . '.xlsx';

            return Excel::download(
                new DividendRecordPaymentDetailExport($transferDate),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Export dividend payment detail error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất file!'
            ], 422);
        }
    }
}
