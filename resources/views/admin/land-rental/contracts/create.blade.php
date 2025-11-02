@extends('layouts.layout-master')

@section('title', 'Thêm Hợp đồng thuê đất')
@section('page_title', 'Thêm Hợp đồng thuê đất')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thêm Hợp đồng thuê đất mới</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.land-rental.contracts.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.land-rental.contracts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Số hợp đồng -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contract_number">Số hợp đồng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('contract_number') is-invalid @enderror" 
                                       id="contract_number" name="contract_number" value="{{ old('contract_number') }}" required>
                                @error('contract_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <!-- File hợp đồng -->
                            <div class="form-group">
                                <label for="contract_file">File hợp đồng</label>
                                <input type="file" class="form-control-file @error('contract_file') is-invalid @enderror" 
                                       id="contract_file" name="contract_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <small class="form-text text-muted">Chỉ chấp nhận file PDF, DOC, DOCX, JPG, JPEG, PNG (tối đa 10MB)</small>
                                @error('contract_file')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Quyết định cho thuê đất -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rental_decision">Quyết định cho thuê đất</label>
                                <input type="text" class="form-control @error('rental_decision') is-invalid @enderror" 
                                       id="rental_decision" name="rental_decision" value="{{ old('rental_decision') }}">
                                @error('rental_decision')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <!-- File quyết định thuê đất -->
                            <div class="form-group">
                                <label for="rental_decision_file">File quyết định thuê đất</label>
                                <input type="file" class="form-control-file @error('rental_decision_file') is-invalid @enderror" 
                                       id="rental_decision_file" name="rental_decision_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <small class="form-text text-muted">Chỉ chấp nhận file PDF, DOC, DOCX, JPG, JPEG, PNG (tối đa 10MB)</small>
                                @error('rental_decision_file')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Khu vực thuê -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="rental_zone">Khu vực thuê</label>
                                <input type="text" class="form-control @error('rental_zone') is-invalid @enderror" 
                                       id="rental_zone" name="rental_zone" value="{{ old('rental_zone') }}">
                                @error('rental_zone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Vị trí thuê đất -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="rental_location">Vị trí thuê đất</label>
                                <input type="text" class="form-control @error('rental_location') is-invalid @enderror" 
                                       id="rental_location" name="rental_location" value="{{ old('rental_location') }}">
                                @error('rental_location')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Mục đích thuê đất -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="rental_purpose">Mục đích thuê đất</label>
                                <input type="text" class="form-control @error('rental_purpose') is-invalid @enderror" 
                                       id="rental_purpose" name="rental_purpose" value="{{ old('rental_purpose') }}">
                                @error('rental_purpose')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Thuế xuất -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="export_tax">Thuế xuất (%)</label>
                                <input type="number" class="form-control @error('export_tax') is-invalid @enderror" 
                                       id="export_tax" name="export_tax" value="{{ old('export_tax', 0.03) }}"
                                       step="0.0001" min="0" max="1">
                                @error('export_tax')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Giá thuế đất -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="land_tax_price">Giá thuế đất (VND)</label>
                                <input type="number" class="form-control @error('land_tax_price') is-invalid @enderror" 
                                       id="land_tax_price" name="land_tax_price" value="{{ old('land_tax_price') }}"
                                       min="0" placeholder="Nhập giá thuế đất">
                                @error('land_tax_price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Diện tích -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Thông tin diện tích</label>
                                <div class="row">
                                    <div class="col-8">
                                        <label for="area_value">Diện tích <span class="text-danger">*</span></label>
                                        <input type="number" step="0.00001" class="form-control @error('area_value') is-invalid @enderror" 
                                               id="area_value" name="area_value" value="{{ old('area_value') }}" placeholder="Nhập diện tích">
                                        @error('area_value')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-4">
                                        <label for="area_unit">Đơn vị</label>
                                        <select class="form-control @error('area_unit') is-invalid @enderror" id="area_unit" name="area_unit">
                                            <option value="m2" {{ old('area_unit', 'm2') == 'm2' ? 'selected' : '' }}>m²</option>
                                            <option value="ha" {{ old('area_unit') == 'ha' ? 'selected' : '' }}>hecta</option>
                                            <option value="km2" {{ old('area_unit') == 'km2' ? 'selected' : '' }}>km²</option>
                                        </select>
                                        @error('area_unit')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thời gian thuê -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Thông tin thời gian thuê</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="rental_start_date">Ngày bắt đầu <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('rental_start_date') is-invalid @enderror" 
                                               id="rental_start_date" name="rental_start_date" value="{{ old('rental_start_date') }}">
                                        @error('rental_start_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="rental_end_date">Ngày kết thúc</label>
                                        <input type="date" class="form-control @error('rental_end_date') is-invalid @enderror" 
                                               id="rental_end_date" name="rental_end_date" value="{{ old('rental_end_date') }}">
                                        @error('rental_end_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="rental_years">Số năm thuê</label>
                                        <input type="number" step="0.1" class="form-control @error('rental_years') is-invalid @enderror" 
                                               id="rental_years" name="rental_years" value="{{ old('rental_years') }}" placeholder="Số năm">
                                        @error('rental_years')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Nhập ngày bắt đầu + số năm → ngày kết thúc sẽ tự động tính. 
                                    Hoặc nhập ngày bắt đầu + ngày kết thúc → số năm sẽ tự động tính.
                                </small>
                            </div>
                        </div>
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
                        <i class="fas fa-save"></i> Lưu Hợp đồng
                    </button>
                    <a href="{{ route('admin.land-rental.contracts.index') }}" class="btn btn-secondary">
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
    // Tự động tính toán thời gian thuê
    function calculateRentalPeriod() {
        const startDate = $('#rental_start_date').val();
        const endDate = $('#rental_end_date').val();
        const years = $('#rental_years').val();
        
        if (startDate && years && !endDate) {
            // Tính ngày kết thúc từ ngày bắt đầu + số năm
            const start = new Date(startDate);
            const end = new Date(start);
            end.setFullYear(start.getFullYear() + parseFloat(years));
            $('#rental_end_date').val(end.toISOString().split('T')[0]);
        } else if (startDate && endDate && !years) {
            // Tính số năm từ ngày bắt đầu và ngày kết thúc
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffYears = diffTime / (1000 * 60 * 60 * 24 * 365.25);
            $('#rental_years').val(Math.ceil(diffYears));
        }
    }
    
    // Khi thay đổi ngày bắt đầu hoặc số năm
    $('#rental_start_date, #rental_years').on('change', function() {
        if ($('#rental_start_date').val() && $('#rental_years').val()) {
            $('#rental_end_date').val(''); // Clear end date
            calculateRentalPeriod();
        }
    });

    
    // Validate ngày kết thúc phải sau ngày bắt đầu
    $('#rental_end_date').on('blur', function() {
        const startDate = $('#rental_start_date').val();
        const endDate = $('#rental_end_date').val();
        
        if (startDate && endDate && new Date(endDate) <= new Date(startDate)) {
            alert('Ngày kết thúc phải sau ngày bắt đầu!');
            $(this).val('');
            $('#rental_years').val('');
        }
    });
});
</script>
@endpush
