@extends('layouts.layout-master')

@section('title', 'Sửa StorageAI')
@section('page_title', 'Sửa StorageAI')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sửa StorageAI</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.config-ai.storage-ai.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.config-ai.storage-ai.update', $storageAI) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name_ai">Tên AI <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name_ai') is-invalid @enderror" id="name_ai" name="name_ai" value="{{ old('name_ai', $storageAI->name_ai) }}" required>
                                @error('name_ai')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slug_ai">Slug AI <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('slug_ai') is-invalid @enderror" id="slug_ai" name="slug_ai" value="{{ old('slug_ai', $storageAI->slug_ai) }}" required>
                                @error('slug_ai')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="des">Mô tả</label>
                        <textarea class="form-control @error('des') is-invalid @enderror" id="des" name="des" rows="3">{{ old('des', $storageAI->des) }}</textarea>
                        @error('des')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data">Tệp dữ liệu</label>
                                @if($storageAI->data)
                                    <div class="mb-2">
                                        <a href="{{ asset('storage/' . $storageAI->data) }}" target="_blank" class="btn btn-info btn-sm">
                                            <i class="fas fa-file"></i> Xem/Tải file hiện tại
                                        </a>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('data') is-invalid @enderror" id="data" name="data" accept=".md,.txt,.pdf">
                                @error('data')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_storage">ID Storage <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('id_storage') is-invalid @enderror" id="id_storage" name="id_storage" value="{{ old('id_storage', $storageAI->id_storage) }}" readonly>
                                @error('id_storage')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu StorageAI
                    </button>
                    <a href="{{ route('admin.config-ai.storage-ai.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function slugify(str) {
    return str
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

$(document).ready(function() {
    $('#name_ai').on('input', function() {
        var slug = slugify($(this).val());
        $('#slug_ai').val(slug);
    });
});
</script>
@endpush
