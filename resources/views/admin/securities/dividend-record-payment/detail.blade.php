@extends('layouts.layout-master')

@section('title', 'Quản lý Cổ tức - Chi tiết Thanh toán')
@section('page_title', 'Quản lý Cổ tức - Chi tiết Thanh toán')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('admin.securities.dividend-record-payment.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Chi tiết thanh toán cổ tức - {{ $paymentDateFormatted }}</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" onclick="exportDetail()" title="Xuất Excel">
                        <i class="fas fa-download"></i> Xuất Excel
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table id="dividend-detail-table" class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Tên nhà đầu tư</th>
                            <th>Mã nhà đầu tư</th>
                            <th>Tổng cổ phiếu</th>
                            <th>Đã lưu ký</th>
                            <th>Chưa lưu ký</th>
                            <th>Tiền (Đã lưu ký)</th>
                            <th>Tiền (Chưa lưu ký)</th>
                            <th>Thuế (Đã lưu ký)</th>
                            <th>Thuế (Chưa lưu ký)</th>
                            <th>Giá cổ phiếu</th>
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
        ajax: {
            url: "{{ route('admin.securities.dividend-record-payment.detail', ['paymentDate' => $paymentDate]) }}",
            type: 'GET'
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'investor_name', name: 'investor_name', orderable: true, searchable: true},
            {data: 'investor_code', name: 'investor_code', orderable: true, searchable: true},
            {data: 'total_shares', name: 'total_shares', orderable: true, className: 'text-right'},
            {data: 'deposited_shares', name: 'deposited_shares', orderable: true, className: 'text-right'},
            {data: 'non_deposited_shares', name: 'non_deposited_shares', orderable: true, className: 'text-right'},
            {data: 'deposited_amount', name: 'deposited_amount', orderable: true, className: 'text-right'},
            {data: 'non_deposited_amount', name: 'non_deposited_amount', orderable: true, className: 'text-right'},
            {data: 'deposited_tax', name: 'deposited_tax', orderable: true, className: 'text-right'},
            {data: 'non_deposited_tax', name: 'non_deposited_tax', orderable: true, className: 'text-right'},
            {data: 'dividend_price', name: 'dividend_price', orderable: true, className: 'text-right'},
            {data: 'payment_status', name: 'payment_status', orderable: true, className: 'text-center'}
        ],
        order: [[2, 'asc']],
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

function exportDetail() {
    const transferDate = "{{ $paymentDate }}";
    // Gửi AJAX request để kiểm tra dữ liệu trước khi export
    $.ajax({
        url: "{{ route('admin.securities.dividend-record-payment.export-detail', '') }}/" + transferDate,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            // Nếu thành công, tải file
            window.location.href = "{{ route('admin.securities.dividend-record-payment.export-detail', '') }}/" + transferDate;
        },
        error: function(xhr) {
            let errorMsg = 'Lỗi khi xuất file';
            if (xhr.responseJSON?.message) {
                errorMsg = xhr.responseJSON.message;
            }
            toastr.error(errorMsg);
        }
    });
}
</script>
@endpush
