@extends('layouts.layout-master')

@section('title', 'Sửa thanh toán')
@section('page_title', 'Sửa thanh toán')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sửa thanh toán</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.land-rental.payment-histories.index', $landRentalContract) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            
            <!-- Contract Info Summary -->
            <div class="card-body">
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

                <form action="{{ route('admin.land-rental.payment-histories.update', [$landRentalContract, $landRentalPaymentHistory]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <!-- Kỳ nộp -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="period">Kỳ nộp <span class="text-danger">*</span></label>
                                <select class="form-control @error('period') is-invalid @enderror" id="period" name="period" required>
                                    <option value="">Chọn kỳ nộp</option>
                                    <option value="1" {{ old('period', $landRentalPaymentHistory->period) == '1' ? 'selected' : '' }}>Kỳ 1 (Tháng 1-6)</option>
                                    <option value="2" {{ old('period', $landRentalPaymentHistory->period) == '2' ? 'selected' : '' }}>Kỳ 2 (Tháng 7-12)</option>
                                </select>
                                @error('period')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Loại nộp -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_type">Loại nộp <span class="text-danger">*</span></label>
                                <select class="form-control @error('payment_type') is-invalid @enderror" id="payment_type" name="payment_type" required>
                                    <option value="">Chọn loại nộp</option>
                                    <option value="1" {{ old('payment_type', $landRentalPaymentHistory->payment_type) == '1' ? 'selected' : '' }}>Nộp trước</option>
                                    <option value="2" {{ old('payment_type', $landRentalPaymentHistory->payment_type) == '2' ? 'selected' : '' }}>Nộp đúng hạn</option>
                                    <option value="3" {{ old('payment_type', $landRentalPaymentHistory->payment_type) == '3' ? 'selected' : '' }}>Miễn giảm</option>
                                </select>
                                @error('payment_type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Số tiền -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Số tiền (VND) <span class="text-danger">*</span></label>
                                <input type="number" min="0" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" value="{{ old('amount', $landRentalPaymentHistory->amount) }}" required>
                                @error('amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Ngày nộp -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_date">Ngày nộp <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('payment_date') is-invalid @enderror" 
                                       id="payment_date" name="payment_date" value="{{ old('payment_date', $landRentalPaymentHistory->payment_date->format('Y-m-d')) }}" required>
                                @error('payment_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Ghi chú -->
                    <div class="form-group">
                        <label for="notes">Ghi chú</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="4">{{ old('notes', $landRentalPaymentHistory->notes) }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật thanh toán
                        </button>
                        <a href="{{ route('admin.land-rental.payment-histories.index', $landRentalContract) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Remove formatting before submit
    $('form').on('submit', function() {
        let amount = $('#amount').val();
        if (amount) {
            $('#amount').val(amount.replace(/,/g, ''));
        }
    });
});
</script>
@endpush
