@extends('layouts.layout-master')

@section('title', 'Lịch sử thanh toán cổ tức')
@section('page_title', 'Lịch sử thanh toán cổ tức')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách thanh toán cổ tức</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.dividend-payment.create') }}" class="btn btn-primary btn-sm">
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
    $('#dividend-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('admin.dividend-history.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'investor_name', name: 'securitiesManagement.full_name'},
            {data: 'payment_date', name: 'payment_date'},
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
});
</script>
@endpush
