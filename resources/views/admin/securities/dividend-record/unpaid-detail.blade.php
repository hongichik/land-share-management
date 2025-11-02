@extends('layouts.layout-master')

@section('title', 'Quản lý Cổ tức - Chi tiết Chưa Trả Cổ tức')
@section('page_title', 'Quản lý Cổ tức - Chi tiết Chưa Trả Cổ tức')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách cổ tức chưa nhận - {{ $investorName }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities.dividend-record.unpaid') }}" class="btn btn-sm btn-secondary" title="Quay lại">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table id="dividend-detail-table" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Ngày tạo</th>
                            <th>Tổng cổ phiếu</th>
                            <th>Cổ phiếu đã lưu ký</th>
                            <th>Cổ phiếu chưa lưu ký</th>
                            <th>Giá cổ tức/cổ phiếu</th>
                            <th>Tiền đã lưu ký (Trước thuế)</th>
                            <th>Tiền chưa lưu ký (Trước thuế)</th>
                            <th>Thuế đã lưu ký</th>
                            <th>Thuế chưa lưu ký</th>
                            <th>Tổng tiền (Sau thuế)</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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
    table.table thead th {
        background-color: #f5f5f5;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    
    table.table tbody td {
        padding: 8px;
        font-size: 13px;
    }
    
    .text-right {
        text-align: right;
    }
    
    .text-center {
        text-align: center;
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
$(document).ready(function() {
    var table = $('#dividend-detail-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        columnDefs: [
            { responsivePriority: 1, targets: -1 },
            { className: 'text-right', targets: [2, 3, 4, 5, 6, 7, 8, 9, 10] }
        ],
        ajax: {
            url: "{{ route('admin.securities.dividend-record.unpaid.detail', ['investorId' => $investorId]) }}",
            type: 'GET'
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'payment_date_formatted', name: 'payment_date', orderable: true},
            {data: 'total_shares', name: 'total_shares', orderable: true},
            {data: 'deposited_shares', name: 'deposited_shares', orderable: true},
            {data: 'non_deposited_shares', name: 'non_deposited_shares', orderable: true},
            {data: 'dividend_price', name: 'dividend_price', orderable: true},
            {data: 'deposited_amount', name: 'deposited_amount', orderable: true},
            {data: 'non_deposited_amount', name: 'non_deposited_amount', orderable: true},
            {data: 'deposited_tax', name: 'deposited_tax', orderable: true},
            {data: 'non_deposited_tax', name: 'non_deposited_tax', orderable: true},
            {data: 'total_amount_after_tax', name: 'total_amount_after_tax', orderable: true},
            {data: 'payment_status', name: 'payment_status', orderable: true}
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
</script>
@endpush
