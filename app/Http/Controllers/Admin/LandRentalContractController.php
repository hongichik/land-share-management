<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandRentalContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class LandRentalContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $contracts = LandRentalContract::select([
                'id', 'contract_number', 'rental_zone', 'rental_location', 'rental_decision',
                'contract_file_path', 'rental_decision_file_path', 'export_tax', 'created_at'
            ]);
            
            return DataTables::of($contracts)
                ->addIndexColumn()
                ->editColumn('contract_number', function($item) {
                    return '<strong>' . $item->contract_number . '</strong>';
                })
                ->editColumn('rental_zone', function($item) {
                    return $item->rental_zone ?: 'Chưa có thông tin';
                })
                ->editColumn('rental_location', function($item) {
                    return $item->rental_location ?: 'Chưa có thông tin';
                })
                ->editColumn('export_tax', function($item) {
                    return number_format($item->export_tax * 100, 2) . '%';
                })
                ->editColumn('contract_file_path', function($item) {
                    if ($item->contract_file_path) {
                        $url = asset('storage/' . str_replace('public/', '', $item->contract_file_path));
                        return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-info">Xem file</a>';
                    }
                    return 'Chưa có file';
                })
                ->editColumn('created_at', function($item) {
                    return $item->created_at->format('d/m/Y H:i');
                })
                ->addColumn('action', function($item) {
                    $showBtn = '<a href="' . route('admin.land-rental-contracts.show', $item) . '" class="btn btn-info btn-sm" title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                    </a>';
                    $editBtn = '<a href="' . route('admin.land-rental-contracts.edit', $item) . '" class="btn btn-warning btn-sm" title="Sửa">
                        <i class="fas fa-edit"></i>
                    </a>';
                    $deleteBtn = '<form action="' . route('admin.land-rental-contracts.destroy', $item) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Bạn có chắc chắn muốn xóa hợp đồng này?\')">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>';
                    return '<div class="btn-group" role="group">' . $showBtn . ' ' . $editBtn . ' ' . $deleteBtn . '</div>';
                })
                ->rawColumns(['contract_number', 'contract_file_path', 'action'])
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
