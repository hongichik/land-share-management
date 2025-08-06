@extends('layouts.layout-master')

@section('title', 'Chi tiết thanh toán')
@section('page_title', 'Chi tiết thanh toán')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chi tiết thanh toán</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.land-rental-payment-histories.edit', [$landRentalContract, $landRentalPaymentHistory]) }}"
                            class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="{{ route('admin.land-rental-payment-histories.index', $landRentalContract) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Contract Info Summary -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle"></i> Thông tin hợp đồng</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Số hợp đồng:</strong> {{ $landRentalContract->contract_number }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Khu vực:</strong> {{ $landRentalContract->rental_zone ?: 'Chưa có thông tin' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Vị trí:</strong> {{ $landRentalContract->rental_location ?: 'Chưa có thông tin' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Diện tích:</strong> 
                                        @if ($landRentalContract->area && isset($landRentalContract->area['value']))
                                            {{ number_format($landRentalContract->area['value'], 2) }}
                                            {{ $landRentalContract->area['unit'] ?? 'm²' }}
                                        @else
                                            Chưa có thông tin
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Thông tin thanh toán -->
                        <div class="col-md-6">
                            <h5><i class="fas fa-money-bill-wave"></i> Thông tin thanh toán</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Kỳ nộp:</strong></td>
                                    <td>
                                        <span class="badge bg-info badge-info">{{ $landRentalPaymentHistory->period_name }}</span>
                                        @if($landRentalPaymentHistory->period == 1)
                                            <small class="text-muted">(Tháng 1-6)</small>
                                        @else
                                            <small class="text-muted">(Tháng 7-12)</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Loại nộp:</strong></td>
                                    <td>
                                        @switch($landRentalPaymentHistory->payment_type)
                                            @case(1)
                                                <span class="badge bg-success badge-success">{{ $landRentalPaymentHistory->payment_type_name }}</span>
                                                @break
                                            @case(2)
                                                <span class="badge bg-primary badge-primary">{{ $landRentalPaymentHistory->payment_type_name }}</span>
                                                @break
                                            @case(3)
                                                <span class="badge bg-warning badge-warning">{{ $landRentalPaymentHistory->payment_type_name }}</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary badge-secondary">{{ $landRentalPaymentHistory->payment_type_name }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Số tiền:</strong></td>
                                    <td class="text-success">
                                        <h5>{{ number_format($landRentalPaymentHistory->amount, 0, ',', '.') }} VND</h5>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày nộp:</strong></td>
                                    <td>{{ $landRentalPaymentHistory->payment_date->format('d/m/Y') }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Ghi chú -->
                        <div class="col-md-6">
                            <h5><i class="fas fa-sticky-note"></i> Ghi chú</h5>
                            <div class="card">
                                <div class="card-body">
                                    @if ($landRentalPaymentHistory->notes)
                                        <p>{{ $landRentalPaymentHistory->notes }}</p>
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
                                    <td>{{ $landRentalPaymentHistory->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cập nhật lần cuối:</strong></td>
                                    <td>{{ $landRentalPaymentHistory->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            @php
                                $latestPaymentId = App\Models\LandRentalPaymentHistory::where('land_rental_contract_id', $landRentalContract->id)->max('id');
                                $isLatest = ($landRentalPaymentHistory->id == $latestPaymentId);
                            @endphp
                            
                            <div class="btn-group" role="group">
                                @if($isLatest)
                                    <a href="{{ route('admin.land-rental-payment-histories.edit', [$landRentalContract, $landRentalPaymentHistory]) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Sửa thanh toán
                                    </a>
                                    <form method="POST" action="{{ route('admin.land-rental-payment-histories.destroy', [$landRentalContract, $landRentalPaymentHistory]) }}" 
                                          style="display:inline-block;" 
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch sử thanh toán này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Xóa thanh toán
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-secondary" disabled title="Chỉ được sửa thanh toán mới nhất">
                                        <i class="fas fa-edit"></i> Sửa thanh toán
                                    </button>
                                    <button class="btn btn-secondary" disabled title="Chỉ được xóa thanh toán mới nhất">
                                        <i class="fas fa-trash"></i> Xóa thanh toán
                                    </button>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Chỉ có thể sửa/xóa thanh toán mới nhất. Thanh toán này đã được lưu trữ và không thể thay đổi.
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
