<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DividendRecord;
use App\Models\SecuritiesManagement;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

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
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('admin.dividend-history.show', $row->id) . '" class="btn btn-info btn-sm" title="Xem chi tiết">';
                    $btn .= '<i class="fas fa-eye"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.dividend-history.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(DividendRecord $dividendHistory)
    {
        $dividendHistory->load('securitiesManagement');
        return view('admin.dividend-history.show', compact('dividendHistory'));
    }

    /**
     * Get investor dividend histories.
     */
    public function getInvestorDividendHistories(SecuritiesManagement $securitiesManagement)
    {
        $dividendRecords = $securitiesManagement->dividendRecords()
            ->orderBy('payment_date', 'desc')
            ->get();
            
        return view('admin.securities-management.dividend-histories', compact('securitiesManagement', 'dividendRecords'));
    }

    /**
     * Get investor details for AJAX requests.
     */
    public function getInvestorDetails(Request $request)
    {
        $investorId = $request->input('investor_id');
        $investor = SecuritiesManagement::findOrFail($investorId);
        
        return response()->json($investor);
    }
}
