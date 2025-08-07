@extends('layouts.layout-master')

@section('title', 'Tạo thanh toán cổ tức')
@section('page_title', 'Tạo thanh toán cổ tức')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thông tin thanh toán cổ tức</h3>
            </div>
            <form action="{{ route('admin.dividend-payment.store') }}" method="POST" id="dividend-payment-form">
                @csrf
                <div class="card-body">
                    <!-- Common Settings Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <h4>Cấu hình chung</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tax_rate">Thuế suất <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="tax_rate" name="tax_rate" placeholder="Ví dụ: 0.05" step="0.01" min="0" max="1" value="0.05" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Nhập dưới dạng thập phân (0.05 = 5%)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="dividend_per_share">Giá cổ tức mỗi cổ phần <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="dividend_per_share" name="dividend_per_share" placeholder="Ví dụ: 1000" step="1" min="0" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">VNĐ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="payment_date">Ngày thanh toán <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="payment_type">Loại thanh toán <span class="text-danger">*</span></label>
                                <select class="form-control" id="payment_type" name="payment_type" required>
                                    <option value="both">Cả đã lưu ký và chưa lưu ký</option>
                                    <option value="deposited">Chỉ đã lưu ký</option>
                                    <option value="not_deposited">Chỉ chưa lưu ký</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="notes">Ghi chú</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Nhập ghi chú thanh toán nếu cần"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Investor Selection Section -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="form-group">
                                <h4>Chọn nhà đầu tư</h4>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <button type="button" id="select-all-btn" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-check-square"></i> Chọn tất cả
                                        </button>
                                        <button type="button" id="unselect-all-btn" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-square"></i> Bỏ chọn tất cả
                                        </button>
                                    </div>
                                    <div class="input-group" style="width: 300px;">
                                        <input type="text" id="investor-search" class="form-control form-control-sm" placeholder="Tìm kiếm nhà đầu tư...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-sm" id="investors-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;">Chọn</th>
                                                <th>Tên nhà đầu tư</th>
                                                <th>Mã nhà đầu tư</th>
                                                <th>Số đăng ký</th>
                                                <th>Đã lưu ký</th>
                                                <th>Chưa lưu ký</th>
                                                <th>Tổng số</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($investors as $investor)
                                            <tr data-investor-id="{{ $investor->id }}">
                                                <td class="text-center">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="investor_ids[]" id="investor_{{ $investor->id }}" value="{{ $investor->id }}" class="form-check-input investor-checkbox" {{ ($investor->deposited_quantity + $investor->not_deposited_quantity) > 0 ? '' : 'disabled' }}>
                                                        <label class="form-check-label" for="investor_{{ $investor->id }}"></label>
                                                    </div>
                                                </td>
                                                <td>{{ $investor->full_name }}</td>
                                                <td>{{ $investor->investor_code }}</td>
                                                <td>{{ $investor->registration_number }}</td>
                                                <td class="text-right">{{ number_format($investor->deposited_quantity) }}</td>
                                                <td class="text-right">{{ number_format($investor->not_deposited_quantity) }}</td>
                                                <td class="text-right">{{ number_format($investor->deposited_quantity + $investor->not_deposited_quantity) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="form-group">
                                <h4>Xem trước thanh toán</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="payment-preview-table">
                                        <thead>
                                            <tr>
                                                <th>Tên nhà đầu tư</th>
                                                <th>Loại cổ phần</th>
                                                <th>Số lượng</th>
                                                <th>Số tiền trước thuế</th>
                                                <th>Thuế (VNĐ)</th>
                                                <th>Số tiền sau thuế</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="6" class="text-center">Chưa có dữ liệu. Vui lòng chọn nhà đầu tư.</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <th colspan="2">Tổng cộng</th>
                                                <th class="text-right" id="total-shares">0</th>
                                                <th class="text-right" id="total-before-tax">0</th>
                                                <th class="text-right" id="total-tax">0</th>
                                                <th class="text-right" id="total-after-tax">0</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                        <i class="fas fa-save"></i> Lưu thanh toán cổ tức
                    </button>
                    <a href="{{ route('admin.dividend-history.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #investors-table {
        max-height: 400px;
        overflow-y: auto;
    }
    #payment-preview-table {
        max-height: 300px;
        overflow-y: auto;
    }
    .form-check {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
    }
    .form-check-input {
        margin-top: 0;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Variables
    let selectedInvestors = [];
    let investorData = {};
    
    // Initialize
    updatePreviewTable();
    
    // Event Listeners
    $('#select-all-btn').click(function() {
        $('.investor-checkbox:not(:disabled)').prop('checked', true).trigger('change');
    });
    
    $('#unselect-all-btn').click(function() {
        $('.investor-checkbox').prop('checked', false).trigger('change');
    });
    
    $('#investor-search').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#investors-table tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(searchTerm) > -1);
        });
    });
    
    $('.investor-checkbox').on('change', function() {
        const investorId = $(this).val();
        if ($(this).is(':checked')) {
            if (!selectedInvestors.includes(investorId)) {
                selectedInvestors.push(investorId);
            }
        } else {
            selectedInvestors = selectedInvestors.filter(id => id != investorId);
        }
        
        updateSubmitButtonState();
        updatePreviewTable();
    });
    
    $('#tax_rate, #dividend_per_share, #payment_type').on('change', function() {
        updatePreviewTable();
    });
    
    // Functions
    function updateSubmitButtonState() {
        $('#submit-btn').prop('disabled', selectedInvestors.length === 0);
    }
    
    function updatePreviewTable() {
        if (selectedInvestors.length === 0) {
            $('#payment-preview-table tbody').html('<tr><td colspan="6" class="text-center">Chưa có dữ liệu. Vui lòng chọn nhà đầu tư.</td></tr>');
            resetTotals();
            return;
        }
        
        const taxRate = parseFloat($('#tax_rate').val()) || 0;
        const dividendPerShare = parseFloat($('#dividend_per_share').val()) || 0;
        const paymentType = $('#payment_type').val();
        
        $.ajax({
            url: "{{ route('admin.dividend-payment.investor-details') }}",
            method: 'GET',
            data: {
                investor_ids: selectedInvestors
            },
            success: function(response) {
                investorData = response;
                renderPreviewTable(response, taxRate, dividendPerShare, paymentType);
            },
            error: function(error) {
                console.error('Error fetching investor details:', error);
                $('#payment-preview-table tbody').html('<tr><td colspan="6" class="text-center text-danger">Có lỗi xảy ra khi tải dữ liệu.</td></tr>');
                resetTotals();
            }
        });
    }
    
    function renderPreviewTable(investors, taxRate, dividendPerShare, paymentType) {
        let html = '';
        let totalShares = 0;
        let totalBeforeTax = 0;
        let totalTax = 0;
        let totalAfterTax = 0;
        
        investors.forEach(investor => {
            const showDeposited = (paymentType === 'deposited' || paymentType === 'both') && investor.deposited_quantity > 0;
            const showNotDeposited = (paymentType === 'not_deposited' || paymentType === 'both') && investor.not_deposited_quantity > 0;
            
            if (showDeposited) {
                const shares = investor.deposited_quantity;
                const beforeTax = shares * dividendPerShare;
                const tax = beforeTax * taxRate;
                const afterTax = beforeTax - tax;
                
                html += `<tr>
                    <td>${investor.full_name}</td>
                    <td>Đã lưu ký</td>
                    <td class="text-right">${formatNumber(shares)}</td>
                    <td class="text-right">${formatNumber(beforeTax)}</td>
                    <td class="text-right">${formatNumber(tax)}</td>
                    <td class="text-right">${formatNumber(afterTax)}</td>
                </tr>`;
                
                totalShares += shares;
                totalBeforeTax += beforeTax;
                totalTax += tax;
                totalAfterTax += afterTax;
            }
            
            if (showNotDeposited) {
                const shares = investor.not_deposited_quantity;
                const beforeTax = shares * dividendPerShare;
                const tax = beforeTax * taxRate;
                const afterTax = beforeTax - tax;
                
                html += `<tr>
                    <td>${investor.full_name}</td>
                    <td>Chưa lưu ký</td>
                    <td class="text-right">${formatNumber(shares)}</td>
                    <td class="text-right">${formatNumber(beforeTax)}</td>
                    <td class="text-right">${formatNumber(tax)}</td>
                    <td class="text-right">${formatNumber(afterTax)}</td>
                </tr>`;
                
                totalShares += shares;
                totalBeforeTax += beforeTax;
                totalTax += tax;
                totalAfterTax += afterTax;
            }
        });
        
        if (html === '') {
            html = '<tr><td colspan="6" class="text-center">Không có dữ liệu phù hợp với loại thanh toán đã chọn.</td></tr>';
            resetTotals();
        } else {
            $('#total-shares').text(formatNumber(totalShares));
            $('#total-before-tax').text(formatNumber(totalBeforeTax));
            $('#total-tax').text(formatNumber(totalTax));
            $('#total-after-tax').text(formatNumber(totalAfterTax));
        }
        
        $('#payment-preview-table tbody').html(html);
    }
    
    function resetTotals() {
        $('#total-shares').text('0');
        $('#total-before-tax').text('0');
        $('#total-tax').text('0');
        $('#total-after-tax').text('0');
    }
    
    function formatNumber(number) {
        return number.toLocaleString('vi-VN');
    }
});
</script>
@endpush
