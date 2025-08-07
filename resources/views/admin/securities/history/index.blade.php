@extends('layouts.layout-master')

@section('title', 'Lịch sử thanh toán cổ tức')
@section('page_title', 'Lịch sử thanh toán cổ tức')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Bộ lọc</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="year-filter">Năm</label>
                            <select id="year-filter" class="form-control">
                                <option value="">Tất cả các năm</option>
                                @php
                                    $currentYear = date('Y');
                                    for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
                                        echo "<option value='{$year}'>{$year}</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="investor-filter">Nhà đầu tư</label>
                            <select id="investor-filter" class="form-control select2">
                                <option value="">Tất cả nhà đầu tư</option>
                            </select>
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
                <h3 class="card-title">Danh sách thanh toán cổ tức</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities.history.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tạo thanh toán mới
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dividend-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Nhà đầu tư</th>
                                <th>Ngày thanh toán</th>
                                <th>Cổ phần lưu ký</th>
                                <th>Cổ phần chưa lưu ký</th>
                                <th>Tổng cổ phần</th>
                                <th>Tổng tiền (trước thuế)</th>
                                <th>Thuế</th>
                                <th>Thực nhận</th>
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
                Bạn có chắc chắn muốn xóa thanh toán cổ tức này không?
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px;
        line-height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#investor-filter').select2({
        placeholder: 'Chọn nhà đầu tư',
        allowClear: true,
        ajax: {
            url: '{{ route("admin.securities.management.get-investors-list") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.investors.map(function(investor) {
                        return {
                            id: investor.id,
                            text: investor.full_name + ' (' + investor.investor_code + ')'
                        };
                    }),
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    var table = $('#dividend-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('admin.securities.history.index') }}",
            data: function(d) {
                d.year = $('#year-filter').val();
                d.investor_id = $('#investor-filter').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'investor_name', name: 'securitiesManagement.full_name'},
            {
                data: 'payment_date', 
                name: 'payment_date',
                render: function(data, type, row) {
                    if (type === 'display' || type === 'filter') {
                        if (!data) return '';
                        // Convert to DD/MM/YYYY format
                        var date = new Date(data);
                        var day = date.getDate().toString().padStart(2, '0');
                        var month = (date.getMonth() + 1).toString().padStart(2, '0');
                        var year = date.getFullYear();
                        return day + '/' + month + '/' + year;
                    }
                    return data;
                }
            },
            {
                data: 'deposited_shares_quantity', 
                name: 'deposited_shares_quantity',
                render: function(data, type, row) {
                    return data ? number_format(data) : '0';
                }
            },
            {
                data: 'non_deposited_shares_quantity', 
                name: 'non_deposited_shares_quantity',
                render: function(data, type, row) {
                    return data ? number_format(data) : '0';
                }
            },
            {data: 'total_shares_quantity', name: 'total_shares_quantity'},
            {data: 'total_amount', name: 'total_amount', orderable: false, searchable: false},
            {data: 'tax_amount', name: 'tax_amount', orderable: false, searchable: false},
            {data: 'net_amount', name: 'net_amount', orderable: false, searchable: false},
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
    
    // Apply filters when changed
    $('#year-filter, #investor-filter').change(function() {
        table.draw();
    });
    
    // Helper function to format numbers with thousands separators
    function number_format(number) {
        return new Intl.NumberFormat('vi-VN').format(number);
    }
    
    // Delete functionality
    let deleteId = null;

    // Delete functions
    window.deleteRecord = function(id) {
        deleteId = id;
        $('#deleteModal').modal('show');
    }

    $('#confirmDelete').click(function() {
        if (deleteId) {
            $.ajax({
                url: "{{ route('admin.securities.history.destroy', ':id') }}".replace(':id', deleteId),
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#deleteModal').modal('hide');
                    $('#dividend-table').DataTable().ajax.reload();
                    
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    $('#deleteModal').modal('hide');
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Có lỗi xảy ra khi xóa thanh toán cổ tức!');
                    }
                }
            });
        }
    });
});
</script>
@endpush
