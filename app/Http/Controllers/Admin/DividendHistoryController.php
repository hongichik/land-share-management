<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DividendRecord;
use App\Models\SecuritiesManagement;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class DividendHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dividendRecords = DividendRecord::with('securitiesManagement')
                ->select('dividend_records.*');
                
            // Apply year filter if provided
            if ($request->has('year') && $request->year) {
                $dividendRecords->whereYear('payment_date', $request->year);
            }
            
            // Apply investor filter if provided
            if ($request->has('investor_id') && $request->investor_id) {
                $dividendRecords->where('securities_management_id', $request->investor_id);
            }

            $currentYear = date('Y');

            return DataTables::of($dividendRecords)
                ->addIndexColumn()
                ->addColumn('investor_name', function ($row) {
                    return $row->securitiesManagement->full_name ?? 'N/A';
                })
                ->addColumn('total_shares_quantity', function ($row) {
                    return number_format($row->deposited_shares_quantity + $row->non_deposited_shares_quantity);
                })
                ->addColumn('total_amount', function ($row) {
                    return number_format($row->deposited_amount_before_tax + $row->non_deposited_amount_before_tax, 0, ',', '.');
                })
                ->addColumn('tax_amount', function ($row) {
                    $taxAmount = ($row->deposited_amount_before_tax + $row->non_deposited_amount_before_tax) * $row->tax_rate;
                    return number_format($taxAmount, 0, ',', '.');
                })
                ->addColumn('net_amount', function ($row) {
                    $beforeTax = $row->deposited_amount_before_tax + $row->non_deposited_amount_before_tax;
                    $afterTax = $beforeTax * (1 - $row->tax_rate);
                    return number_format($afterTax, 0, ',', '.');
                })
                ->addColumn('action', function ($row) use ($currentYear) {
                    $btn = '<div class="btn-group" role="group">';
                    
                    // Only show delete button for current year records
                    $paymentYear = date('Y', strtotime($row->payment_date));
                    if ($paymentYear == $currentYear) {
                        $btn .= '<button type="button" class="btn btn-danger btn-sm" title="Xóa" onclick="deleteRecord(' . $row->id . ')">';
                        $btn .= '<i class="fas fa-trash"></i></button>';
                    }
                    
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.securities.history.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(DividendRecord $dividendHistory)
    {
        $dividendHistory->load('securitiesManagement');
        return view('admin.securities.history.show', compact('dividendHistory'));
    }

    /**
     * Get investor dividend histories.
     */
    public function getInvestorDividendHistories(SecuritiesManagement $securitiesManagement)
    {
        $dividendRecords = $securitiesManagement->dividendRecords()
            ->orderBy('payment_date', 'desc')
            ->get();
            
        return view('admin.securities.management.dividend-histories', compact('securitiesManagement', 'dividendRecords'));
    }

    /**
     * Get investor details for AJAX requests.
     */
    public function getInvestorDetails(Request $request)
    {
        $investorId = $request->input('investor_id');
        
        if ($request->has('investor_ids')) {
            // For dividend payment creation (multiple investors)
            $investorIds = $request->input('investor_ids', []);
            
            $investors = SecuritiesManagement::whereIn('id', $investorIds)
                ->select('id', 'full_name', 'investor_code', 'registration_number', 
                        'deposited_quantity', 'not_deposited_quantity', 
                        'account_number as bank_account', 'bank_name')
                ->get();
                
            return response()->json($investors);
        } else {
            // For single investor details
            $investor = SecuritiesManagement::findOrFail($investorId);
            return response()->json($investor);
        }
    }

    /**
     * Show the form for creating a new dividend history.
     */
    public function create()
    {
        // Get current year for filtering
        $currentYear = date('Y');
        
        // Get investor IDs who already have payments in current year
        $paidInvestorIds = DividendRecord::whereYear('payment_date', $currentYear)
            ->pluck('securities_management_id')
            ->toArray();
        
        // Get active investors who haven't been paid in current year
        $investors = SecuritiesManagement::where('status', 1)
            ->whereNotIn('id', $paidInvestorIds)
            ->select('id', 'full_name', 'investor_code', 'registration_number', 'deposited_quantity', 'not_deposited_quantity')
            ->get();
        
        // Add information about current year to the view
        return view('admin.securities.history.create', compact('investors', 'currentYear'));
    }

    /**
     * Store a newly created dividend payment in storage.
     */
    public function store(Request $request)
    {
        try {
            \Log::info('Dividend payment store called', ['request' => $request->all()]);
            
            $request->validate([
                'investor_ids' => 'required|array',
                'investor_ids.*' => 'exists:securities_management,id',
                'tax_rate' => 'required|numeric|min:0|max:1',
                'dividend_per_share' => 'required|numeric|min:0',
                'payment_date' => 'required|date',
                'payment_type' => 'required|string|in:deposited,not_deposited,both',
                'notes' => 'nullable|string'
            ]);

            // Validate that investors don't already have a payment in the same year
            $paymentYear = date('Y', strtotime($request->input('payment_date')));
            $investorIds = $request->input('investor_ids', []);
            
            // Find any existing payments for these investors in the same year
            $existingPayments = DividendRecord::whereIn('securities_management_id', $investorIds)
                ->whereYear('payment_date', $paymentYear)
                ->get();
                
            if ($existingPayments->count() > 0) {
                // Group by investor for clear error reporting
                $investorsWithPayments = [];
                foreach ($existingPayments as $payment) {
                    $investorName = $payment->securitiesManagement->full_name ?? 'Nhà đầu tư #' . $payment->securities_management_id;
                    $paymentDate = date('d/m/Y', strtotime($payment->payment_date));
                    $investorsWithPayments[] = "{$investorName} (đã thanh toán ngày {$paymentDate})";
                }
                
                return back()
                    ->withErrors(['payment_date' => 'Các nhà đầu tư sau đã nhận cổ tức trong năm ' . $paymentYear . ': ' . implode(', ', $investorsWithPayments)])
                    ->withInput();
            }

            \Log::info('Validation passed');
            DB::beginTransaction();
            
            try {
                $taxRate = $request->input('tax_rate');
                $dividendPerShare = $request->input('dividend_per_share');
                $paymentDate = $request->input('payment_date');
                $paymentType = $request->input('payment_type');
                $notes = $request->input('notes');
                
                $records = [];
                
                foreach ($request->input('investor_ids') as $investorId) {
                    $investor = SecuritiesManagement::findOrFail($investorId);
                    \Log::info('Processing investor', ['id' => $investorId, 'investor' => $investor->toArray()]);
                    
                    $depositedSharesQuantity = ($paymentType == 'deposited' || $paymentType == 'both') ? $investor->deposited_quantity : 0;
                    $nonDepositedSharesQuantity = ($paymentType == 'not_deposited' || $paymentType == 'both') ? $investor->not_deposited_quantity : 0;
                    
                    $depositedAmountBeforeTax = $depositedSharesQuantity * $dividendPerShare;
                    $nonDepositedAmountBeforeTax = $nonDepositedSharesQuantity * $dividendPerShare;
                    
                    // Skip if no shares to pay dividends on
                    if ($depositedAmountBeforeTax == 0 && $nonDepositedAmountBeforeTax == 0) {
                        continue;
                    }
                    
                    \Log::info('Creating dividend record', [
                        'investor_id' => $investorId,
                        'deposited_shares' => $depositedSharesQuantity,
                        'non_deposited_shares' => $nonDepositedSharesQuantity,
                        'deposited_amount' => $depositedAmountBeforeTax,
                        'non_deposited_amount' => $nonDepositedAmountBeforeTax
                    ]);
                    
                    $record = DividendRecord::create([
                        'securities_management_id' => $investorId,
                        'tax_rate' => $taxRate,
                        'deposited_shares_quantity' => $depositedSharesQuantity,
                        'deposited_amount_before_tax' => $depositedAmountBeforeTax,
                        'non_deposited_shares_quantity' => $nonDepositedSharesQuantity,
                        'non_deposited_amount_before_tax' => $nonDepositedAmountBeforeTax,
                        'payment_date' => $paymentDate,
                        'account_number' => $investor->account_number ?? $investor->bank_account, // Try both fields
                        'bank_name' => $investor->bank_name,
                        'notes' => $notes
                    ]);
                    
                    \Log::info('Dividend record created', ['record_id' => $record->id]);
                    $records[] = $record;
                }
                
                DB::commit();
                \Log::info('Transaction committed', ['records_count' => count($records)]);
                
                return redirect()->route('admin.securities.history.index')
                    ->with('success', 'Đã tạo thanh toán cổ tức thành công cho ' . count($records) . ' nhà đầu tư!');
                    
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error in transaction', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return back()->withErrors(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()])->withInput();
            }
        } catch (\Exception $e) {
            \Log::error('Error in validation', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Check if investors already have payments in a given year
     */
    public function checkExistingPayments(Request $request)
    {
        $year = $request->input('year');
        $investorIds = $request->input('investor_ids', []);
        
        if (empty($year) || empty($investorIds)) {
            return response()->json(['has_existing_payments' => false, 'investors' => []]);
        }
        
        $existingPayments = DividendRecord::whereIn('securities_management_id', $investorIds)
            ->whereYear('payment_date', $year)
            ->with('securitiesManagement:id,full_name')
            ->get();
        
        $investorsWithPayments = [];
        foreach ($existingPayments as $payment) {
            $investorsWithPayments[] = [
                'id' => $payment->securities_management_id,
                'name' => $payment->securitiesManagement->full_name ?? 'Nhà đầu tư #' . $payment->securities_management_id,
                'payment_date' => date('d/m/Y', strtotime($payment->payment_date))
            ];
        }
        
        return response()->json([
            'has_existing_payments' => count($investorsWithPayments) > 0,
            'investors' => $investorsWithPayments
        ]);
    }

    /**
     * Get all investors including those who already received dividends
     */
    public function getAllInvestors()
    {
        $currentYear = date('Y');
        
        // Get all active investors
        $investors = SecuritiesManagement::where('status', 1)
            ->select('id', 'full_name', 'investor_code', 'registration_number', 
                    'deposited_quantity', 'not_deposited_quantity')
            ->get();
        
        // Get IDs of investors who already have payments this year
        $paidInvestorIds = DividendRecord::whereYear('payment_date', $currentYear)
            ->pluck('securities_management_id')
            ->toArray();
        
        // Add payment status to each investor
        foreach ($investors as $investor) {
            $investor->has_payment = in_array($investor->id, $paidInvestorIds);
        }
        
        return response()->json(['investors' => $investors]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $record = DividendRecord::findOrFail($id);
            
            // Only allow deletion of current year records
            $currentYear = date('Y');
            $paymentYear = date('Y', strtotime($record->payment_date));
            
            if ($paymentYear != $currentYear) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể xóa thanh toán cổ tức của năm hiện tại!'
                ], 403);
            }
            
            $record->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa thanh toán cổ tức thành công!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting dividend record', ['id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa thanh toán cổ tức!'
            ], 500);
        }
    }
}
