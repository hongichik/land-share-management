@extends('layouts.layout-master')

@section('title', 'Chi tiết cổ tức - ' . $investor->full_name)
@section('page_title', 'Chi tiết cổ tức')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice-dollar"></i> Chi tiết cổ tức - <strong>{{ $investor->full_name }}</strong>
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.securities.dividend.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Thông tin cổ đông -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h5 class="card-title">Thông tin cổ đông</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Mã NĐT:</strong> {{ $investor->investor_code ?? 'N/A' }}</p>
                                <p><strong>SID:</strong> {{ $investor->sid ?? 'N/A' }}</p>
                                <p><strong>Email:</strong> {{ $investor->email ?? 'N/A' }}</p>
                                <p><strong>Điện thoại:</strong> {{ $investor->phone ?? 'N/A' }}</p>
                                <p><strong>Địa chỉ:</strong> {{ $investor->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h5 class="card-title">Thông tin chứng khoán</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Cổ phiếu chưa LK:</strong> {{ number_format($investor->not_deposited_quantity ?? 0) }}</p>
                                <p><strong>Cổ phiếu đã LK:</strong> {{ number_format($investor->deposited_quantity ?? 0) }}</p>
                                <p><strong>Tổng cộng:</strong> <span style="color: #28a745; font-weight: bold;">{{ number_format(($investor->not_deposited_quantity ?? 0) + ($investor->deposited_quantity ?? 0)) }}</span></p>
                                <p><strong>Ngân hàng:</strong> {{ $investor->bank_name ?? 'N/A' }}</p>
                                <p><strong>Tài khoản:</strong> {{ $investor->bank_account ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thống kê -->
                @if(!$dividendRecords->isEmpty())
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Đã thanh toán</span>
                                    <span class="info-box-number" id="paid-total">0 đ</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Chưa thanh toán</span>
                                    <span class="info-box-number" id="unpaid-total">0 đ</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tổng cổ tức</span>
                                    <span class="info-box-number" id="total-dividend">0 đ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Danh sách cổ tức (Bảng) -->
                @if($dividendRecords->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Không có dữ liệu cổ tức</strong>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 40px;">STT</th>
                                    <th>Ngày trả</th>
                                    <th>Trạng thái</th>
                                    <th>Cổ phiếu chưa LK</th>
                                    <th>Cổ phiếu đã LK</th>
                                    <th>Tiền trước thuế</th>
                                    <th>Thuế TNHH</th>
                                    <th style="text-align: right; color: #ffc107;"><strong>Net (sau thuế)</strong></th>
                                    <th style="width: 80px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dividendRecords as $index => $record)
                                    @php
                                        $paymentDate = $record->payment_date;
                                        $formattedDate = \Carbon\Carbon::parse($paymentDate)->format('d/m/Y');
                                        
                                        // Tính tổng tiền theo loại
                                        $nonDepositedAmount = ($record->non_deposited_amount_before_tax ?? 0);
                                        $depositedAmount = ($record->deposited_amount_before_tax ?? 0);
                                        $totalBeforeTax = $nonDepositedAmount + $depositedAmount;
                                        
                                        $nonDepositedTax = ($record->non_deposited_personal_income_tax ?? 0);
                                        $depositedTax = ($record->deposited_personal_income_tax ?? 0);
                                        $totalTax = $nonDepositedTax + $depositedTax;
                                        
                                        $totalAfterTax = $totalBeforeTax - $totalTax;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $index + 1 }}</strong></td>
                                        <td><strong>{{ $formattedDate }}</strong></td>
                                        <td>
                                            @if($record->payment_status === 'paid_not_deposited')
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Đã trả (chưa LK)</span>
                                            @elseif($record->payment_status === 'paid_deposited')
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Đã trả (đã LK)</span>
                                            @else
                                                <span class="badge badge-warning"><i class="fas fa-hourglass-half"></i> Chưa trả</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($record->non_deposited_shares_quantity ?? 0) }}</td>
                                        <td>{{ number_format($record->deposited_shares_quantity ?? 0) }}</td>
                                        <td>{{ number_format($totalBeforeTax, 0, ',', '.') }} đ</td>
                                        <td>{{ number_format($totalTax, 0, ',', '.') }} đ</td>
                                        <td style="text-align: right; color: #0066cc; font-weight: bold;">
                                            {{ number_format($totalAfterTax, 0, ',', '.') }} đ
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" onclick="viewDetails({{ $record->id }}, '{{ $formattedDate }}')" title="Chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Chi tiết -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Chi tiết cổ tức</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Nội dung chi tiết sẽ được load vào đây -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<style>
    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }
    .info-box-number {
        font-size: 1.5rem;
        font-weight: bold;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    // Tính tổng tiền đã thanh toán và chưa thanh toán
    @if(!$dividendRecords->isEmpty())
        let paidTotal = 0;
        let unpaidTotal = 0;
        let totalDividend = 0;
        
        @foreach($dividendRecords as $record)
            @php
                $nonDepositedAmount = ($record->non_deposited_amount_before_tax ?? 0);
                $depositedAmount = ($record->deposited_amount_before_tax ?? 0);
                $totalBeforeTax = $nonDepositedAmount + $depositedAmount;
                
                $nonDepositedTax = ($record->non_deposited_personal_income_tax ?? 0);
                $depositedTax = ($record->deposited_personal_income_tax ?? 0);
                $totalTax = $nonDepositedTax + $depositedTax;
                
                $totalAfterTax = $totalBeforeTax - $totalTax;
            @endphp
            
            totalDividend += {{ $totalAfterTax }};
            @if($record->payment_status === 'paid_not_deposited' || $record->payment_status === 'paid_deposited')
                paidTotal += {{ $totalAfterTax }};
            @else
                unpaidTotal += {{ $totalAfterTax }};
            @endif
        @endforeach
        
        $('#paid-total').text(new Intl.NumberFormat('vi-VN', {style: 'currency', currency: 'VND'}).format(paidTotal));
        $('#unpaid-total').text(new Intl.NumberFormat('vi-VN', {style: 'currency', currency: 'VND'}).format(unpaidTotal));
        $('#total-dividend').text(new Intl.NumberFormat('vi-VN', {style: 'currency', currency: 'VND'}).format(totalDividend));
    @endif
});

function viewDetails(recordId, paymentDate) {
    const detailHtml = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary mb-3"><strong>📦 Cổ phiếu chưa lưu ký</strong></h6>
                <table class="table table-sm table-bordered">
                    <tr>
                        <td style="width: 40%;"><strong>Số lượng:</strong></td>
                        <td id="detail-non-deposited-qty"></td>
                    </tr>
                    <tr>
                        <td><strong>Tiền (trước thuế):</strong></td>
                        <td id="detail-non-deposited-amount"></td>
                    </tr>
                    <tr>
                        <td><strong>Thuế TNHH (10%):</strong></td>
                        <td id="detail-non-deposited-tax"></td>
                    </tr>
                    <tr style="background-color: #f0f0f0;">
                        <td><strong style="color: #0066cc;">Net (sau thuế):</strong></td>
                        <td id="detail-non-deposited-net" style="color: #0066cc; font-weight: bold;"></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-success mb-3"><strong>🔒 Cổ phiếu đã lưu ký</strong></h6>
                <table class="table table-sm table-bordered">
                    <tr>
                        <td style="width: 40%;"><strong>Số lượng:</strong></td>
                        <td id="detail-deposited-qty"></td>
                    </tr>
                    <tr>
                        <td><strong>Tiền (trước thuế):</strong></td>
                        <td id="detail-deposited-amount"></td>
                    </tr>
                    <tr>
                        <td><strong>Thuế TNHH (10%):</strong></td>
                        <td id="detail-deposited-tax"></td>
                    </tr>
                    <tr style="background-color: #f0f0f0;">
                        <td><strong style="color: #0066cc;">Net (sau thuế):</strong></td>
                        <td id="detail-deposited-net" style="color: #0066cc; font-weight: bold;"></td>
                    </tr>
                </table>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4">
                <p><strong>Giá cổ tức/cổ phần:</strong> <span class="text-primary" id="detail-dividend-price"></span></p>
            </div>
            <div class="col-md-4">
                <p><strong>Tỷ lệ cổ tức (%):</strong> <span class="text-primary" id="detail-dividend-percent"></span></p>
            </div>
            <div class="col-md-4">
                <p><strong>Ngày trả:</strong> <span class="text-primary">${paymentDate}</span></p>
            </div>
        </div>
        <div id="transfer-info"></div>
        <div id="bank-info"></div>
        <div id="notes-info"></div>
        <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; margin-top: 15px; text-align: right;">
            <strong style="font-size: 18px; color: #28a745;">
                Tổng cộng: <span id="detail-total"></span>
            </strong>
        </div>
    `;
    
    $('#detailContent').html(detailHtml);
    
    // Lấy dữ liệu từ bảng
    const row = event.target.closest('tr');
    const cells = row.querySelectorAll('td');
    
    // Parse dữ liệu từ các ô
    const nonDepositedQty = cells[3].textContent.trim();
    const depositedQty = cells[4].textContent.trim();
    const beforeTax = cells[5].textContent.trim().replace(' đ', '').replace(/\./g, '');
    const tax = cells[6].textContent.trim().replace(' đ', '').replace(/\./g, '');
    const afterTax = cells[7].textContent.trim().replace(' đ', '').replace(/\./g, '');
    
    // Cập nhật modal
    $('#detail-non-deposited-qty').text(nonDepositedQty);
    $('#detail-deposited-qty').text(depositedQty);
    $('#detail-non-deposited-amount').text(cells[5].textContent.trim());
    $('#detail-deposited-amount').text(cells[5].textContent.trim());
    $('#detail-non-deposited-tax').text(cells[6].textContent.trim());
    $('#detail-deposited-tax').text(cells[6].textContent.trim());
    $('#detail-non-deposited-net').text((parseInt(beforeTax) / 2 - parseInt(tax) / 2).toLocaleString('vi-VN') + ' đ');
    $('#detail-deposited-net').text((parseInt(beforeTax) / 2 - parseInt(tax) / 2).toLocaleString('vi-VN') + ' đ');
    $('#detail-dividend-price').text('10.000 đ');
    $('#detail-dividend-percent').text('1.00%');
    $('#detail-total').text(cells[7].textContent.trim());
    
    $('#detailModal').modal('show');
}
</script>
@endpush
@endsection
