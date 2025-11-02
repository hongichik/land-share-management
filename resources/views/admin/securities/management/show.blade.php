@extends('layouts.layout-master')

@section('title', 'Chi tiết nhà đầu tư')
@section('page_title', 'Chi tiết nhà đầu tư')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Chi tiết nhà đầu tư: {{ $securitiesManagement->full_name }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities.management.edit', $securitiesManagement) }}" 
                       class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    <a href="{{ route('admin.securities.management.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Thông tin cơ bản -->
                    <div class="col-md-6">
                        <h5><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Tên đầy đủ:</strong></td>
                                <td>{{ $securitiesManagement->full_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>SID:</strong></td>
                                <td>{{ $securitiesManagement->sid ?: 'Chưa có thông tin' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Mã nhà đầu tư:</strong></td>
                                <td>{{ $securitiesManagement->investor_code ?: 'Chưa có thông tin' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Số đăng ký:</strong></td>
                                <td>{{ $securitiesManagement->registration_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Ngày phát hành:</strong></td>
                                <td>
                                    @if($securitiesManagement->issue_date)
                                        {{ $securitiesManagement->issue_date->format('d/m/Y') }}
                                    @else
                                        Chưa có thông tin
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Quốc tịch:</strong></td>
                                <td>{{ $securitiesManagement->nationality ?: 'Chưa có thông tin' }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Thông tin liên hệ -->
                    <div class="col-md-6">
                        <h5><i class="fas fa-address-book"></i> Thông tin liên hệ</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $securitiesManagement->email ?: 'Chưa có thông tin' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Số điện thoại:</strong></td>
                                <td>{{ $securitiesManagement->phone ?: 'Chưa có thông tin' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Địa chỉ:</strong></td>
                                <td>{{ $securitiesManagement->address ?: 'Chưa có thông tin' }}</td>
                            </tr>
                        </table>

                        <h5><i class="fas fa-university"></i> Thông tin ngân hàng</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Số tài khoản:</strong></td>
                                <td>{{ $securitiesManagement->account_number ?: 'Chưa có thông tin' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tên ngân hàng:</strong></td>
                                <td>{{ $securitiesManagement->bank_name ?: 'Chưa có thông tin' }}</td>
                            </tr>
                        </table>

                        <h5><i class="fas fa-chart-bar"></i> Thông tin số lượng</h5>
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box bg-warning">
                                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Chưa lưu ký</span>
                                        <span class="info-box-number">{{ number_format($securitiesManagement->not_deposited_quantity) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-shield-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Đã lưu ký</span>
                                        <span class="info-box-number">{{ number_format($securitiesManagement->deposited_quantity) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Trạng thái -->
                <div class="row">
                    <div class="col-12">
                        <h5><i class="fas fa-flag"></i> Trạng thái</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Trạng thái hoạt động:</strong> 
                                    <span class="badge badge-{{ $securitiesManagement->status == 1 ? 'success' : 'danger' }}">
                                        {{ $securitiesManagement->status_text }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Trạng thái lưu ký:</strong> 
                                    @if($securitiesManagement->not_deposited_quantity > 0)
                                        <span class="badge badge-warning">{{ $securitiesManagement->deposit_status_text }}</span>
                                    @elseif($securitiesManagement->deposited_quantity > 0)
                                        <span class="badge badge-success">{{ $securitiesManagement->deposit_status_text }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $securitiesManagement->deposit_status_text }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Ghi chú -->
                <div class="row">
                    <div class="col-12">
                        <h5><i class="fas fa-sticky-note"></i> Ghi chú</h5>
                        <div class="card">
                            <div class="card-body">
                                {{ $securitiesManagement->notes ?: 'Không có ghi chú.' }}
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Thông tin hệ thống -->
                <div class="row">
                    <div class="col-12">
                        <h5><i class="fas fa-clock"></i> Thông tin hệ thống</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Ngày tạo:</strong></td>
                                <td>{{ $securitiesManagement->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Cập nhật lần cuối:</strong></td>
                                <td>{{ $securitiesManagement->updated_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection