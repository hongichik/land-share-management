@extends('layouts.layout-master')

@section('title', 'Quản lý Chứng khoán - Danh sách Cổ đông')
@section('page_title', 'Quản lý Chứng khoán - Danh sách Cổ đông')

@section('content')
<!-- Summary Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Thống kê tổng quan (Năm {{ date('Y') }})</h3>
            </div>
            <div class="card-body">
                <!-- Hàng 1: Thông tin cấp cao -->
                <div class="row mb-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tổng số cổ đông</span>
                                <span class="info-box-number" id="total-investors">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cổ đông hoạt động</span>
                                <span class="info-box-number" id="active-investors">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tổng thuế</span>
                                <span class="info-box-number" id="tax-total" style="font-size: 18px;">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hàng 2: Chi tiết thuế -->
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="card card-sm">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-coins"></i> Thuế theo loại lưu ký</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div style="text-align: center; padding: 10px; border: 1px solid #e3e6f0; border-radius: 4px; margin-bottom: 10px;">
                                            <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Thuế (Chưa LK)</div>
                                            <div style="font-size: 18px; font-weight: bold; color: #e74c3c;" id="tax-unsigned">0</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div style="text-align: center; padding: 10px; border: 1px solid #e3e6f0; border-radius: 4px; margin-bottom: 10px;">
                                            <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Thuế (Đã LK)</div>
                                            <div style="font-size: 18px; font-weight: bold; color: #3498db;" id="tax-signed">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hàng 2 cột 2: Chi tiết tiền -->
                    <div class="col-lg-6">
                        <div class="card card-sm">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-dollar-sign"></i> Tổng tiền theo trạng thái</h5>
                            </div>
                            <div class="card-body">
                                <div class="row" style="font-size: 13px;">
                                    <div class="col-6">
                                        <div style="padding: 8px; border: 1px solid #e3e6f0; border-radius: 4px; margin-bottom: 8px;">
                                            <div style="color: #666; margin-bottom: 3px;">Chưa LK - Chưa nhận</div>
                                            <div style="font-weight: bold; color: #e74c3c;" id="amount-unsigned-unpaid">0</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div style="padding: 8px; border: 1px solid #e3e6f0; border-radius: 4px; margin-bottom: 8px;">
                                            <div style="color: #666; margin-bottom: 3px;">Đã LK - Chưa nhận</div>
                                            <div style="font-weight: bold; color: #3498db;" id="amount-signed-unpaid">0</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div style="padding: 8px; border: 1px solid #e3e6f0; border-radius: 4px;">
                                            <div style="color: #666; margin-bottom: 3px;">Chưa LK - Đã nhận</div>
                                            <div style="font-weight: bold; color: #27ae60;" id="amount-unsigned-paid">0</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div style="padding: 8px; border: 1px solid #e3e6f0; border-radius: 4px;">
                                            <div style="color: #666; margin-bottom: 3px;">Đã LK - Đã nhận</div>
                                            <div style="font-weight: bold; color: #f39c12;" id="amount-signed-paid">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách Cổ đông</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities.dividend-record.index') }}" class="btn btn-warning btn-sm" title="Xem danh sách đợt trả cổ tức">
                        <i class="bi bi-calendar2-check"></i> Danh sách đợt trả cổ tức
                    </a>
                    <a href="{{ route('admin.securities.dividend-record-payment.index') }}" class="btn btn-success btn-sm" title="Xem nhận tiền cổ tức">
                        <i class="bi bi-cash-coin"></i> Nhận tiền cổ tức
                    </a>
                    <a href="{{ route('admin.securities.dividend.payment') }}" class="btn btn-info btn-sm" title="Thanh toán cổ tức cho cổ đông">
                        <i class="fas fa-money-bill-wave"></i> Thanh toán cổ tức
                    </a>
                    <button type="button" class="btn btn-success btn-sm" id="import-btn">
                        <i class="fas fa-upload"></i> Import
                    </button>
                    <input type="file" id="import-investors" accept=".xlsx,.xls,.csv" style="display: none;">
                </div>
            </div>
            <!-- Bộ lọc -->
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label mb-2" style="font-weight: 600; font-size: 14px;">Tình trạng lưu ký:</label>
                        <select id="filter-signed" class="form-select form-select-sm filter-select">
                            <option value="">-- Tất cả --</option>
                            <option value="signed">Đã lưu ký</option>
                            <option value="unsigned">Chưa lưu ký</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-2" style="font-weight: 600; font-size: 14px;">Tình trạng thanh toán:</label>
                        <select id="filter-payment" class="form-select form-select-sm filter-select">
                            <option value="">-- Tất cả --</option>
                            <option value="unpaid">Chưa thanh toán cả 2</option>
                            <option value="unpaid_paid_not_deposited">Chưa thanh toán (Chưa LK)</option>
                            <option value="unpaid_paid_deposited">Chưa thanh toán (Đã LK)</option>
                            <option value="paid_not_deposited">Đã thanh toán (Chưa LK)</option>
                            <option value="paid_deposited">Đã thanh toán (Đã LK)</option>
                            <option value="paid_both">Đã thanh toán (Cả 2)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-2" style="font-weight: 600; font-size: 14px;">Tùy chọn:</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="reset-filters">
                            <i class="fas fa-redo"></i> Reset bộ lọc
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="securities-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;">STT</th>
                                <th>Thông tin cá nhân</th>
                                <th>Thông tin đầu tư</th>
                                <th>Cổ tức chưa nhận</th>
                                <th>Ngân hàng</th>
                                <th>Ghi chú</th>
                                <th style="width: 120px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa dữ liệu này?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Import Preview Modal -->
<div class="modal fade" id="importPreviewModal" tabindex="-1" role="dialog" aria-labelledby="importPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importPreviewModalLabel">Xem trước dữ liệu Import</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <div class="row w-100">
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label for="dividendDate" class="mb-2">Ngày trả cổ tức:</label>
                            <input type="date" class="form-control" id="dividendDate">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label for="dividendPrice" class="mb-2">Giá cổ tức/cổ phần (VNĐ):</label>
                            <input type="number" class="form-control" id="dividendPrice" value="10000" min="0" step="100">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                <div id="preview-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="confirmImport">Xác nhận Import</button>
            </div>
        </div>
    </div>
</div>

<!-- Bank Edit Modal -->
<div class="modal fade" id="bankEditModal" tabindex="-1" role="dialog" aria-labelledby="bankEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bankEditModalLabel">Sửa thông tin ngân hàng</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="investorName">Cổ đông:</label>
                    <input type="text" class="form-control" id="investorName" readonly>
                </div>
                <div class="form-group">
                    <label for="bankName">Tên ngân hàng:</label>
                    <select class="form-control" id="bankName" style="width: 100%;">
                        <option value="">-- Chọn ngân hàng --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bankAccount">Tài khoản:</label>
                    <input type="text" class="form-control" id="bankAccount" placeholder="Nhập số tài khoản">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="saveBankInfo">Lưu</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<link rel="stylesheet" href="{{ asset('css/custom-admin.css') }}">
<style>
    /* Modal header close button fix */
    .modal-header .close {
        color: #000;
        opacity: 1;
        text-shadow: none;
    }
    .modal-header .close:hover {
        opacity: 0.8;
    }
    
    .import-item {
        padding: 12px;
        margin-bottom: 12px;
        border-left: 4px solid #007bff;
        background-color: #f8f9fa;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .import-item.insert {
        border-left-color: #28a745;
        background-color: #f1f8f5;
    }
    .import-item.update {
        border-left-color: #ffc107;
        background-color: #fff8f0;
    }
    .import-item > div:first-child {
        margin-bottom: 8px;
        font-weight: 500;
    }
    .change-item {
        margin-left: 20px;
        margin-top: 10px;
        padding: 10px 12px;
        font-size: 13px;
        background-color: #ffffff;
        border-radius: 4px;
        border-left: 3px solid #ffc107;
        line-height: 1.5;
    }
    .change-item strong {
        display: block;
        margin-bottom: 6px;
        color: #333;
    }
    .change-old {
        color: #dc3545;
        display: inline;
    }
    .change-new {
        color: #28a745;
        display: inline;
        font-weight: 500;
    }
    .import-item.insert .change-item {
        border-left-color: #28a745;
        background-color: #f0faf7;
    }
    .import-item.update .change-item {
        border-left-color: #0066cc;
        background-color: #f0f5ff;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
var currentImportFile = null;

$(document).ready(function() {
    var currentFilter = 'all';
    
    // Initialize Select2 for bank selection (use dropdownParent so it works inside modal)
    $('#bankName').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Chọn ngân hàng --',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0,
        dropdownParent: $('#bankEditModal'),
        ajax: {
            url: "{{ route('admin.securities.dividend.get-banks-list') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term || ''
                };
            },
            processResults: function(data) {
                // ensure proper format and return
                return {
                    results: data.results || []
                };
            },
            cache: true
        }
    });
    
    var table = $('#securities-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        columnDefs: [
            { responsivePriority: 1, targets: -1 },  
        ],
        ajax: function(data, callback, settings) {
            data.filter = currentFilter;
            $.get("{{ route('admin.securities.dividend.index') }}", data, function(res) {
                callback(res);
            });
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'group1_personal', name: 'group1_personal', orderable: false, searchable: false},
            {data: 'group2_investor', name: 'group2_investor', orderable: false, searchable: false},
            {data: 'group3_unpaid_dividend', name: 'group3_unpaid_dividend', orderable: false, searchable: false},
            {data: 'group5_bank', name: 'group5_bank', orderable: false, searchable: false},
            {data: 'group6_notes', name: 'group6_notes', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        language: {
            processing: "Đang xử lý...",
            lengthMenu: "Hiển thị _MENU_ mục",
            zeroRecords: "Không tìm thấy dữ liệu",
            info: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
            infoEmpty: "Hiển thị 0 đến 0 của 0 mục",
            infoFiltered: "(được lọc từ _MAX_ mục)",
            search: "Tìm kiếm:",
            paginate: {
                first: "Đầu tiên",
                last: "Cuối cùng",
                next: "Tiếp theo",
                previous: "Trước đó"
            }
        }
    });
    
    // Xử lý bộ lọc từ dropdown
    function updateFilters() {
        var signedFilter = $('#filter-signed').val();
        var paymentFilter = $('#filter-payment').val();
        
        // Cập nhật các option thanh toán dựa trên bộ lọc lưu ký
        updatePaymentOptions(signedFilter);
        
        var filters = [];
        if (signedFilter) filters.push(signedFilter);
        if (paymentFilter) filters.push(paymentFilter);
        
        currentFilter = filters.length > 0 ? filters.join(',') : 'all';
        table.ajax.reload();
        loadSummaryStats(currentFilter);
    }
    
    // Hàm cập nhật các option thanh toán
    function updatePaymentOptions(signedFilter) {
        var $paymentSelect = $('#filter-payment');
        var paymentOptions = {
            '': '-- Tất cả --',
            'unpaid': 'Chưa thanh toán cả 2',
            'unpaid_paid_not_deposited': 'Chưa thanh toán (Chưa LK)',
            'unpaid_paid_deposited': 'Chưa thanh toán (Đã LK)',
            'paid_not_deposited': 'Đã thanh toán (Chưa LK)',
            'paid_deposited': 'Đã thanh toán (Đã LK)',
            'paid_both': 'Đã thanh toán (Cả 2)'
        };
        
        var filteredOptions = {};
        
        if (signedFilter === 'unsigned') {
            // Chỉ hiển thị trạng thái thanh toán của "Chưa LK"
            filteredOptions = {
                '': '-- Tất cả --',
                'unpaid_paid_not_deposited': 'Chưa thanh toán',
                'paid_not_deposited': 'Đã thanh toán'
            };
        } else if (signedFilter === 'signed') {
            // Chỉ hiển thị trạng thái thanh toán của "Đã LK"
            filteredOptions = {
                '': '-- Tất cả --',
                'unpaid_paid_deposited': 'Chưa thanh toán',
                'paid_deposited': 'Đã thanh toán'
            };
        } else {
            // Hiển thị tất cả
            filteredOptions = paymentOptions;
        }
        
        // Lưu giá trị hiện tại
        var currentValue = $paymentSelect.val();
        
        // Xóa tất cả option
        $paymentSelect.empty();
        
        // Thêm các option mới
        $.each(filteredOptions, function(value, text) {
            $paymentSelect.append($('<option></option>').val(value).text(text));
        });
        
        // Nếu giá trị cũ không tồn tại trong list mới, reset về ""
        if (!filteredOptions.hasOwnProperty(currentValue)) {
            $paymentSelect.val('');
        } else {
            $paymentSelect.val(currentValue);
        }
    }
    
    $('.filter-select').change(function() {
        updateFilters();
    });
    
    // Reset bộ lọc
    $('#reset-filters').click(function() {
        $('#filter-signed').val('');
        $('#filter-payment').val('');
        updatePaymentOptions(''); // Reset payment options về mặc định
        currentFilter = 'all';
        table.ajax.reload();
        loadSummaryStats('all');
    });
    
    // Load initial stats
    loadSummaryStats('all');
    
    // Import button
    $('#import-btn').click(function() {
        $('#import-investors').click();
    });
    
    // Xử lý import file
    $('#import-investors').on('change', function() {
        var formData = new FormData();
        formData.append('file', this.files[0]);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        currentImportFile = this.files[0];
        
        toastr.info('Đang phân tích file...', 'Vui lòng chờ');
        
        $.ajax({
            url: "{{ route('admin.securities.dividend.import-preview') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                showImportPreview(response);
            },
            error: function(xhr) {
                toastr.error('Lỗi: ' + (xhr.responseJSON?.error || 'Không thể xử lý file'));
            }
        });
    });
    
    // Xử lý confirm import
    $('#confirmImport').click(function() {
        if (!currentImportFile) {
            toastr.error('Vui lòng chọn file trước');
            return;
        }
        
        // Validate dividend inputs
        var dividendDate = $('#dividendDate').val();
        var dividendPrice = $('#dividendPrice').val();
        
        if (!dividendDate) {
            toastr.error('Vui lòng nhập ngày thanh toán cổ tức');
            return;
        }
        
        if (!dividendPrice || parseFloat(dividendPrice) <= 0) {
            toastr.error('Vui lòng nhập mức cổ tức mỗi cổ phiếu (> 0)');
            return;
        }
        
        var formData = new FormData();
        formData.append('file', currentImportFile);
        formData.append('payment_date', dividendDate);
        formData.append('dividend_price_per_share', dividendPrice);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');
        
        $.ajax({
            url: "{{ route('admin.securities.dividend.import-confirm') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                toastr.success('Import dữ liệu thành công!');
                $('#importPreviewModal').modal('hide');
                table.ajax.reload();
                loadSummaryStats('all');
            },
            error: function(xhr) {
                toastr.error('Lỗi: ' + (xhr.responseJSON?.error || 'Không thể import'));
            },
            complete: function() {
                $('#confirmImport').prop('disabled', false).html('<i class="fas fa-upload"></i> Xác nhận Import');
            }
        });
    });
});

// Hiển thị preview import
function showImportPreview(response) {
    var insertCount = response.insertCount || 0;
    var updateCount = response.updateCount || 0;
    var preview = response.preview || [];
    
    $('#insert-count').html('<span class="badge badge-success">' + insertCount + ' Thêm mới</span>');
    $('#update-count').html('<span class="badge badge-warning">' + updateCount + ' Cập nhật</span>');
    
    var html = '';
    
    if (insertCount === 0 && updateCount === 0) {
        html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong>Không có dữ liệu thay đổi.</strong></div>';
    } else if (preview.length === 0) {
        html = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> <strong>Cảnh báo!</strong> Không thể phân tích được dữ liệu từ file.</div>';
    } else {
        $.each(preview, function(index, item) {
            if (item.type === 'insert') {
                html += '<div class="import-item insert">';
                html += '<div><strong>✓ Thêm mới nhà đầu tư:</strong> ' + item.full_name + ' (SID: ' + (item.sid || 'N/A') + ')</div>';
                
                // Hiển thị dữ liệu SecuritiesManagement
                if (item.data && Object.keys(item.data).length > 0) {
                    html += '<div class="change-item"><strong>Thông tin nhà đầu tư:</strong>';
                    $.each(item.data, function(field, value) {
                        html += '<br>• <span style="color: #0066cc;">' + field + '</span>: ' + value;
                    });
                    html += '</div>';
                }
                
                // Hiển thị dữ liệu DividendRecord
                if (item.dividend_record) {
                    html += '<div class="change-item" style="border-left-color: #28a745;"><strong>📊 Bản ghi Cổ tức sẽ tạo:</strong>';
                    html += '<br>• Chứng khoán chưa lưu ký: <span class="change-new">' + (item.dividend_record.non_deposited_shares_quantity || 0) + '</span>';
                    html += '<br>• Chứng khoán đã lưu ký: <span class="change-new">' + (item.dividend_record.deposited_shares_quantity || 0) + '</span>';
                    html += '<br>• Tiền thanh toán trước thuế (chưa LK): <span class="change-new">' + formatNumber(item.dividend_record.non_deposited_amount_before_tax || 0) + '</span>';
                    html += '<br>• Tiền thanh toán trước thuế (đã LK): <span class="change-new">' + formatNumber(item.dividend_record.deposited_amount_before_tax || 0) + '</span>';
                    html += '<br>• Thuế thu nhập cá nhân (chưa LK): <span class="change-new">' + formatNumber(item.dividend_record.non_deposited_personal_income_tax || 0) + '</span>';
                    html += '<br>• Thuế thu nhập cá nhân (đã LK): <span class="change-new">' + formatNumber(item.dividend_record.deposited_personal_income_tax || 0) + '</span>';
                    html += '</div>';
                }
                
                html += '</div>';
            } else if (item.type === 'update') {
                html += '<div class="import-item update">';
                html += '<div><strong>⟳ Cập nhật nhà đầu tư:</strong> ' + item.full_name + ' (ID: ' + item.id + ', SID: ' + (item.sid || 'N/A') + ')</div>';
                
                // Hiển thị thay đổi SecuritiesManagement
                if (item.changes && Object.keys(item.changes).length > 0) {
                    html += '<div class="change-item"><strong>Thông tin nhà đầu tư thay đổi:</strong>';
                    $.each(item.changes, function(field, change) {
                        html += '<br>• <span style="color: #0066cc;">' + field + '</span>:';
                        html += '<span class="change-old"> từ: ' + (change.old || 'trống') + '</span>';
                        html += '<span class="change-new"> → thành: ' + (change.new || 'trống') + '</span>';
                    });
                    html += '</div>';
                }
                
                // Hiển thị dữ liệu DividendRecord
                if (item.dividend_record) {
                    html += '<div class="change-item" style="border-left-color: #28a745;"><strong>📊 Bản ghi Cổ tức sẽ tạo:</strong>';
                    html += '<br>• Chứng khoán chưa lưu ký: <span class="change-new">' + (item.dividend_record.non_deposited_shares_quantity || 0) + '</span>';
                    html += '<br>• Chứng khoán đã lưu ký: <span class="change-new">' + (item.dividend_record.deposited_shares_quantity || 0) + '</span>';
                    html += '<br>• Tiền thanh toán trước thuế (chưa LK): <span class="change-new">' + formatNumber(item.dividend_record.non_deposited_amount_before_tax || 0) + '</span>';
                    html += '<br>• Tiền thanh toán trước thuế (đã LK): <span class="change-new">' + formatNumber(item.dividend_record.deposited_amount_before_tax || 0) + '</span>';
                    html += '<br>• Thuế thu nhập cá nhân (chưa LK): <span class="change-new">' + formatNumber(item.dividend_record.non_deposited_personal_income_tax || 0) + '</span>';
                    html += '<br>• Thuế thu nhập cá nhân (đã LK): <span class="change-new">' + formatNumber(item.dividend_record.deposited_personal_income_tax || 0) + '</span>';
                    html += '</div>';
                }
                
                html += '</div>';
            }
        });
    }
    
    $('#preview-content').html(html);
    $('#importPreviewModal').modal('show');
}

// Hàm format số
function formatNumber(num) {
    return new Intl.NumberFormat('vi-VN').format(num);
}

// Hàm load thống kê
function loadSummaryStats(filter) {
    $.ajax({
        url: "{{ route('admin.securities.dividend.summary-stats') }}",
        type: 'GET',
        data: { filter: filter },
        success: function(data) {
            $('#total-investors').text(data.total_investors);
            $('#active-investors').text(data.active_investors);
            $('#tax-unsigned').text(data.tax_unsigned + ' đ');
            $('#tax-signed').text(data.tax_signed + ' đ');
            $('#tax-total').text(data.tax_total + ' đ');
            $('#amount-unsigned-unpaid').text(data.amount_unsigned_unpaid + ' đ');
            $('#amount-signed-unpaid').text(data.amount_signed_unpaid + ' đ');
            $('#amount-unsigned-paid').text(data.amount_unsigned_paid + ' đ');
            $('#amount-signed-paid').text(data.amount_signed_paid + ' đ');
        }
    });
}

// Delete functions
let deleteId = null;

function deleteRecord(id) {
    deleteId = id;
    $('#deleteModal').modal('show');
}

// Handle modal show event to properly initialize Select2
$('#bankEditModal').on('shown.bs.modal', function () {
    // Trigger Select2 to recalculate its position
    $('#bankName').select2('open').select2('close');
});

$('#confirmDelete').click(function() {
    if (deleteId) {
        $.ajax({
            url: "{{ route('admin.securities.dividend.destroy', '') }}/" + deleteId,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success(response.message);
                $('#deleteModal').modal('hide');
                $('#securities-table').DataTable().ajax.reload();
            },
            error: function(xhr) {
                toastr.error('Lỗi: ' + (xhr.responseJSON?.message || 'Không thể xóa'));
            }
        });
    }
});

// Bank edit functions
let currentBankEditId = null;

function viewDividendDetails(investorId, investorName) {
    window.location.href = `/admin/securities/dividend/${investorId}/dividend-details`;
}

function editBankInfo(id, fullName, bankName, bankAccount) {
    currentBankEditId = id;
    $('#investorName').val(fullName);
    $('#bankAccount').val(bankAccount);
    
    // Clear and reset Select2
    var $bankSelect = $('#bankName');
    $bankSelect.val(null).trigger('change');

    // If there's an existing bank value, add it as an option (id/text) and select it
    if (bankName && bankName.trim() !== '') {
        // bankName parameter might be stored as full text or code; use it for both id and text if id not available
        var optionValue = bankName;
        var optionText = bankName;

        // If the option with this id doesn't exist, append it
        if (!$bankSelect.find("option[value='" + optionValue + "']").length) {
            var newOption = new Option(optionText, optionValue, true, true);
            $bankSelect.append(newOption).trigger('change');
        } else {
            $bankSelect.val(optionValue).trigger('change');
        }
    }
    
    // Show modal and focus on the bank field
    $('#bankEditModal').modal('show');
    
    // Auto-open the dropdown after modal is shown
    setTimeout(function() {
        $bankSelect.select2('open');
    }, 300);
}

$('#saveBankInfo').click(function() {
    if (!currentBankEditId) {
        toastr.error('Lỗi: Không tìm thấy ID');
        return;
    }

    var bankName = $('#bankName').val().trim();
    var bankAccount = $('#bankAccount').val().trim();

    if (!bankName || !bankAccount) {
        toastr.warning('Vui lòng điền đầy đủ thông tin ngân hàng');
        return;
    }

    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');

    $.ajax({
        url: `/admin/securities/dividend/${currentBankEditId}/update-bank`,
        type: 'PUT',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            bank_name: bankName,
            bank_account: bankAccount
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#bankEditModal').modal('hide');
                $('#securities-table').DataTable().ajax.reload();
            } else {
                toastr.error(response.message || 'Cập nhật thất bại');
            }
        },
        error: function(xhr) {
            var errorMsg = 'Không thể cập nhật thông tin ngân hàng';
            if (xhr.responseJSON?.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseJSON?.error) {
                errorMsg = xhr.responseJSON.error;
            }
            toastr.error('Lỗi: ' + errorMsg);
        },
        complete: function() {
            $('#saveBankInfo').prop('disabled', false).html('Lưu');
        }
    });
});
</script>
@endpush
