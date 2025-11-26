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
                        <a href="{{ route('admin.land-rental.payment-histories.index', $landRentalContract->id) }}"
                            class="btn btn-info btn-sm">
                            <i class="fas fa-money-bill-wave"></i> Lịch sử thanh toán
                        </a>
                        <a href="{{ route('admin.land-rental.contracts.edit', $landRentalContract) }}"
                            class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="{{ route('admin.land-rental.contracts.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Thông tin cơ bản -->
                        <div class="col-md-6">
                            <h5><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                            <p><strong>Số hợp đồng:</strong> {{ $landRentalContract->contract_number }}</p>
                            <p><strong>Quyết định cho thuê đất:</strong>
                                {{ $landRentalContract->rental_decision ?: 'Chưa có thông tin' }}</p>
                            <p><strong>Khu vực thuê:</strong> {{ $landRentalContract->rental_zone ?: 'Chưa có thông tin' }}
                            </p>
                            <p><strong>Vị trí thuê đất:</strong>
                                {{ $landRentalContract->rental_location ?: 'Chưa có thông tin' }}</p>
                            <p><strong>Mục đích thuê đất:</strong>
                                {{ $landRentalContract->rental_purpose ?: 'Chưa có thông tin' }}</p>
                            <p><strong>Thuế xuất:</strong> {{ $landRentalContract->export_tax }}%
                            </p>
                            <p><strong>Giá thuế đất:</strong> 
                                @if($landRentalContract->land_tax_price)
                                    {{ number_format($landRentalContract->land_tax_price, 0, ',', '.') }} VND
                                @else
                                    Chưa có thông tin
                                @endif
                            </p>
                        </div>

                        <!-- Thông tin diện tích và thời gian -->
                        <div class="col-md-6">
                            <h5><i class="fas fa-calculator"></i> Thông tin diện tích & thời gian</h5>
                            <p>
                                <strong>Diện tích:</strong>
                                @if ($landRentalContract->area && isset($landRentalContract->area['value']))
                                    {{ number_format($landRentalContract->area['value'], 2) }}
                                    {{ $landRentalContract->area['unit'] ?? 'm²' }}
                                @else
                                    Chưa có thông tin
                                @endif
                            </p>
                            <p>
                                <strong>Thời gian thuê:</strong>

                                @if ($landRentalContract->rental_period)
                                    @if (isset($landRentalContract->rental_period['start_date']))
                                        <strong>Từ:</strong>
                                        {{ \Carbon\Carbon::parse($landRentalContract->rental_period['start_date'])->format('d/m/Y') }} - 
                                    @endif
                                    @if (isset($landRentalContract->rental_period['end_date']))
                                        <strong>đến:</strong>
                                        {{ \Carbon\Carbon::parse($landRentalContract->rental_period['end_date'])->format('d/m/Y') }} - 
                                    @endif
                                    @if (isset($landRentalContract->rental_period['years']))
                                        <strong>thời gian:</strong> {{ $landRentalContract->rental_period['years'] }} năm
                                    @endif
                                @else
                                    Chưa có thông tin
                                @endif
                            </p>
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
                                            @if ($landRentalContract->contract_file_path)
                                                <a href="{{ asset('storage/' . str_replace('public/', '', $landRentalContract->contract_file_path)) }}"
                                                    target="_blank" class="btn btn-primary">
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
                                            @if ($landRentalContract->rental_decision_file_path)
                                                <a href="{{ asset('storage/' . str_replace('public/', '', $landRentalContract->rental_decision_file_path)) }}"
                                                    target="_blank" class="btn btn-primary">
                                                    <i class="fas fa-file-pdf"></i> Xem/Tải file
                                                </a>
                                                @if ($landRentalContract->rental_decision_file_name)
                                                    <br><small
                                                        class="text-muted">{{ $landRentalContract->rental_decision_file_name }}</small>
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

                    <!-- Payment Summary -->
                    <div class="row">
                        <div class="col-12">
                            <h5><i class="fas fa-money-bill-wave"></i> Tóm tắt thanh toán</h5>
                            @php
                                $paymentHistories = $landRentalContract->paymentHistories()->get();
                                $totalPaid = $paymentHistories->sum('amount');
                                $period1Payments = $paymentHistories->where('period', 1);
                                $period2Payments = $paymentHistories->where('period', 2);
                            @endphp
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-money-bill-wave"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng đã thanh toán</span>
                                            <span class="info-box-number">{{ number_format($totalPaid, 0, ',', '.') }} VND</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-calendar-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Kỳ 1 </span>
                                            <span class="info-box-number">{{ number_format($period1Payments->sum('amount'), 0, ',', '.') }} VND</span>
                                            <span class="progress-description">{{ $period1Payments->count() }} lần thanh toán</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-calendar-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Kỳ 2 </span>
                                            <span class="info-box-number">{{ number_format($period2Payments->sum('amount'), 0, ',', '.') }} VND</span>
                                            <span class="progress-description">{{ $period2Payments->count() }} lần thanh toán</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary"><i class="fas fa-list"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng số lần</span>
                                            <span class="info-box-number">{{ $paymentHistories->count() }}</span>
                                            <span class="progress-description">
                                                <a href="{{ route('admin.land-rental.payment-histories.index', $landRentalContract) }}" class="text-primary">
                                                    Xem chi tiết
                                                </a>
                                            </span>
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
                                    @if ($landRentalContract->notes)
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
