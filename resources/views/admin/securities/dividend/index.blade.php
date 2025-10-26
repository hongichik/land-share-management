@extends('layouts.layout-master')

@section('title', 'Quản lý Chứng khoán - Danh sách Cổ đông')
@section('page_title', 'Quản lý Chứng khoán - Danh sách Cổ đông')

@section('content')
<!-- Summary Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Thống kê tổng quan</h3>
            </div>
            <div class="card-body">
                <div class="row">
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
                            <span class="info-box-icon bg-warning"><i class="fas fa-box"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Chưa lưu ký</span>
                                <span class="info-box-number" id="not-deposited">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-lock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Đã lưu ký</span>
                                <span class="info-box-number" id="deposited">0</span>
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
                    <a href="{{ route('admin.securities.dividend.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Thêm mới
                    </a>
                    <button type="button" class="btn btn-success btn-sm" id="import-btn">
                        <i class="fas fa-upload"></i> Import
                    </button>
                    <input type="file" id="import-investors" accept=".xlsx,.xls,.csv" style="display: none;">
                </div>
            </div>
            <!-- Bộ lọc -->
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-primary filter-btn active" data-filter="all">
                        <i class="fas fa-list"></i> Tất cả
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary filter-btn" data-filter="large">
                        <i class="fas fa-star"></i> Cổ đông lớn (≥5%)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary filter-btn" data-filter="small">
                        <i class="fas fa-user"></i> Cổ đông nhỏ (<5%)
                    </button>
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
                                <th>Số lượng lưu ký</th>
                                <th>Phân loại</th>
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
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" href="{{ asset('css/custom-admin.css') }}">
<style>
    .import-item {
        padding: 10px;
        margin-bottom: 10px;
        border-left: 4px solid #007bff;
        background-color: #f8f9fa;
        border-radius: 3px;
    }
    .import-item.insert {
        border-left-color: #28a745;
        background-color: #f1f8f5;
    }
    .import-item.update {
        border-left-color: #ffc107;
        background-color: #fff8f0;
    }
    .change-item {
        margin-left: 20px;
        margin-top: 8px;
        padding: 8px 10px;
        font-size: 12px;
        background-color: #ffffff;
        border-radius: 3px;
        border-left: 3px solid #ffc107;
    }
    .change-old {
        color: #dc3545;
        display: block;
        margin-bottom: 4px;
    }
    .change-new {
        color: #28a745;
        display: block;
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
var currentImportFile = null;

$(document).ready(function() {
    var currentFilter = 'all';
    
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
            {data: 'group3_deposited', name: 'group3_deposited', orderable: false, searchable: false},
            {data: 'group5_classification', name: 'group5_classification', orderable: false, searchable: false},
            {data: 'group6_bank', name: 'group6_bank', orderable: false, searchable: false},
            {data: 'group7_notes', name: 'group7_notes', orderable: false, searchable: false},
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
    
    // Xử lý bộ lọc
    $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('active').addClass('btn-outline-primary').removeClass('btn-primary');
        $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
        
        currentFilter = $(this).data('filter');
        table.ajax.reload();
        
        loadSummaryStats(currentFilter);
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
        
        var formData = new FormData();
        formData.append('file', currentImportFile);
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
                html += '<div class="import-item insert"><strong>✓ Thêm mới:</strong> ' + item.full_name + '</div>';
            } else if (item.type === 'update') {
                html += '<div class="import-item update"><strong>⟳ Cập nhật:</strong> ' + item.full_name + '</div>';
            }
        });
    }
    
    $('#preview-content').html(html);
    $('#importPreviewModal').modal('show');
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
            $('#not-deposited').text(data.not_deposited);
            $('#deposited').text(data.deposited);
        }
    });
}

// Delete functions
let deleteId = null;

function deleteRecord(id) {
    deleteId = id;
    $('#deleteModal').modal('show');
}

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
</script>
@endpush
