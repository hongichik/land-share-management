@extends('layouts.layout-master')

@section('title', 'Thêm nhà đầu tư')
@section('page_title', 'Thêm nhà đầu tư')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thêm nhà đầu tư mới</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities-management.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.securities-management.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Tên đầy đủ -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="full_name">Tên đầy đủ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                       id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                                @error('full_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- SID -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sid">SID</label>
                                <input type="text" class="form-control @error('sid') is-invalid @enderror" 
                                       id="sid" name="sid" value="{{ old('sid') }}">
                                @error('sid')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Mã nhà đầu tư -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="investor_code">Mã nhà đầu tư</label>
                                <input type="text" class="form-control @error('investor_code') is-invalid @enderror" 
                                       id="investor_code" name="investor_code" value="{{ old('investor_code') }}">
                                @error('investor_code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Số đăng ký -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="registration_number">Số đăng ký <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('registration_number') is-invalid @enderror" 
                                       id="registration_number" name="registration_number" value="{{ old('registration_number') }}" required>
                                @error('registration_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Ngày phát hành -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="issue_date">Ngày phát hành</label>
                                <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                                       id="issue_date" name="issue_date" value="{{ old('issue_date') }}">
                                @error('issue_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Quốc tịch -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nationality">Quốc tịch</label>
                                <input type="text" class="form-control @error('nationality') is-invalid @enderror" 
                                       id="nationality" name="nationality" value="{{ old('nationality') }}">
                                @error('nationality')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Email -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Số điện thoại -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Số điện thoại</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="form-group">
                        <label for="address">Địa chỉ</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3">{{ old('address') }}</textarea>
                        @error('address')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Số lượng chưa lưu ký -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="not_deposited_quantity">Số lượng chưa lưu ký</label>
                                <input type="number" class="form-control @error('not_deposited_quantity') is-invalid @enderror" 
                                       id="not_deposited_quantity" name="not_deposited_quantity" value="{{ old('not_deposited_quantity', 0) }}" min="0">
                                @error('not_deposited_quantity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Số lượng đã lưu ký -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deposited_quantity">Số lượng đã lưu ký</label>
                                <input type="number" class="form-control @error('deposited_quantity') is-invalid @enderror" 
                                       id="deposited_quantity" name="deposited_quantity" value="{{ old('deposited_quantity', 0) }}" min="0">
                                @error('deposited_quantity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Trạng thái -->
                    <div class="form-group">
                        <label for="status">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="">-- Chọn trạng thái --</option>
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Không hoạt động</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Ghi chú -->
                    <div class="form-group">
                        <label for="notes">Ghi chú</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu nhà đầu tư
                    </button>
                    <a href="{{ route('admin.securities-management.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection