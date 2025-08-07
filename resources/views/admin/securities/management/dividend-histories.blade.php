@extends('layouts.layout-master')

@section('title', 'Lịch sử thanh toán cổ tức - ' . $securitiesManagement->full_name)
@section('page_title', 'Lịch sử thanh toán cổ tức - ' . $securitiesManagement->full_name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thông tin nhà đầu tư</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities.management.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 200px;">Tên đầy đủ:</th>
                                <td>{{ $securitiesManagement->full_name }}</td>
                            </tr>
                            <tr>
                                <th>SID:</th>
                                <td>{{ $securitiesManagement->sid }}</td>
                            </tr>
                            <tr>
                                <th>Mã nhà đầu tư:</th>
                                <td>{{ $securitiesManagement->investor_code }}</td>
                            </tr>
                            <tr>
                                <th>Số đăng ký:</th>
                                <td>{{ $securitiesManagement->registration_number }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 200px;">Ngày phát hành:</th>
                                <td>{{ $securitiesManagement->issue_date ? $securitiesManagement->issue_date->format('d/m/Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Số lượng chưa lưu ký:</th>
                                <td>{{ number_format($securitiesManagement->not_deposited_quantity) }}</td>
                            </tr>
                            <tr>
                                <th>Số lượng đã lưu ký:</th>
                                <td>{{ number_format($securitiesManagement->deposited_quantity) }}</td>
                            </tr>
                            <tr>
                                <th>Trạng thái:</th>
                                <td>
                                    <span class="badge badge-{{ $securitiesManagement->status == 1 ? 'success' : 'danger' }}">
                                        {{ $securitiesManagement->status_text }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lịch sử thanh toán cổ tức</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities.history.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tạo thanh toán mới
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($dividendRecords->isEmpty())
                    <div class="alert alert-info">
                        Không có dữ liệu thanh toán cổ tức cho nhà đầu tư này.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Ngày thanh toán</th>
                                    <th>Loại cổ phần</th>
                                    <th>Số lượng</th>
                                    <th>Số tiền trước thuế</th>
                                    <th>Thuế (%)</th>
                                    <th>Số tiền sau thuế</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dividendRecords as $index => $record)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $record->payment_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($record->deposited_shares_quantity > 0 && $record->non_deposited_shares_quantity > 0)
                                            Cả hai
                                        @elseif($record->deposited_shares_quantity > 0)
                                            Đã lưu ký
                                        @elseif($record->non_deposited_shares_quantity > 0)
                                            Chưa lưu ký
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ number_format($record->deposited_shares_quantity + $record->non_deposited_shares_quantity) }}</td>
                                    <td>{{ number_format($record->deposited_amount_before_tax + $record->non_deposited_amount_before_tax) }} VNĐ</td>
                                    <td>{{ number_format($record->tax_rate * 100, 2) }}%</td>
                                    <td>{{ number_format(($record->deposited_amount_before_tax + $record->non_deposited_amount_before_tax) * (1 - $record->tax_rate)) }} VNĐ</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="3">Tổng cộng</th>
                                    <th>{{ number_format($dividendRecords->sum(function($record) { return $record->deposited_shares_quantity + $record->non_deposited_shares_quantity; })) }}</th>
                                    <th>{{ number_format($dividendRecords->sum(function($record) { return $record->deposited_amount_before_tax + $record->non_deposited_amount_before_tax; })) }} VNĐ</th>
                                    <th>-</th>
                                    <th>{{ number_format($dividendRecords->sum(function($record) { return ($record->deposited_amount_before_tax + $record->non_deposited_amount_before_tax) * (1 - $record->tax_rate); })) }} VNĐ</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/custom-admin.css') }}">
@endpush
