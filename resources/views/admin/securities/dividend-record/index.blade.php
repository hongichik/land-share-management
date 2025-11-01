@extends('layouts.layout-master')

@section('title', 'Quản lý Cổ tức')
@section('page_title', 'Quản lý Cổ tức')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách Cổ tức</h3>
            </div>

            <!-- Bộ lọc -->
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label mb-2" style="font-weight: 600; font-size: 14px;">Chọn năm:</label>
                        <select id="filter-year" class="form-select form-select-sm">
                            <!-- Options sẽ được thêm bởi JavaScript -->
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
                <table id="dividend-records-table" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Thời gian trả</th>
                            <th>Số lượng cổ phiếu</th>
                            <th>Tỷ lệ cổ tức</th>
                            <th>Tổng thuế TNCT</th>
                            <th>Tổng tiền (Trước thuế)</th>
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
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa tất cả dữ liệu cổ tức cho lần trả này?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
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

$(document).ready(function() {
    var currentYear = new Date().getFullYear();
    var selectedYear = currentYear;
    
    // Tạo danh sách năm (từ 5 năm trước đến 5 năm sau hiện tại)
    function initializeYearFilter() {
        var $yearSelect = $('#filter-year');
        for (var i = currentYear - 5; i <= currentYear + 5; i++) {
            $yearSelect.append($('<option></option>').val(i).text(i));
        }
        $yearSelect.val(currentYear); // Chọn năm hiện tại mặc định
    }
    
    initializeYearFilter();
    
    var table = $('#dividend-records-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('admin.securities.dividend-record.index') }}",
            type: 'GET',
            data: function(data) {
                data.year = selectedYear;
                return data;
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'payment_date_formatted', name: 'payment_date', orderable: true},
            {data: 'total_shares_formatted', name: 'total_shares', orderable: true, className: 'text-right'},
            {data: 'dividend_percentage_formatted', name: 'dividend_percentage', orderable: true, className: 'text-center'},
            {data: 'tax_info', name: 'tax_info', orderable: false, searchable: false},
            {data: 'total_amount_formatted', name: 'total_amount_before_tax', orderable: true, className: 'text-right'},
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
    
    // Xử lý thay đổi bộ lọc năm
    $('#filter-year').change(function() {
        selectedYear = $(this).val();
        table.ajax.reload();
    });
    
    // Xử lý reset bộ lọc
    $('#reset-filters').click(function() {
        $('#filter-year').val(currentYear);
        selectedYear = currentYear;
        table.ajax.reload();
    });
});

let deletePaymentDate = null;

function deleteRecord(paymentDate) {
    deletePaymentDate = paymentDate;
    $('#deleteModal').modal('show');
}

$('#confirmDelete').click(function() {
    if (deletePaymentDate) {
        $.ajax({
            url: "{{ route('admin.securities.dividend-record.destroy', '') }}/" + deletePaymentDate,
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
    
    $('#exportForm').submit();
    $('#exportModal').modal('hide');
});
</script>
@endpush
