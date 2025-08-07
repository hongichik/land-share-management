@extends('layouts.layout-master')

@section('title', 'Sửa thông tin nhà đầu tư')
@section('page_title', 'Sửa thông tin nhà đầu tư')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sửa thông tin nhà đầu tư</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities-management.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.securities-management.update', $securitiesManagement) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <!-- Tên đầy đủ -->
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

                        <!-- SID -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sid">SID</label>
                                <input type="text" class="form-control @error('sid') is-invalid @enderror" 
                                       id="sid" name="sid" value="{{ old('sid', $securitiesManagement->sid) }}">
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
                                       id="investor_code" name="investor_code" value="{{ old('investor_code', $securitiesManagement->investor_code) }}">
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
                                       id="registration_number" name="registration_number" value="{{ old('registration_number', $securitiesManagement->registration_number) }}" required>
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
                                       id="issue_date" name="issue_date" value="{{ old('issue_date', $securitiesManagement->issue_date ? $securitiesManagement->issue_date->format('Y-m-d') : '') }}">
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
                                       id="nationality" name="nationality" value="{{ old('nationality', $securitiesManagement->nationality) }}">
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
                                       id="email" name="email" value="{{ old('email', $securitiesManagement->email) }}">
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
                                       id="phone" name="phone" value="{{ old('phone', $securitiesManagement->phone) }}">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin ngân hàng -->
                    <div class="row">
                        <!-- Số tài khoản -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_number">Số tài khoản</label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                       id="account_number" name="account_number" value="{{ old('account_number', $securitiesManagement->account_number) }}">
                                @error('account_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Tên ngân hàng -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_name">Tên ngân hàng</label>
                                <select class="form-control select2-bank @error('bank_name') is-invalid @enderror" 
                                       id="bank_name" name="bank_name">
                                    <option value="">-- Chọn ngân hàng --</option>
                                    <option value="VPBank" {{ old('bank_name', $securitiesManagement->bank_name) == 'VPBank' ? 'selected' : '' }}>VPBank</option>
                                    <option value="BIDV" {{ old('bank_name', $securitiesManagement->bank_name) == 'BIDV' ? 'selected' : '' }}>BIDV</option>
                                    <option value="Vietcombank" {{ old('bank_name', $securitiesManagement->bank_name) == 'Vietcombank' ? 'selected' : '' }}>Vietcombank</option>
                                    <option value="VietinBank" {{ old('bank_name', $securitiesManagement->bank_name) == 'VietinBank' ? 'selected' : '' }}>VietinBank</option>
                                    <option value="MBBANK" {{ old('bank_name', $securitiesManagement->bank_name) == 'MBBANK' ? 'selected' : '' }}>MBBANK</option>
                                    <option value="ACB" {{ old('bank_name', $securitiesManagement->bank_name) == 'ACB' ? 'selected' : '' }}>ACB</option>
                                    <option value="SHB" {{ old('bank_name', $securitiesManagement->bank_name) == 'SHB' ? 'selected' : '' }}>SHB</option>
                                    <option value="Techcombank" {{ old('bank_name', $securitiesManagement->bank_name) == 'Techcombank' ? 'selected' : '' }}>Techcombank</option>
                                    <option value="Agribank" {{ old('bank_name', $securitiesManagement->bank_name) == 'Agribank' ? 'selected' : '' }}>Agribank</option>
                                    <option value="HDBank" {{ old('bank_name', $securitiesManagement->bank_name) == 'HDBank' ? 'selected' : '' }}>HDBank</option>
                                    <option value="LienVietPostBank" {{ old('bank_name', $securitiesManagement->bank_name) == 'LienVietPostBank' ? 'selected' : '' }}>LienVietPostBank</option>
                                    <option value="VIB" {{ old('bank_name', $securitiesManagement->bank_name) == 'VIB' ? 'selected' : '' }}>VIB</option>
                                    <option value="SeABank" {{ old('bank_name', $securitiesManagement->bank_name) == 'SeABank' ? 'selected' : '' }}>SeABank</option>
                                    <option value="VBSP" {{ old('bank_name', $securitiesManagement->bank_name) == 'VBSP' ? 'selected' : '' }}>VBSP</option>
                                    <option value="TPBank" {{ old('bank_name', $securitiesManagement->bank_name) == 'TPBank' ? 'selected' : '' }}>TPBank</option>
                                    <option value="OCB" {{ old('bank_name', $securitiesManagement->bank_name) == 'OCB' ? 'selected' : '' }}>OCB</option>
                                    <option value="MSB" {{ old('bank_name', $securitiesManagement->bank_name) == 'MSB' ? 'selected' : '' }}>MSB</option>
                                    <option value="Sacombank" {{ old('bank_name', $securitiesManagement->bank_name) == 'Sacombank' ? 'selected' : '' }}>Sacombank</option>
                                    <option value="Eximbank" {{ old('bank_name', $securitiesManagement->bank_name) == 'Eximbank' ? 'selected' : '' }}>Eximbank</option>
                                    <option value="SCB" {{ old('bank_name', $securitiesManagement->bank_name) == 'SCB' ? 'selected' : '' }}>SCB</option>
                                    <option value="VDB" {{ old('bank_name', $securitiesManagement->bank_name) == 'VDB' ? 'selected' : '' }}>VDB</option>
                                    <option value="Nam A Bank" {{ old('bank_name', $securitiesManagement->bank_name) == 'Nam A Bank' ? 'selected' : '' }}>Nam A Bank</option>
                                    <option value="ABBANK" {{ old('bank_name', $securitiesManagement->bank_name) == 'ABBANK' ? 'selected' : '' }}>ABBANK</option>
                                    <option value="PVcomBank" {{ old('bank_name', $securitiesManagement->bank_name) == 'PVcomBank' ? 'selected' : '' }}>PVcomBank</option>
                                    <option value="Bac A Bank" {{ old('bank_name', $securitiesManagement->bank_name) == 'Bac A Bank' ? 'selected' : '' }}>Bac A Bank</option>
                                    <option value="UOB" {{ old('bank_name', $securitiesManagement->bank_name) == 'UOB' ? 'selected' : '' }}>UOB</option>
                                    <option value="Woori" {{ old('bank_name', $securitiesManagement->bank_name) == 'Woori' ? 'selected' : '' }}>Woori</option>
                                    <option value="HSBC" {{ old('bank_name', $securitiesManagement->bank_name) == 'HSBC' ? 'selected' : '' }}>HSBC</option>
                                    <option value="SCBVL" {{ old('bank_name', $securitiesManagement->bank_name) == 'SCBVL' ? 'selected' : '' }}>SCBVL</option>
                                    <option value="PBVN" {{ old('bank_name', $securitiesManagement->bank_name) == 'PBVN' ? 'selected' : '' }}>PBVN</option>
                                    <option value="SHBVN" {{ old('bank_name', $securitiesManagement->bank_name) == 'SHBVN' ? 'selected' : '' }}>SHBVN</option>
                                    <option value="NCB" {{ old('bank_name', $securitiesManagement->bank_name) == 'NCB' ? 'selected' : '' }}>NCB</option>
                                    <option value="VietABank" {{ old('bank_name', $securitiesManagement->bank_name) == 'VietABank' ? 'selected' : '' }}>VietABank</option>
                                    <option value="BVBank" {{ old('bank_name', $securitiesManagement->bank_name) == 'BVBank' ? 'selected' : '' }}>BVBank</option>
                                    <option value="Vikki Bank" {{ old('bank_name', $securitiesManagement->bank_name) == 'Vikki Bank' ? 'selected' : '' }}>Vikki Bank</option>
                                    <option value="Vietbank" {{ old('bank_name', $securitiesManagement->bank_name) == 'Vietbank' ? 'selected' : '' }}>Vietbank</option>
                                    <option value="ANZVL" {{ old('bank_name', $securitiesManagement->bank_name) == 'ANZVL' ? 'selected' : '' }}>ANZVL</option>
                                    <option value="MBV" {{ old('bank_name', $securitiesManagement->bank_name) == 'MBV' ? 'selected' : '' }}>MBV</option>
                                    <option value="CIMB" {{ old('bank_name', $securitiesManagement->bank_name) == 'CIMB' ? 'selected' : '' }}>CIMB</option>
                                    <option value="Kienlongbank" {{ old('bank_name', $securitiesManagement->bank_name) == 'Kienlongbank' ? 'selected' : '' }}>Kienlongbank</option>
                                    <option value="IVB" {{ old('bank_name', $securitiesManagement->bank_name) == 'IVB' ? 'selected' : '' }}>IVB</option>
                                    <option value="BAOVIET Bank" {{ old('bank_name', $securitiesManagement->bank_name) == 'BAOVIET Bank' ? 'selected' : '' }}>BAOVIET Bank</option>
                                    <option value="SAIGONBANK" {{ old('bank_name', $securitiesManagement->bank_name) == 'SAIGONBANK' ? 'selected' : '' }}>SAIGONBANK</option>
                                    <option value="Co-opBank" {{ old('bank_name', $securitiesManagement->bank_name) == 'Co-opBank' ? 'selected' : '' }}>Co-opBank</option>
                                    <option value="GPBank" {{ old('bank_name', $securitiesManagement->bank_name) == 'GPBank' ? 'selected' : '' }}>GPBank</option>
                                    <option value="VRB" {{ old('bank_name', $securitiesManagement->bank_name) == 'VRB' ? 'selected' : '' }}>VRB</option>
                                    <option value="VCBNeo" {{ old('bank_name', $securitiesManagement->bank_name) == 'VCBNeo' ? 'selected' : '' }}>VCBNeo</option>
                                    <option value="HLBVN" {{ old('bank_name', $securitiesManagement->bank_name) == 'HLBVN' ? 'selected' : '' }}>HLBVN</option>
                                    <option value="PGBank" {{ old('bank_name', $securitiesManagement->bank_name) == 'PGBank' ? 'selected' : '' }}>PGBank</option>
                                </select>
                                @error('bank_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="form-group">
                        <label for="address">Địa chỉ</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3">{{ old('address', $securitiesManagement->address) }}</textarea>
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
                                       id="not_deposited_quantity" name="not_deposited_quantity" value="{{ old('not_deposited_quantity', $securitiesManagement->not_deposited_quantity) }}" min="0">
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
                                       id="deposited_quantity" name="deposited_quantity" value="{{ old('deposited_quantity', $securitiesManagement->deposited_quantity) }}" min="0">
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
                            <option value="1" {{ old('status', $securitiesManagement->status) == '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="0" {{ old('status', $securitiesManagement->status) == '0' ? 'selected' : '' }}>Không hoạt động</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Ghi chú -->
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
                    <a href="{{ route('admin.securities-management.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-bank').select2({
            theme: 'bootstrap4',
            placeholder: '-- Chọn ngân hàng --',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush