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
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-excel"></i> Xuất Excel
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="{{ route('admin.land-rental.contracts.export') }}" class="dropdown-item">
                                <i class="fas fa-list mr-2"></i> Danh sách hợp đồng
                            </a>
                            
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#exportRentalPlanModal">
                                <i class="fas fa-calendar mr-2"></i> Kế hoạch nộp tiền thuê đất
                            </a>
                            
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#exportTaxPlanModal">
                                <i class="fas fa-file-invoice-dollar mr-2"></i> Kế hoạch nộp thuế PNN
                            </a>
                            
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#exportNonAgriTaxModal">
                                <i class="fas fa-calculator mr-2"></i> Bảng tính thuế SDD PNN
                            </a>
                            
                            
                            <!-- Replace the embedded form with a button that opens a modal -->
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#exportTaxCalculationModal">
                                <i class="fas fa-file-invoice-dollar mr-2"></i> Bảng tính tiền thuê đất
                            </a>
                            
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#exportSupplementalPaymentModal">
                                <i class="fas fa-receipt mr-2"></i> Bảng tính tiền nộp bổ sung
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('admin.land-rental.contracts.create') }}" class="btn btn-primary btn-sm ml-1">
                        <i class="bi bi-plus"></i> Thêm Hợp đồng
                    </a>
                    <button type="button" class="btn btn-warning btn-sm ml-1" data-toggle="modal" data-target="#payAllModal">
                        <i class="fas fa-money-bill-wave"></i> Thanh toán tất cả
                    </button>
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

<!-- Excel Export Modal -->
<div class="modal fade" id="exportExcelModal" tabindex="-1" role="dialog" aria-labelledby="exportExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="exportExcelModalLabel"><i class="fas fa-file-excel mr-2"></i>Xuất báo cáo Excel</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Export Full List Option -->
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-list mr-2"></i>Danh sách hợp đồng</h6>
                                <p class="text-muted small">Xuất tất cả danh sách hợp đồng hiện có</p>
                                <a href="{{ route('admin.land-rental.contracts.export') }}" class="btn btn-outline-success btn-sm btn-block">
                                    <i class="fas fa-download mr-1"></i> Xuất danh sách
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Export Tax Calculation Option -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-calculator mr-2"></i>Bảng tính tiền thuê đất</h6>
                                <form action="{{ route('admin.land-rental.contracts.export-tax-calculation') }}" method="get">
                                    <div class="form-group">
                                        <label>Kỳ thanh toán</label>
                                        <select name="period" class="form-control">
                                            <option value="1">Kỳ I (Tháng 1-6)</option>
                                            <option value="2">Kỳ II (Tháng 7-12)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Năm</label>
                                        <select name="year" class="form-control">
                                            @php
                                                $currentYear = (int)date('Y');
                                                $startYear = $currentYear - 2;
                                                $endYear = $currentYear + 2;
                                            @endphp
                                            @for($year = $startYear; $year <= $endYear; $year++)
                                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-download mr-1"></i> Xuất báo cáo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Plan Modal -->
<div class="modal fade" id="exportRentalPlanModal" tabindex="-1" role="dialog" aria-labelledby="exportRentalPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="exportRentalPlanModalLabel"><i class="fas fa-file-excel mr-2"></i>Xuất Kế Hoạch Nộp Tiền Thuê Đất</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.land-rental.contracts.export-rental-plan') }}" method="get">
                    <div class="form-group">
                        <label>Năm kế hoạch</label>
                        <select name="year" class="form-control">
                            @php
                                $currentYear = (int)date('Y');
                                $startYear = $currentYear - 2;
                                $endYear = $currentYear + 2;
                            @endphp
                            @for($year = $startYear; $year <= $endYear; $year++)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-download mr-1"></i> Xuất kế hoạch
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Tax Plan Modal -->
<div class="modal fade" id="exportTaxPlanModal" tabindex="-1" role="dialog" aria-labelledby="exportTaxPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="exportTaxPlanModalLabel"><i class="fas fa-file-excel mr-2"></i>Xuất Kế Hoạch Nộp Thuế PNN</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.land-rental.contracts.export-tax-plan') }}" method="get">
                    <div class="form-group">
                        <label>Năm kế hoạch</label>
                        <select name="year" class="form-control">
                            @php
                                $currentYear = (int)date('Y');
                                $startYear = $currentYear - 2;
                                $endYear = $currentYear + 2;
                            @endphp
                            @for($year = $startYear; $year <= $endYear; $year++)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-download mr-1"></i> Xuất kế hoạch thuế
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Non-Agricultural Land Tax Modal -->
<div class="modal fade" id="exportNonAgriTaxModal" tabindex="-1" role="dialog" aria-labelledby="exportNonAgriTaxModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="exportNonAgriTaxModalLabel"><i class="fas fa-file-excel mr-2"></i>Xuất Bảng Tính Thuế SDD PNN</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.land-rental.contracts.export-non-agri-tax') }}" method="get">
                    <div class="form-group">
                        <label>Năm tính thuế</label>
                        <select name="year" class="form-control">
                            @php
                                $currentYear = (int)date('Y');
                                $startYear = $currentYear - 2;
                                $endYear = $currentYear + 2;
                            @endphp
                            @for($year = $startYear; $year <= $endYear; $year++)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-download mr-1"></i> Xuất bảng tính
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Tax Calculation Modal -->
<div class="modal fade" id="exportTaxCalculationModal" tabindex="-1" role="dialog" aria-labelledby="exportTaxCalculationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="exportTaxCalculationModalLabel"><i class="fas fa-file-excel mr-2"></i>Xuất Bảng Tính Tiền Thuê Đất</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.land-rental.contracts.export-tax-calculation') }}" method="get">
                    <div class="form-group">
                        <label>Kỳ thanh toán</label>
                        <select name="period" class="form-control">
                            <option value="1">Kỳ I (Tháng 1-6)</option>
                            <option value="2">Kỳ II (Tháng 7-12)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Năm</label>
                        <select name="year" class="form-control">
                            @php
                                $currentYear = (int)date('Y');
                                $startYear = $currentYear - 2;
                                $endYear = $currentYear + 2;
                            @endphp
                            @for($year = $startYear; $year <= $endYear; $year++)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-download mr-1"></i> Xuất bảng tính
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Supplemental Payment Modal -->
<div class="modal fade" id="exportSupplementalPaymentModal" tabindex="-1" role="dialog" aria-labelledby="exportSupplementalPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="exportSupplementalPaymentModalLabel"><i class="fas fa-file-excel mr-2"></i>Xuất Bảng Tính Tiền Nộp Bổ Sung</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.land-rental.contracts.export-supplemental-payment') }}" method="get">
                    <div class="form-group">
                        <label>Năm</label>
                        <select name="year" class="form-control">
                            @php
                                $currentYear = (int)date('Y');
                                $startYear = $currentYear - 2;
                                $endYear = $currentYear + 2;
                            @endphp
                            @for($year = $startYear; $year <= $endYear; $year++)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="btn btn-info btn-block">
                        <i class="fas fa-download mr-1"></i> Xuất bảng tính
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Pay All Modal -->
<div class="modal fade" id="payAllModal" tabindex="-1" role="dialog" aria-labelledby="payAllModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="payAllModalLabel"><i class="fas fa-money-bill-wave mr-2"></i>Thanh toán tất cả</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="payAllForm" action="{{ route('admin.land-rental.contracts.pay-all') }}" method="POST">
                    @csrf
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Chú ý:</strong> Hành động này sẽ cập nhật trạng thái thanh toán cho tất cả hợp đồng trong kỳ và năm được chọn.
                    </div>
                    <div class="form-group">
                        <label for="payPeriod">Kỳ thanh toán <span class="text-danger">*</span></label>
                        <select id="payPeriod" name="period" class="form-control" required>
                            <option value="">-- Chọn kỳ --</option>
                            <option value="1">Kỳ I (Tháng 1-6)</option>
                            <option value="2">Kỳ II (Tháng 7-12)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="payYear">Năm <span class="text-danger">*</span></label>
                        <select id="payYear" name="year" class="form-control" required>
                            <option value="">-- Chọn năm --</option>
                            @php
                                $currentYear = (int)date('Y');
                                $startYear = $currentYear - 2;
                                $endYear = $currentYear + 2;
                            @endphp
                            @for($year = $startYear; $year <= $endYear; $year++)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="paymentDate">Ngày thanh toán <span class="text-danger">*</span></label>
                        <input type="date" id="paymentDate" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-warning" onclick="confirmPayAll()">
                    <i class="fas fa-check mr-1"></i> Xác nhận thanh toán
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="{{ asset('css/custom-admin.css') }}">
<style>
    .modal-header.bg-success {
        color: white;
    }
    #exportExcelModal .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        transition: all 0.3s;
    }
    #exportExcelModal .card:hover {
        box-shadow: 0 0 8px rgba(0,0,0,.2);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script>
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

// Function to confirm pay all
function confirmPayAll() {
    const period = $('#payPeriod').val();
    const year = $('#payYear').val();
    const paymentDate = $('#paymentDate').val();
    
    if (!period || !year || !paymentDate) {
        Swal.fire('Lỗi', 'Vui lòng chọn kỳ, năm và ngày thanh toán', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Xác nhận thanh toán',
        html: `Bạn có chắc chắn muốn thanh toán tất cả hợp đồng trong:<br>
               <strong>Kỳ ${period === '1' ? 'I (Tháng 1-6)' : 'II (Tháng 7-12)'} năm ${year}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('payAllForm').submit();
        }
    });
}

$(document).ready(function() {
    $('#contracts-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        columnDefs: [
            { responsivePriority: 1, targets: -1 },  
        ],
        ajax: "{{ route('admin.land-rental.contracts.index') }}",
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
});
</script>
@endpush
