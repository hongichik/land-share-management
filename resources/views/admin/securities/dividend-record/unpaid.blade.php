@extends('layouts.layout-master')

@section('title', 'Quản lý Cổ tức - Chưa trả')
@section('page_title', 'Quản lý Cổ tức - Chưa trả')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách Cổ tức - Chưa trả</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities.dividend-record.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table id="unpaid-table" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Tên nhà đầu tư</th>
                            <th>Mã nhà đầu tư</th>
                            <th>Số lượng cổ phiếu</th>
                            <th>Số bản ghi</th>
                            <th>Tỷ lệ cổ tức</th>
                            <th>Tổng thuế TNCT</th>
                            <th>Tổng tiền (Sau thuế)</th>
                            <th style="width: 80px">Hành động</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
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
<script>
$(document).ready(function() {
    $('#unpaid-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('admin.securities.dividend-record.unpaid') }}",
            type: 'GET'
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'investor_name', name: 'investor_name', orderable: false},
            {data: 'investor_code', name: 'investor_code', orderable: false},
            {data: 'total_shares_formatted', name: 'total_shares', orderable: true, className: 'text-right'},
            {data: 'record_count_formatted', name: 'record_count', orderable: true, className: 'text-center'},
            {data: 'dividend_percentage_formatted', name: 'dividend_percentage', orderable: true, className: 'text-center'},
            {data: 'tax_info', name: 'tax_info', orderable: false, searchable: false},
            {data: 'total_amount_after_tax_formatted', name: 'total_amount_after_tax', orderable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[1, 'asc']],
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
