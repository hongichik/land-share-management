@extends('layouts.layout-master')

@section('title', 'Sửa Setting')
@section('page_title', 'Sửa Setting')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sửa Setting</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.settings.update', $setting) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="title_excel">Title Excel</label>
                        <input type="text" class="form-control @error('title_excel') is-invalid @enderror" id="title_excel" name="title_excel" value="{{ old('title_excel', $setting->title_excel) }}" required>
                        @error('title_excel')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="des">Description</label>
                        <textarea class="form-control @error('des') is-invalid @enderror" id="des" name="des" rows="3">{{ old('des', $setting->des) }}</textarea>
                        @error('des')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật Setting
                    </button>
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection