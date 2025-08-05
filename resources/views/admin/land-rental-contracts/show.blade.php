@extends('layouts.layout-master')

@section('title', 'Chi tiết Hợp đồng thuê đất')
@section('page_title', 'Chi tiết Hợp đồng thuê đất')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Chi tiết Hợp đồng thuê đất: {{ $landRentalContract->contract_number }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.land-rental-contracts.edit', $landRentalContract) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    <a href="{{ route('admin.land-rental-contracts.index') }}" class="btn btn-secondary btn-sm">
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
                                <td><strong>Số hợp đồng:</strong></td>
                                <td>{{ $landRentalContract->contract_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Quyết định cho thuê đất:</strong></td>
                                <td>{{ $landRentalContract->rental_decision ?: 'Chưa có thông tin' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Khu vực thuê:</strong></td>
                                <td>{{ $landRentalContract->rental_zone ?: 'Chưa có thông tin' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Vị trí thuê đất:</strong></td>
                                <td>{{ $landRentalContract->rental_location ?: 'Chưa có thông tin' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Thuế xuất:</strong></td>
                                <td>{{ number_format($landRentalContract->export_tax * 100, 4) }}%</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Thông tin diện tích và thời gian -->
                    <div class="col-md-6">
                        <h5><i class="fas fa-calculator"></i> Thông tin diện tích & thời gian</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Diện tích:</strong></td>
                                <td>
                                    @if($landRentalContract->area && isset($landRentalContract->area['value']))
                                        {{ number_format($landRentalContract->area['value'], 2) }} {{ $landRentalContract->area['unit'] ?? 'm²' }}
                                    @else
                                        Chưa có thông tin
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Thời gian thuê:</strong></td>
                                <td>
                                    @if($landRentalContract->rental_period)
                                        @if(isset($landRentalContract->rental_period['start_date']))
                                            <strong>Từ:</strong> {{ \Carbon\Carbon::parse($landRentalContract->rental_period['start_date'])->format('d/m/Y') }}<br>
                                        @endif
                                        @if(isset($landRentalContract->rental_period['end_date']))
                                            <strong>Đến:</strong> {{ \Carbon\Carbon::parse($landRentalContract->rental_period['end_date'])->format('d/m/Y') }}<br>
                                        @endif
                                        @if(isset($landRentalContract->rental_period['years']))
                                            <strong>Thời gian:</strong> {{ $landRentalContract->rental_period['years'] }} năm
                                        @endif
                                    @else
                                        Chưa có thông tin
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <!-- File đính kèm -->
                <div class="row">
                    <div class="col-12">
                        <h5><i class="fas fa-paperclip"></i> File đính kèm</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title">File hợp đồng</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($landRentalContract->contract_file_path)
                                            <a href="{{ asset('storage/' . str_replace('public/', '', $landRentalContract->contract_file_path)) }}" target="_blank" class="btn btn-primary">
                                                <i class="fas fa-file-pdf"></i> Xem/Tải file
                                            </a>
                                        @else
                                            <p class="text-muted">Chưa có file</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title">File quyết định thuê đất</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($landRentalContract->rental_decision_file_path)
                                            <a href="{{ asset('storage/' . str_replace('public/', '', $landRentalContract->rental_decision_file_path)) }}" target="_blank" class="btn btn-primary">
                                                <i class="fas fa-file-pdf"></i> Xem/Tải file
                                            </a>
                                            @if($landRentalContract->rental_decision_file_name)
                                                <br><small class="text-muted">{{ $landRentalContract->rental_decision_file_name }}</small>
                                            @endif
                                        @else
                                            <p class="text-muted">Chưa có file</p>
                                        @endif
                                    </div>
                                </div>
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
                                @if($landRentalContract->notes)
                                    <p>{{ $landRentalContract->notes }}</p>
                                @else
                                    <p class="text-muted">Không có ghi chú</p>
                                @endif
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
                                <td>{{ $landRentalContract->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Cập nhật lần cuối:</strong></td>
                                <td>{{ $landRentalContract->updated_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
