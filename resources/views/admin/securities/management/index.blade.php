@extends('layouts.layout-master')

@section('title', 'Quản lý Chứng khoán')
@section('page_title', 'Quản lý Chứng khoán')

@section('content')
<!-- Summary Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Tổng quan quản lý chứng khoán
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="dashboard-stat bg-gradient-success">
                            <div class="visual">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="details">
                                <div class="number" id="total-investors">
                                    <span>-</span>
                                </div>
                                <div class="desc">Tổng nhà đầu tư</div>
                            </div>
                            <a href="#" class="more">
                                Chi tiết <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="dashboard-stat bg-gradient-warning">
                            <div class="visual">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="details">
                                <div class="number" id="not-deposited">
                                    <span>-</span>
                                </div>
                                <div class="desc">Chưa lưu ký</div>
                            </div>
                            <a href="#" class="more">
                                Chi tiết <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="dashboard-stat bg-gradient-primary">
                            <div class="visual">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="details">
                                <div class="number" id="deposited">
                                    <span>-</span>
                                </div>
                                <div class="desc">Đã lưu ký</div>
                            </div>
                            <a href="#" class="more">
                                Chi tiết <i class="fas fa-arrow-circle-right"></i>
                            </a>
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
                <h3 class="card-title">Danh sách cổ đông</h3>
                <div class="card-tools">
                    <form id="import-form" enctype="multipart/form-data" style="display:inline-block; margin-right:8px;">
                        @csrf
                        <label for="import-investors" class="btn btn-info btn-sm mb-0">
                            <i class="fas fa-file-import"></i> Import cổ đông
                        </label>
                        <input type="file" id="import-investors" name="file" accept=".xlsx,.xls,.csv" style="display:none">
                    </form>
                    <a href="{{ route('admin.securities.management.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Thêm nhà đầu tư
                    </a>
                </div>
            </div>
            <!-- Bộ lọc -->
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group mb-0">
                            <label for="filter-type" class="font-weight-bold">
                                <i class="fas fa-filter"></i> Lọc theo loại cổ đông:
                            </label>
                            <div class="btn-group" role="group" style="display: inline-flex;">
                                <button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">
                                    <i class="fas fa-list"></i> Tất cả
                                </button>
                                <button type="button" class="btn btn-outline-success filter-btn" data-filter="large" title="Cổ đông có tỷ lệ cổ phần từ 5% trở lên">
                                    <i class="fas fa-chart-line"></i> Cổ đông lớn (≥5%)
                                </button>
                                <button type="button" class="btn btn-outline-warning filter-btn" data-filter="small" title="Cổ đông có tỷ lệ cổ phần dưới 5%">
                                    <i class="fas fa-chart-pie"></i> Cổ đông nhỏ (<5%)
                                </button>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="search-input" class="font-weight-bold">
                                <i class="fas fa-search"></i> Tìm kiếm:
                            </label>
                            <input type="text" id="search-input" class="form-control form-control-sm" 
                                   placeholder="Tìm theo tên, email, phone, SID, mã NĐT, số ĐK...">
                        </div>
                    </div> --}}
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="securities-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th colspan="1">Thông tin cá nhân</th>
                                <th colspan="1">Thông tin đầu tư</th>
                                <th colspan="1">Số lượng lưu ký</th>
                                <th colspan="1">Quyền mua chứng chỉ</th>
                                <th colspan="1">Phân loại</th>
                                <th colspan="1">Ghi chú</th>
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

<!-- Import Preview Modal -->
<div class="modal fade" id="importPreviewModal" tabindex="-1" role="dialog" aria-labelledby="importPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importPreviewModalLabel">
                    <i class="fas fa-eye"></i> Xem trước dữ liệu import
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                <div id="preview-stats" class="alert alert-info">
                    <strong>Thống kê:</strong>
                    <span class="badge badge-success" id="insert-count">0 Thêm mới</span>
                    <span class="badge badge-warning" id="update-count">0 Cập nhật</span>
                </div>
                <div id="preview-content">
                    <!-- Nội dung preview sẽ được thêm ở đây -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="confirmImport">
                    <i class="fas fa-check"></i> Xác nhận import
                </button>
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
    .import-item strong {
        color: #007bff;
    }
    .import-item.insert strong {
        color: #28a745;
    }
    .import-item.update strong {
        color: #ff9800;
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
    .import-item i {
        margin-right: 5px;
        font-size: 16px;
    }
    #preview-content {
        max-height: 600px;
        overflow-y: auto;
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
            $.get("{{ route('admin.securities.management.index') }}", data, function(res) {
                callback(res);
            });
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'group1_personal', name: 'group1_personal', orderable: false, searchable: false},
            {data: 'group2_investor', name: 'group2_investor', orderable: false, searchable: false},
            {data: 'group3_deposited', name: 'group3_deposited', orderable: false, searchable: false},
            {data: 'group4_options', name: 'group4_options', orderable: false, searchable: false},
            {data: 'group5_classification', name: 'group5_classification', orderable: false, searchable: false},
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
                first: "Đầu",
                last: "Cuối", 
                next: "Tiếp",
                previous: "Trước"
            }
        }
    });
    
    // Xử lý bộ lọc
    $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('active').addClass('btn-outline-primary').removeClass('btn-primary');
        $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
        
        currentFilter = $(this).data('filter');
        table.ajax.reload();
        
        // Reload stats với bộ lọc mới
        loadSummaryStats(currentFilter);
    });
    
    // Xử lý tìm kiếm
    
    // Load initial stats
    loadSummaryStats('all');
    
    // Xử lý import file
    $('#import-investors').on('change', function() {
        var formData = new FormData();
        formData.append('file', this.files[0]);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        currentImportFile = this.files[0];
        
        // Show loading
        toastr.info('Đang phân tích file...', 'Vui lòng chờ');
        
        $.ajax({
            url: "{{ route('admin.securities.management.import-preview') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                console.log('Import preview response:', response);
                if (response.success) {
                    showImportPreview(response);
                    toastr.success(response.message);
                } else {
                    toastr.error(response.error || 'Lỗi không xác định');
                    console.error('Import preview error:', response);
                }
            },
            error: function(xhr) {
                console.error('Import preview error:', xhr);
                var error = 'Lỗi xử lý file';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    error = xhr.responseJSON.error;
                }
                toastr.error(error);
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
            url: "{{ route('admin.securities.management.import-confirm') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                $('#importPreviewModal').modal('hide');
                $('#confirmImport').prop('disabled', false).html('<i class="fas fa-check"></i> Xác nhận import');
                
                toastr.success(response.message + ' (' + response.processedRows + ' dòng)');
                
                // Reset file input
                $('#import-investors').val('');
                currentImportFile = null;
                
                // Reload table
                $('#securities-table').DataTable().ajax.reload();
                loadSummaryStats('all');
            },
            error: function(xhr) {
                $('#confirmImport').prop('disabled', false).html('<i class="fas fa-check"></i> Xác nhận import');
                var error = xhr.responseJSON ? xhr.responseJSON.error : 'Lỗi xử lý file';
                toastr.error(error);
            }
        });
    });
});

// Hiển thị preview import
function showImportPreview(response) {
    console.log('Show import preview:', response);
    
    var insertCount = response.insertCount || 0;
    var updateCount = response.updateCount || 0;
    var preview = response.preview || [];
    
    console.log('Insert:', insertCount, 'Update:', updateCount, 'Preview items:', preview.length);
    
    $('#insert-count').html('<span class="badge badge-success">' + insertCount + ' Thêm mới</span>');
    $('#update-count').html('<span class="badge badge-warning">' + updateCount + ' Cập nhật</span>');
    
    var html = '';
    
    if (insertCount === 0 && updateCount === 0) {
        html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong>Không có dữ liệu thay đổi.</strong> File này không chứa bất kỳ nhà đầu tư mới nào hoặc cập nhật nào so với dữ liệu hiện tại.</div>';
    } else if (preview.length === 0) {
        html = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> <strong>Cảnh báo!</strong> Không thể phân tích được dữ liệu từ file. Vui lòng kiểm tra lại định dạng file.</div>';
    } else {
        $.each(preview, function(index, item) {
            console.log('Item ' + index + ':', item);
            if (item.type === 'insert') {
                html += '<div class="import-item insert">' +
                    '<i class="fas fa-plus-circle"></i> <strong>' + (item.full_name || 'N/A') + '</strong>';
                if (item.registration_number) {
                    html += ' <small class="text-muted">(' + item.registration_number + ')</small>';
                }
                html += ' <span class="badge badge-success">Thêm mới</span><br>' +
                    '<small>Sẽ thêm mới nhà đầu tư này vào hệ thống</small>' +
                    '</div>';
            } else if (item.type === 'update') {
                html += '<div class="import-item update">' +
                    '<i class="fas fa-edit"></i> <strong>' + (item.full_name || 'N/A') + '</strong>';
                if (item.registration_number) {
                    html += ' <small class="text-muted">(' + item.registration_number + ')</small>';
                }
                html += ' <span class="badge badge-warning">Cập nhật</span><br>';
                
                if (item.changes && Object.keys(item.changes).length > 0) {
                    $.each(item.changes, function(field, change) {
                        var fieldName = formatFieldName(field);
                        html += '<div class="change-item">' +
                            '<strong>' + fieldName + ':</strong><br>' +
                            '<span class="change-old"><i class="fas fa-times-circle"></i> Cũ: ' + (change.old || '(trống)') + '</span><br>' +
                            '<span class="change-new"><i class="fas fa-check-circle"></i> Mới: ' + (change.new || '(trống)') + '</span>' +
                            '</div>';
                    });
                } else {
                    html += '<small class="text-muted">Không có trường nào thay đổi</small>';
                }
                
                html += '</div>';
            }
        });
    }
    
    $('#preview-content').html(html);
    console.log('Opening modal...');
    $('#importPreviewModal').modal('show');
}

// Format tên field để dễ đọc
function formatFieldName(field) {
    var fieldNames = {
        'full_name': 'Họ tên',
        'address': 'Địa chỉ',
        'email': 'Email',
        'phone': 'Điện thoại',
        'nationality': 'Quốc tịch',
        'sid': 'SID',
        'investor_code': 'Mã nhà đầu tư',
        'registration_number': 'Số đăng ký',
        'issue_date': 'Ngày phát hành',
        'not_deposited_quantity': 'Số lượng chưa lưu ký',
        'deposited_quantity': 'Số lượng đã lưu ký',
        'bank_account': 'Số tài khoản',
        'bank_name': 'Tên ngân hàng'
    };
    return fieldNames[field] || field;
}

// Hàm load thống kê
function loadSummaryStats(filter) {
    $.ajax({
        url: "{{ route('admin.securities.management.summary-stats') }}",
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
            url: "{{ route('admin.securities.management.destroy', ':id') }}".replace(':id', deleteId),
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
</script>
@endpush