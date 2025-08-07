<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DividendRecord;
use App\Models\SecuritiesManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DividendPaymentController extends Controller
{
    /**
     * Show the form for creating a new dividend payment.
     */
    public function create()
    {
        $investors = SecuritiesManagement::where('status', 1)
            ->select('id', 'full_name', 'investor_code', 'registration_number', 'deposited_quantity', 'not_deposited_quantity')
            ->get();
            
        return view('admin.dividend-payment.create', compact('investors'));
    }

    /**
     * Get investor details for AJAX requests.
     */
    public function getInvestorDetails(Request $request)
    {
        $investorIds = $request->input('investor_ids', []);
        
        // Check if we should use account_number instead of bank_account
        $investors = SecuritiesManagement::whereIn('id', $investorIds)
            ->select('id', 'full_name', 'investor_code', 'registration_number', 
                    'deposited_quantity', 'not_deposited_quantity', 
                    'account_number as bank_account', 'bank_name')
            ->get();
            
        return response()->json($investors);
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
                
                return redirect()->route('admin.dividend-history.index')
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
}
