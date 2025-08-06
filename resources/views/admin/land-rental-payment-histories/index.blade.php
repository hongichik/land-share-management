@extends('layouts.layout-master')

@section('title', 'Lịch sử thanh toán')
@section('page_title', 'Lịch sử thanh toán')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Lịch sử thanh toán - Hợp đồng: {{ $landRentalContract->contract_number }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.land-rental-payment-histories.create', $landRentalContract) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Thêm thanh toán
                    </a>
                    <a href="{{ route('admin.land-rental-contracts.show', $landRentalContract) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại hợp đồng
                    </a>
                </div>
            </div>
            
            <!-- Contract Info Summary -->
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Thông tin hợp đồng</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Số hợp đồng:</strong> {{ $landRentalContract->contract_number }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Khu vực:</strong> {{ $landRentalContract->rental_zone ?: 'Chưa có thông tin' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Vị trí:</strong> {{ $landRentalContract->rental_location ?: 'Chưa có thông tin' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Diện tích:</strong> 
                                    @if ($landRentalContract->area && isset($landRentalContract->area['value']))
                                        {{ number_format($landRentalContract->area['value'], 2) }}
                                        {{ $landRentalContract->area['unit'] ?? 'm²' }}
                                    @else
                                        Chưa có thông tin
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="payment-histories-table">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>Kỳ nộp</th>
                                <th>Loại nộp</th>
                                <th>Số tiền</th>
                                <th>Ngày nộp</th>
                                <th>Ghi chú</th>
                                <th width="15%" class="nowrap">Thao tác</th>
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
    $('#payment-histories-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        columnDefs: [
            { responsivePriority: 1, targets: -1 },  // Cột cuối luôn ưu tiên hiển thị
        ],
        ajax: "{{ route('admin.land-rental-payment-histories.index', $landRentalContract) }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'period', name: 'period'},
            {data: 'payment_type', name: 'payment_type'},
            {data: 'amount', name: 'amount'},
            {data: 'payment_date', name: 'payment_date'},
            {data: 'notes', name: 'notes'},
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
