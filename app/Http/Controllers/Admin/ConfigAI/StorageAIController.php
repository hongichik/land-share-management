<?php

namespace App\Http\Controllers\Admin\ConfigAI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StorageAI;
use Illuminate\Support\Facades\Storage;

class StorageAIController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $storageAIs = StorageAI::select(['id', 'name_ai', 'slug_ai', 'des', 'data', 'id_storage', 'created_at']);
            return \Yajra\DataTables\DataTables::of($storageAIs)
                ->addIndexColumn()
                ->editColumn('name_ai', function($item) {
                    return '<strong>' . $item->name_ai . '</strong><br><small class="text-muted">' . $item->slug_ai . '</small>';
                })
                ->editColumn('des', function($item) {
                    return $item->des ?? 'Chưa có mô tả';
                })
                ->editColumn('data', function($item) {
                    if ($item->data) {
                        $url = asset('storage/' . $item->data);
                        return '<a href="' . $url . '" target="_blank">Tải file</a>';
                    }
                    return 'Chưa có file';
                })
                ->editColumn('created_at', function($item) {
                    return $item->created_at->format('d/m/Y H:i');
                })
                ->addColumn('action', function($item) {
                    $editBtn = '<a href="' . route('admin.config-ai.storage-ai.edit', $item) . '" class="btn btn-warning btn-xs"><i class="bi bi-pencil"></i></a>';
                    $updateFileBtn = '<a href="' . route('admin.config-ai.storage-ai.upload-file', $item) . '" class="btn btn-info btn-xs" title="Cập nhật file"><i class="bi bi-arrow-repeat"></i></a>';
                    $deleteBtn = '<form action="' . route('admin.config-ai.storage-ai.destroy', $item) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Bạn có chắc chắn muốn xóa?\')">'
                        . csrf_field() . method_field('DELETE') .
                        '<button type="submit" class="btn btn-danger btn-xs"><i class="bi bi-trash"></i></button>' .
                    '</form>';
                    return '<div class="btn-group">' . $editBtn . ' ' . $updateFileBtn . ' ' . $deleteBtn . '</div>';
                })
                ->rawColumns(['name_ai', 'des', 'data', 'action'])
                ->make(true);
        }
        return view('admin.config-ai.storage-ai.index');
    }

    public function create()
    {
        return view('admin.config-ai.storage-ai.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ai' => 'required|string|max:255|unique:storage_a_i_s,name_ai',
            'slug_ai' => 'required|string|max:255|unique:storage_a_i_s,slug_ai',
            'des' => 'nullable|string',
            'data' => 'nullable|file|mimes:md,txt,pdf|max:10240',
            'id_storage' => 'nullable|string|max:255',
        ]);

        $dataPath = null;
        if ($request->hasFile('data')) {
            $file = $request->file('data');
            $filename = uniqid('storage_ai_') . '.' . $file->getClientOriginalExtension();
            $dataPath = $file->storeAs('storage_ai', $filename);
        }

        StorageAI::create([
            'name_ai' => $request->name_ai,
            'slug_ai' => $request->slug_ai,
            'des' => $request->des,
            'data' => $dataPath,
            'id_storage' => $request->id_storage,
        ]);
        return redirect()->route('admin.config-ai.storage-ai.index')->with('success', 'Thêm mới thành công!');
    }

    public function edit(StorageAI $storageAI)
    {
        return view('admin.config-ai.storage-ai.edit', compact('storageAI'));
    }

    public function update(Request $request, StorageAI $storageAI)
    {
        $request->validate([
            'name_ai' => 'required|string|max:255|unique:storage_a_i_s,name_ai,' . $storageAI->id,
            'slug_ai' => 'required|string|max:255|unique:storage_a_i_s,slug_ai,' . $storageAI->id,
            'des' => 'nullable|string',
            'data' => 'nullable|file|mimes:md,txt,pdf|max:10240',
            'id_storage' => 'nullable|string|max:255',
        ]);

        $dataPath = $storageAI->data;
        if ($request->hasFile('data')) {
            // Xoá file cũ nếu có
            if ($dataPath && Storage::exists($dataPath)) {
                Storage::delete($dataPath);
            }
            $file = $request->file('data');
            $filename = uniqid('storage_ai_') . '.' . $file->getClientOriginalExtension();
            $dataPath = $file->storeAs('storage_ai', $filename);
        }

        $storageAI->update([
            'name_ai' => $request->name_ai,
            'slug_ai' => $request->slug_ai,
            'des' => $request->des,
            'data' => $dataPath,
            'id_storage' => $request->id_storage,
        ]);
        return redirect()->route('admin.config-ai.storage-ai.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy(StorageAI $storageAI)
    {
        if ($storageAI->data && Storage::exists($storageAI->data)) {
            Storage::delete($storageAI->data);
        }
        $storageAI->delete();
        return redirect()->route('admin.config-ai.storage-ai.index')->with('success', 'Xoá thành công!');
    }

    public function uploadFile() {
     return redirect()->route('admin.config-ai.storage-ai.index')->with('info', 'Chức năng upload file chưa được triển khai.');
    }
}
