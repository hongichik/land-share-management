@extends('layouts.layout-master')

@section('title', 'Quản lý Cổ tức - Thanh toán')
@section('page_title', 'Quản lý Cổ tức - Thanh toán')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách thanh toán cổ tức theo ngày</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#exportModal" title="Xuất Excel">
                        <i class="fas fa-download"></i> Xuất Excel
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="card-header" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 12px;">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Năm</label>
                                <select id="filter-year" class="form-control">
                                    <option value="">-- Tất cả --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tháng</label>
                                <select id="filter-month" class="form-control">
                                    <option value="">-- Tất cả --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Ngày</label>
                                <select id="filter-day" class="form-control">
                                    <option value="">-- Tất cả --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-secondary btn-block" id="reset-filters" title="Reset bộ lọc">
                                    <i class="fas fa-redo"></i> Làm mới
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <table id="dividend-records-table" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Ngày thanh toán</th>
                            <th>Số lượng cổ phiếu</th>
                            <th>Tỷ lệ cổ tức</th>
                            <th>Tổng tiền (Trước thuế)</th>
                            <th>Tổng thuế TNCT</th>
                            <th>Tổng tiền (Sau thuế)</th>
                            <th>Số nhà đầu tư</th>
                            <th style="width: 120px">Hành động</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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
                Bạn có chắc chắn muốn xóa tất cả dữ liệu thanh toán cổ tức cho ngày này?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel"><i class="fas fa-file-excel mr-2"></i>Xuất Danh sách thanh toán cổ tức</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="exportForm" action="{{ route('admin.securities.dividend-record-payment.export') }}" method="get">
                    <div class="form-group">
                        <label>Năm</label>
                        <select name="year" class="form-control" id="exportYear" required>
                            <option value="">-- Chọn năm --</option>
                            @php
                                $currentYear = (int)date('Y');
                                $startYear = $currentYear - 5;
                                $endYear = $currentYear + 5;
                            @endphp
                            @for($year = $startYear; $year <= $endYear; $year++)
                                <option value="{{ $year }}" @if($year == $currentYear) selected @endif>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="exportBtn">Xuất Excel</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
    
    .tax-info-container {
        min-width: 150px;
    }
    
    .tax-info-container > div:first-child {
        font-weight: 500;
        margin-bottom: 4px;
    }
    
    .tax-info-container div {
        line-height: 1.4;
    }
    
    .amount-info-container {
        min-width: 150px;
    }
    
    .amount-info-container > div:first-child {
        font-weight: 500;
        margin-bottom: 4px;
    }
    
    .amount-info-container div {
        line-height: 1.4;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
let currentPaymentDate = null;
let selectedYear = '';
let selectedMonth = '';
let selectedDay = '';

$(document).ready(function() {
    initializeFilters();
    
    var table = $('#dividend-records-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('admin.securities.dividend-record-payment.index') }}",
            type: 'GET',
            data: function(data) {
                data.year = selectedYear;
                data.month = selectedMonth;
                data.day = selectedDay;
                return data;
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'payment_date_formatted', name: 'payment_date', orderable: true},
            {data: 'total_shares_formatted', name: 'total_shares', orderable: true, className: 'text-right'},
            {data: 'dividend_percentage_formatted', name: 'dividend_percentage', orderable: true, className: 'text-center'},
            {data: 'total_amount_before_tax', name: 'total_amount_before_tax', orderable: true, className: 'text-right'},
            {data: 'tax_info', name: 'tax_info', orderable: false, searchable: false, className: 'text-right'},
            {data: null, orderable: false, searchable: false, className: 'text-right', render: function(data) {
                // Tính tổng tiền sau thuế = tổng tiền trước thuế - tổng thuế
                let beforeTax = data.total_amount_before_tax_tmp || 0;
                let tax = data.total_tax || 0;
                let afterTax = beforeTax - tax;
                return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(afterTax).replace('₫', 'đ');
            }},
            {data: 'investor_count_formatted', name: 'investor_count', orderable: true, className: 'text-center'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[1, 'desc']],
        language: {
            processing: "Đang xử lý...",
            lengthMenu: "Hiển thị _MENU_ mục",
            zeroRecords: "Không tìm thấy dữ liệu",
            info: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
            infoEmpty: "Hiển thị 0 đến 0 của 0 mục",
            infoFiltered: "(được lọc từ _MAX_ mục)",
            search: "Tìm kiếm:",
            paginate: {
                first: "Đầu",
                last: "Cuối",
                next: "Tiếp",
                previous: "Trước"
            }
        }
    });
});

function initializeFilters() {
    const currentYear = new Date().getFullYear();
    const $yearSelect = $('#filter-year');
    const $monthSelect = $('#filter-month');
    const $daySelect = $('#filter-day');
    
    // Initialize year options
    for (var i = currentYear - 5; i <= currentYear + 5; i++) {
        $yearSelect.append($('<option></option>').val(i).text(i));
    }
    
    // Initialize month options
    const months = ['', 'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                    'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
    for (let i = 1; i <= 12; i++) {
        $monthSelect.append($('<option></option>').val(i).text(months[i]));
    }
    
    // Initialize day options
    for (let i = 1; i <= 31; i++) {
        $daySelect.append($('<option></option>').val(i).text('Ngày ' + i));
    }
    
    // Handle year change
    $yearSelect.change(function() {
        selectedYear = $(this).val();
        selectedMonth = '';
        selectedDay = '';
        $monthSelect.val('');
        $daySelect.val('');
        $('#dividend-records-table').DataTable().ajax.reload();
    });
    
    // Handle month change
    $monthSelect.change(function() {
        selectedMonth = $(this).val();
        selectedDay = '';
        $daySelect.val('');
        $('#dividend-records-table').DataTable().ajax.reload();
    });
    
    // Handle day change
    $daySelect.change(function() {
        selectedDay = $(this).val();
        $('#dividend-records-table').DataTable().ajax.reload();
    });
    
    // Handle reset filters
    $('#reset-filters').click(function() {
        selectedYear = '';
        selectedMonth = '';
        selectedDay = '';
        $yearSelect.val('');
        $monthSelect.val('');
        $daySelect.val('');
        $('#dividend-records-table').DataTable().ajax.reload();
    });
}

let deletePaymentDate = null;

function deleteRecord(paymentDate) {
    deletePaymentDate = paymentDate;
    $('#deleteModal').modal('show');
}

$('#confirmDelete').click(function() {
    if (deletePaymentDate) {
        $.ajax({
            url: "{{ route('admin.securities.dividend-record-payment.destroy', '') }}/" + deletePaymentDate,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#dividend-records-table').DataTable().ajax.reload();
                    $('#deleteModal').modal('hide');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                var errorMsg = 'Không thể xóa dữ liệu';
                if (xhr.responseJSON?.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error('Lỗi: ' + errorMsg);
            }
        });
    }
});

$('#exportBtn').click(function() {
    const year = $('#exportYear').val();
    if (!year) {
        toastr.error('Vui lòng chọn năm');
        return;
    }
    
    // Gửi AJAX request để kiểm tra dữ liệu trước khi export
    $.ajax({
        url: "{{ route('admin.securities.dividend-record-payment.export') }}",
        method: 'GET',
        data: { year: year },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            // Nếu thành công, tải file
            window.location.href = "{{ route('admin.securities.dividend-record-payment.export') }}?year=" + year;
            $('#exportModal').modal('hide');
        },
        error: function(xhr) {
            let errorMsg = 'Lỗi khi xuất file';
            if (xhr.responseJSON?.message) {
                errorMsg = xhr.responseJSON.message;
            }
            toastr.error(errorMsg);
        }
    });
});
</script>
@endpush
