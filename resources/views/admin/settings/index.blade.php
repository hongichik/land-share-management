@extends('layouts.layout-master')

@section('title', 'Quản lý Setting bảng Chứng khoán')
@section('page_title', 'Quản lý Setting bảng Chứng khoán')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách Setting cho bảng securities_management</h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <form action="{{ route('admin.settings.index') }}" method="POST">
                    @csrf
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>column_name</th>
                                <th>title_excel</th>
                                <th>des</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settings as $i => $setting)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $setting->column_name }}</td>
                                <td>
                                    <input type="text" name="settings[{{ $setting->id }}][title_excel]" value="{{ $setting->title_excel }}" class="form-control" />
                                </td>
                                <td>
                                    <input type="text" name="settings[{{ $setting->id }}][des]" value="{{ $setting->des }}" class="form-control" />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu tất cả
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection