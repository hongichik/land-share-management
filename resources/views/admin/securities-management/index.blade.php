@extends('layouts.layout-master')

@section('title', 'Quản lý Chứng khoán')
@section('page_title', 'Quản lý Chứng khoán')

@section('content')
<!-- Summary Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-info">
            <div class="card-header">
                <h5 class="card-title text-white"><i class="fas fa-chart-pie"></i> Tổng quan quản lý chứng khoán</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tổng nhà đầu tư</span>
                                <span class="info-box-number" id="total-investors">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Đang hoạt động</span>
                                <span class="info-box-number" id="active-investors">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Chưa lưu ký</span>
                                <span class="info-box-number" id="not-deposited">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="fas fa-shield-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Đã lưu ký</span>
                                <span class="info-box-number" id="deposited">-</span>
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
                <h3 class="card-title">Danh sách quản lý chứng khoán</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities-management.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Thêm nhà đầu tư
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="securities-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên đầy đủ</th>
                                <th>SID</th>
                                <th>Mã nhà đầu tư</th>
                                <th>Số đăng ký</th>
                                <th>Ngày phát hành</th>
                                <th>Số lượng</th>
                                <th>Trạng thái lưu ký</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
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
                Bạn có chắc chắn muốn xóa thông tin nhà đầu tư này không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#securities-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        columnDefs: [
            { responsivePriority: 1, targets: -1 },  
        ],
        ajax: "{{ route('admin.securities-management.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'full_name', name: 'full_name'},
            {data: 'sid', name: 'sid'},
            {data: 'investor_code', name: 'investor_code'},
            {data: 'registration_number', name: 'registration_number'},
            {data: 'issue_date', name: 'issue_date'},
            {data: 'quantities', name: 'quantities', orderable: false, searchable: false},
            {data: 'deposit_badge', name: 'deposit_badge', orderable: false, searchable: false},
            {data: 'status_badge', name: 'status_badge', orderable: false, searchable: false},
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
                first: "Đầu",
                last: "Cuối", 
                next: "Tiếp",
                previous: "Trước"
            }
        }
    });
});

// Delete functions
let deleteId = null;

function deleteRecord(id) {
    deleteId = id;
    $('#deleteModal').modal('show');
}

$('#confirmDelete').click(function() {
    if (deleteId) {
        $.ajax({
            url: "{{ route('admin.securities-management.destroy', ':id') }}".replace(':id', deleteId),
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                $('#securities-table').DataTable().ajax.reload();
                
                if (response.success) {
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                $('#deleteModal').modal('hide');
                toastr.error('Có lỗi xảy ra khi xóa!');
            }
        });
    }
});
$.ajax({
        url: "{{ route('admin.securities-management.summary-stats') }}",
        type: 'GET',
        success: function(data) {
            $('#total-investors').text(data.total_investors);
            $('#active-investors').text(data.active_investors);
            $('#not-deposited').text(data.not_deposited);
            $('#deposited').text(data.deposited);
        },
        error: function() {
            $('#total-investors').text('-');
            $('#active-investors').text('-');
            $('#not-deposited').text('-');
            $('#deposited').text('-');
        }
    });


</script>
@endpush