@extends('layouts.layout-master')

@section('title', 'Quản lý Hợp đồng thuê đất')
@section('page_title', 'Quản lý Hợp đồng thuê đất')

@section('content')
<!-- Payment Status Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tachometer-alt mr-1"></i>
                    Tổng quan thanh toán năm {{ date('Y') }}
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="dashboard-stat bg-gradient-success">
                            <div class="visual">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="details">
                                <div class="number" id="paid-count">
                                    <span>-</span>
                                </div>
                                <div class="desc">Đã thanh toán đủ</div>
                            </div>
                            <a href="#" class="more">
                                Chi tiết <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="dashboard-stat bg-gradient-warning">
                            <div class="visual">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="details">
                                <div class="number" id="warning-count">
                                    <span>-</span>
                                </div>
                                <div class="desc">Cảnh báo sớm</div>
                            </div>
                            <a href="#" class="more">
                                Chi tiết <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="dashboard-stat bg-gradient-danger">
                            <div class="visual">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="details">
                                <div class="number" id="urgent-count">
                                    <span>-</span>
                                </div>
                                <div class="desc">Sắp hết hạn</div>
                            </div>
                            <a href="#" class="more">
                                Chi tiết <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="dashboard-stat bg-gradient-secondary">
                            <div class="visual">
                                <i class="fas fa-ban"></i>
                            </div>
                            <div class="details">
                                <div class="number" id="overdue-count">
                                    <span>-</span>
                                </div>
                                <div class="desc">Quá hạn</div>
                            </div>
                            <a href="#" class="more">
                                Chi tiết <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Progress -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="info-box bg-light elevation-2">
                            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-percentage"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tỷ lệ thanh toán</span>
                                <span class="info-box-number" id="payment-percentage">0%</span>
                                <div class="progress">
                                    <div class="progress-bar bg-success" id="payment-progress" style="width: 0%"></div>
                                </div>
                                <span class="progress-description">
                                    Tỷ lệ hợp đồng đã thanh toán đủ
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light elevation-2">
                            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-bell"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Mức độ khẩn cấp</span>
                                <span class="info-box-number" id="urgent-percentage">0%</span>
                                <div class="progress">
                                    <div class="progress-bar bg-danger" id="urgent-progress" style="width: 0%"></div>
                                </div>
                                <span class="progress-description">
                                    Tỷ lệ hợp đồng cần xử lý gấp
                                </span>
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
                <h3 class="card-title">Danh sách Hợp đồng thuê đất</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.land-rental-contracts.export') }}" class="btn btn-success btn-sm mr-1">
                        <i class="fas fa-file-excel"></i> Xuất Excel
                    </a>
                    <a href="{{ route('admin.land-rental-contracts.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Thêm Hợp đồng
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Warning Legend -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> <strong>Hệ thống cảnh báo thanh toán</strong></h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <span class="deadline-warning warning">⏰ Cảnh báo sớm</span>
                                    <small class="text-muted d-block">Kỳ 1: từ tháng 4 | Kỳ 2: từ tháng 9</small>
                                </div>
                                <div class="col-md-3">
                                    <span class="deadline-warning danger">⚠️ Sắp hết hạn</span>
                                    <small class="text-muted d-block">Trong tháng deadline</small>
                                </div>
                                <div class="col-md-3">
                                    <span class="deadline-warning critical">🚨 Khẩn cấp</span>
                                    <small class="text-muted d-block">Còn 6 ngày cuối</small>
                                </div>
                                <div class="col-md-3">
                                    <span class="payment-status paid">✓ Đã thanh toán đủ</span>
                                    <small class="text-muted d-block">Hoàn thành</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="contracts-table">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>Hợp đồng & Quyết định</th>
                                <th>Khu vực & Vị trí</th>
                                <th>Diện tích & Tiền thuê</th>
                                <th>Thời hạn thuê</th>
                                <th>Thuế & Tiền thuế</th>
                                <th>Thanh toán</th>
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
<link rel="stylesheet" href="{{ asset('css/custom-admin.css') }}">
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
        columnDefs: [
            { responsivePriority: 1, targets: -1 },  
        ],
        ajax: "{{ route('admin.land-rental-contracts.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'contract_and_decision', name: 'contract_number'},
            {data: 'rental_zone', name: 'rental_zone'},
            {data: 'area', name: 'area'},
            {data: 'rental_period', name: 'rental_period'},
            {data: 'land_tax_price', name: 'land_tax_price'},
            {data: 'payment', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        "drawCallback": function(settings) {
            // Update payment status dashboard after table is drawn
            updatePaymentStatusDashboard();
        },
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
    
    // Function to update payment status dashboard
    function updatePaymentStatusDashboard() {
        let paidCount = 0;
        let warningCount = 0;
        let urgentCount = 0;
        let overdueCount = 0;
        
        // Analyze payment column data in the table
        $('#contracts-table tbody tr').each(function() {
            const paymentCell = $(this).find('td:nth-child(7)'); // Payment column (now column 7 instead of 9)
            const paymentHtml = paymentCell.html();
            
            if (paymentHtml) {
                if (paymentHtml.includes('payment-status paid')) {
                    paidCount++;
                } else if (paymentHtml.includes('deadline-warning critical')) {
                    urgentCount++;
                } else if (paymentHtml.includes('deadline-warning danger')) {
                    overdueCount++;
                } else if (paymentHtml.includes('deadline-warning warning')) {
                    warningCount++;
                }
            }
        });
        
        // Update dashboard counters
        $('#paid-count').text(paidCount);
        $('#warning-count').text(warningCount);
        $('#urgent-count').text(urgentCount);
        $('#overdue-count').text(overdueCount);
        
        // Calculate and update percentages
        const totalContracts = paidCount + warningCount + urgentCount + overdueCount;
        if (totalContracts > 0) {
            const paymentPercentage = Math.round((paidCount / totalContracts) * 100);
            const urgentPercentage = Math.round(((urgentCount + overdueCount) / totalContracts) * 100);
            
            $('#payment-percentage').text(paymentPercentage + '%');
            $('#urgent-percentage').text(urgentPercentage + '%');
            $('#payment-progress').css('width', paymentPercentage + '%');
            $('#urgent-progress').css('width', urgentPercentage + '%');
        }
    }
});
</script>
@endpush
