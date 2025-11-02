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
                    <a href="{{ route('admin.securities.management.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.securities.management.update', $securitiesManagement) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <!--  THÔNG TIN CÁ NHÂN -->
                    <div class="section-header bg-info text-white">
                        <i class="fas fa-user"></i>  Thông tin cá nhân
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
                        <i class="fas fa-info-circle"></i>  Thông tin đầu tư
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
                        <i class="fas fa-chart-bar"></i>Số lượng lưu ký
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="not_deposited_quantity">Số lượng chưa lưu ký</label>
                                <input type="number" class="form-control @error('not_deposited_quantity') is-invalid @enderror" 
                                       id="not_deposited_quantity" name="not_deposited_quantity" value="{{ old('not_deposited_quantity', $securitiesManagement->not_deposited_quantity ?? 0) }}" min="0">
                                @error('not_deposited_quantity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deposited_quantity">Số lượng đã lưu ký</label>
                                <input type="number" class="form-control @error('deposited_quantity') is-invalid @enderror" 
                                       id="deposited_quantity" name="deposited_quantity" value="{{ old('deposited_quantity', $securitiesManagement->deposited_quantity ?? 0) }}" min="0">
                                @error('deposited_quantity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- CỘT 4: QUYỀN MUA CHỨNG CHỈ -->
                    <div class="section-header bg-success text-white">
                        <i class="fas fa-money-bill"></i>Quyền mua chứng chỉ
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slqmpb_chualk">SL quyền mua chưa lưu ký</label>
                                <input type="number" class="form-control @error('slqmpb_chualk') is-invalid @enderror" 
                                       id="slqmpb_chualk" name="slqmpb_chualk" value="{{ old('slqmpb_chualk', $securitiesManagement->slqmpb_chualk ?? 0) }}" min="0">
                                @error('slqmpb_chualk')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slqmpb_dalk">SL quyền mua đã lưu ký</label>
                                <input type="number" class="form-control @error('slqmpb_dalk') is-invalid @enderror" 
                                       id="slqmpb_dalk" name="slqmpb_dalk" value="{{ old('slqmpb_dalk', $securitiesManagement->slqmpb_dalk ?? 0) }}" min="0">
                                @error('slqmpb_dalk')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- CỘT 5: PHÂN LOẠI -->
                    <div class="section-header bg-secondary text-white">
                        <i class="fas fa-tags"></i>Phân loại
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cntc">Phân loại (CNTC)</label>
                                <select class="form-control @error('cntc') is-invalid @enderror" id="cntc" name="cntc">
                                    <option value="">-- Chọn phân loại --</option>
                                    <option value="CN" {{ old('cntc', $securitiesManagement->cntc) == 'CN' ? 'selected' : '' }}>Cá nhân (CN)</option>
                                    <option value="TC" {{ old('cntc', $securitiesManagement->cntc) == 'TC' ? 'selected' : '' }}>Tổ chức (TC)</option>
                                </select>
                                @error('cntc')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="txnum">Mã giao dịch (TXNUM)</label>
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
                        <i class="fas fa-university"></i>Thông tin ngân hàng
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

                    <!-- CỘT 7: GHI CHÚ -->
                    <div class="section-header bg-danger text-white">
                        <i class="fas fa-sticky-note"></i>Ghi chú
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
                    <a href="{{ route('admin.securities.management.index') }}" class="btn btn-secondary">
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
