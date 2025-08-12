@extends('layouts.layout-master')

@section('title', 'Thêm Giá thuê đất')
@section('page_title', 'Thêm Giá thuê đất')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thêm Giá thuê đất mới</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.land-rental-prices.index', $landRentalContract) }}"
                            class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.land-rental-prices.store', $landRentalContract) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="price_decision">Quyết định giá thuê</label>
                            <input type="text" class="form-control @error('price_decision') is-invalid @enderror"
                                id="price_decision" name="price_decision" value="{{ old('price_decision') }}">
                            @error('price_decision')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="price_decision_file">File Quyết định giá thuê</label>
                            <input type="file" class="form-control @error('price_decision_file') is-invalid @enderror"
                                id="price_decision_file" name="price_decision_file">
                            @error('price_decision_file')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="price_period">Thời gian áp dụng</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="price_period_start">Ngày bắt đầu</label>
                                    <input type="date" class="form-control" id="price_period_start"
                                        name="price_period[start]" value="{{ $defaultStartDate }}" readonly>
                                    <small class="text-muted">Thời gian bắt đầu được tự động gán</small>
                                </div>
                                <div class="col-md-4">
                                    <label for="price_period_end">Ngày kết thúc <span class="text-danger">*</span></label>
                                    <input type="date"
                                        class="form-control @error('price_period.end') is-invalid @enderror"
                                        id="price_period_end" name="price_period[end]" value="{{ old('price_period.end') }}"
                                        required>
                                    @error('price_period.end')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="price_period_years">Số năm</label>
                                    <input type="number" step="0.1"
                                        class="form-control @error('price_period.years') is-invalid @enderror"
                                        id="price_period_years" name="price_period[years]"
                                        value="{{ old('price_period.years') }}" placeholder="Số năm">
                                    @error('price_period.years')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Nhập ngày kết thúc hoặc số năm để tự động tính toán.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="rental_price">Giá thuê</label>
                            <input type="number" step="0.01"
                                class="form-control @error('rental_price') is-invalid @enderror" id="rental_price"
                                name="rental_price" value="{{ old('rental_price') }}">
                            @error('rental_price')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="note">Ghi chú</label>
                            <input type="text" class="form-control @error('note') is-invalid @enderror" 
                                   id="note" name="note" value="{{ old('note') }}" 
                                   placeholder="Nhập ghi chú về giá thuê đất (nếu có)">
                            @error('note')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu Giá thuê đất
                        </button>
                        <a href="{{ route('admin.land-rental-prices.index', $landRentalContract) }}"
                            class="btn btn-secondary">
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
        $(document).ready(function() {
            function calculatePeriod() {
                const startDate = $('#price_period_start').val();
                const endDate = $('#price_period_end').val();
                const years = $('#price_period_years').val();

                if (startDate && years && !endDate) {
                    const start = new Date(startDate);
                    const end = new Date(start);
                    end.setFullYear(start.getFullYear() + parseFloat(years));
                    $('#price_period_end').val(end.toISOString().split('T')[0]);
                } else if (startDate && endDate && !years) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    const diffTime = Math.abs(end - start);
                    const diffYears = diffTime / (1000 * 60 * 60 * 24 * 365.25);
                    $('#price_period_end').val(Math.ceil(diffYears));
                }
            }

            $('#price_period_years').on('input', function() {
                const startDate = $('#price_period_start').val();
                const years = $(this).val();
                if (startDate && years) {
                    $('#price_period_end').val('');
                    calculatePeriod();
                }
            });


            $('#price_period_end').on('blur', function() {
                const startDate = $('#price_period_start').val();
                const endDate = $('#price_period_end').val();

                if (startDate && endDate && new Date(endDate) <= new Date(startDate)) {
                    alert('Ngày kết thúc phải sau ngày bắt đầu!');
                    $(this).val('');
                    $('#price_period_years').val('');
                }
            });
        });
    </script>
@endpush
