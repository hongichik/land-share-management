@extends('layouts.layout-master')

@section('title', 'Sửa thông tin nhà đầu tư')
@section('page_title', 'Sửa thông tin nhà đầu tư')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
    }
    .section-header {
        background-color: #f8f9fa;
        padding: 10px 15px;
        margin-top: 20px;
        margin-bottom: 15px;
        border-left: 4px solid #007bff;
        font-weight: bold;
        font-size: 14px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sửa thông tin nhà đầu tư: {{ $securitiesManagement->full_name }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities.dividend.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.securities.dividend.update', $securitiesManagement) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <!--  THÔNG TIN CÁ NHÂN -->
                    <div class="section-header bg-info text-white">
                        <i class="fas fa-user"></i> Thông tin cá nhân
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="full_name">Tên đầy đủ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                       id="full_name" name="full_name" value="{{ old('full_name', $securitiesManagement->full_name) }}" required>
                                @error('full_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address">Địa chỉ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" value="{{ old('address', $securitiesManagement->address) }}" required>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $securitiesManagement->email) }}">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Số điện thoại</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $securitiesManagement->phone) }}">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nationality">Quốc tịch</label>
                                <input type="text" class="form-control @error('nationality') is-invalid @enderror" 
                                       id="nationality" name="nationality" value="{{ old('nationality', $securitiesManagement->nationality) }}">
                                @error('nationality')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!--  THÔNG TIN ĐẦU TƯ -->
                    <div class="section-header bg-primary text-white">
                        <i class="fas fa-info-circle"></i> Thông tin đầu tư
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sid">SID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('sid') is-invalid @enderror" 
                                       id="sid" name="sid" value="{{ old('sid', $securitiesManagement->sid) }}" required>
                                @error('sid')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="investor_code">Mã nhà đầu tư <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('investor_code') is-invalid @enderror" 
                                       id="investor_code" name="investor_code" value="{{ old('investor_code', $securitiesManagement->investor_code) }}" required>
                                @error('investor_code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="registration_number">Số đăng ký <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('registration_number') is-invalid @enderror" 
                                       id="registration_number" name="registration_number" value="{{ old('registration_number', $securitiesManagement->registration_number) }}" required>
                                @error('registration_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="issue_date">Ngày phát hành <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                                       id="issue_date" name="issue_date" value="{{ old('issue_date', $securitiesManagement->issue_date ? $securitiesManagement->issue_date->format('Y-m-d') : '') }}" required>
                                @error('issue_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- : SỐ LƯỢNG LƯU KÝ -->
                    <div class="section-header bg-warning text-dark">
                        <i class="fas fa-chart-bar"></i> Số lượng lưu ký
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="not_deposited_quantity">Số lượng chưa lưu ký</label>
                                <input type="number" class="form-control @error('not_deposited_quantity') is-invalid @enderror" 
                                       id="not_deposited_quantity" name="not_deposited_quantity" value="{{ old('not_deposited_quantity', $securitiesManagement->not_deposited_quantity ?? 0) }}">
                                @error('not_deposited_quantity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deposited_quantity">Số lượng đã lưu ký</label>
                                <input type="number" class="form-control @error('deposited_quantity') is-invalid @enderror" 
                                       id="deposited_quantity" name="deposited_quantity" value="{{ old('deposited_quantity', $securitiesManagement->deposited_quantity ?? 0) }}">
                                @error('deposited_quantity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- CỘT 4: QUYỀN MUA CHỨNG CHỈ -->
                    <div class="section-header bg-success text-white">
                        <i class="fas fa-money-bill"></i> Quyền mua chứng chỉ
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slqmpb_chualk">Số lượng quyền mua chưa LK</label>
                                <input type="number" class="form-control @error('slqmpb_chualk') is-invalid @enderror" 
                                       id="slqmpb_chualk" name="slqmpb_chualk" value="{{ old('slqmpb_chualk', $securitiesManagement->slqmpb_chualk ?? 0) }}">
                                @error('slqmpb_chualk')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slqmpb_dalk">Số lượng quyền mua đã LK</label>
                                <input type="number" class="form-control @error('slqmpb_dalk') is-invalid @enderror" 
                                       id="slqmpb_dalk" name="slqmpb_dalk" value="{{ old('slqmpb_dalk', $securitiesManagement->slqmpb_dalk ?? 0) }}">
                                @error('slqmpb_dalk')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- CỘT 5: PHÂN LOẠI -->
                    <div class="section-header bg-secondary text-white">
                        <i class="fas fa-tags"></i> Phân loại
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cntc">Cá nhân / Tổ chức</label>
                                <select class="form-control @error('cntc') is-invalid @enderror" id="cntc" name="cntc">
                                    <option value="">-- Chọn --</option>
                                    <option value="1" {{ old('cntc', $securitiesManagement->cntc) == '1' ? 'selected' : '' }}>Cá nhân (CN)</option>
                                    <option value="2" {{ old('cntc', $securitiesManagement->cntc) == '2' ? 'selected' : '' }}>Tổ chức (TC)</option>
                                </select>
                                @error('cntc')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="txnum">Người thành lập / Nhà phát hành</label>
                                <input type="text" class="form-control @error('txnum') is-invalid @enderror" 
                                       id="txnum" name="txnum" value="{{ old('txnum', $securitiesManagement->txnum) }}">
                                @error('txnum')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- CỘT 6: THÔNG TIN NGÂN HÀNG -->
                    <div class="section-header bg-dark text-white">
                        <i class="fas fa-university"></i> Thông tin ngân hàng
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_account">Số tài khoản</label>
                                <input type="text" class="form-control @error('bank_account') is-invalid @enderror" 
                                       id="bank_account" name="bank_account" value="{{ old('bank_account', $securitiesManagement->bank_account) }}">
                                @error('bank_account')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_name">Tên ngân hàng</label>
                                <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                                       id="bank_name" name="bank_name" value="{{ old('bank_name', $securitiesManagement->bank_name) }}">
                                @error('bank_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- CỘT 7: GHI CHÚ -->
                    <div class="section-header bg-danger text-white">
                        <i class="fas fa-sticky-note"></i> Ghi chú
                    </div>

                    <div class="form-group">
                        <label for="notes">Ghi chú</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="4">{{ old('notes', $securitiesManagement->notes) }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật nhà đầu tư
                    </button>
                    <a href="{{ route('admin.securities.dividend.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush
