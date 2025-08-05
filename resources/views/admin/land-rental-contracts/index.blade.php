@extends('layouts.layout-master')

@section('title', 'Quản lý Hợp đồng thuê đất')
@section('page_title', 'Quản lý Hợp đồng thuê đất')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách Hợp đồng thuê đất</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.land-rental-contracts.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Thêm Hợp đồng
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="contracts-table">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>Số hợp đồng</th>
                                <th>Khu vực thuê</th>
                                <th>Vị trí thuê</th>
                                <th>Thuế xuất</th>
                                <th>File hợp đồng</th>
                                <th>Ngày tạo</th>
                                <th width="15%">Thao tác</th>
                            </tr>
                        </thead>
                    </table>
                </div>
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
    $('#contracts-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('admin.land-rental-contracts.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'contract_number', name: 'contract_number'},
            {data: 'rental_zone', name: 'rental_zone'},
            {data: 'rental_location', name: 'rental_location'},
            {data: 'export_tax', name: 'export_tax'},
            {data: 'contract_file_path', name: 'contract_file_path'},
            {data: 'created_at', name: 'created_at'},
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
</script>
@endpush
