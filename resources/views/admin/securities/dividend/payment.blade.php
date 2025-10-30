@extends('layouts.layout-master')

@section('title', 'Thanh toán cổ tức')
@section('page_title', 'Thanh toán cổ tức')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .search-section {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .search-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }

    .search-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 16px;
    }

    .search-body {
        padding: 20px;
    }

    .investor-card {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #fff;
        transition: all 0.3s ease;
    }

    .investor-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #667eea;
    }

    .investor-card.selected {
        background-color: #e7f3ff;
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }

    .investor-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        margin-right: 15px;
        margin-top: 2px;
    }

    .investor-info {
        flex: 1;
    }

    .investor-name {
        font-weight: 600;
        font-size: 15px;
        color: #333;
        margin-bottom: 5px;
    }

    .investor-detail {
        font-size: 13px;
        color: #666;
        margin: 3px 0;
    }

    .investor-detail strong {
        color: #333;
        min-width: 120px;
        display: inline-block;
    }

    .investor-unpaid {
        font-weight: 700;
        color: #dc3545;
        font-size: 14px;
        margin-top: 8px;
    }

    .investor-row {
        display: flex;
        align-items: flex-start;
        cursor: pointer;
    }

    .selected-count {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px 15px;
        border-radius: 6px;
        font-weight: 600;
        display: inline-block;
    }

    .payment-section {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #28a745;
    }

    .payment-form-group {
        margin-bottom: 20px;
    }

    .payment-form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .btn-confirm-payment {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-confirm-payment:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        color: white;
    }

    .btn-confirm-payment:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .back-link {
        margin-bottom: 20px;
    }

    .back-link a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }

    .back-link a:hover {
        text-decoration: underline;
    }

    .loading-spinner {
        display: none;
        text-align: center;
        padding: 20px;
    }

    .pagination-wrapper {
        text-align: center;
        margin-top: 20px;
    }

    .pagination-info {
        font-size: 13px;
        color: #666;
        margin-bottom: 15px;
    }

    .no-results {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    .no-results i {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .select-all-section {
        padding: 15px;
        background-color: #e7f3ff;
        border-radius: 6px;
        margin-bottom: 15px;
        border-left: 4px solid #667eea;
    }

    .select-all-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        margin-right: 10px;
    }

    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .summary-item {
        display: inline-block;
        margin-right: 30px;
    }

    .summary-label {
        font-size: 12px;
        opacity: 0.9;
        text-transform: uppercase;
    }

    .summary-value {
        font-size: 24px;
        font-weight: 700;
    }

    .selected-investors-section {
        margin: 30px 0;
    }

    .selected-investors-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 15px;
    }

    .selected-investor-card {
        background: #f8f9fa;
        border: 2px solid #667eea;
        border-radius: 8px;
        padding: 15px;
        position: relative;
        transition: all 0.3s ease;
    }

    .selected-investor-card:hover {
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        background: #f0f4ff;
    }

    .selected-investor-info {
        margin-bottom: 10px;
    }

    .selected-investor-name {
        font-weight: 700;
        font-size: 15px;
        color: #333;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }

    .selected-investor-name i {
        margin-right: 8px;
        color: #667eea;
    }

    .selected-investor-detail {
        font-size: 13px;
        color: #666;
        margin-bottom: 5px;
        word-break: break-word;
    }

    .selected-investor-detail strong {
        color: #333;
    }

    .selected-investor-dividend {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .selected-investor-remove {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.3s ease;
        font-size: 14px;
    }

    .selected-investor-remove:hover {
        background: #c82333;
    }

    .selected-investor-remove:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Back Link -->
        <div class="back-link">
            <a href="{{ route('admin.securities.dividend.index') }}">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách nhà đầu tư
            </a>
        </div>

        <!-- Search Section -->
        <div class="search-card">
            <div class="search-header">
                <i class="fas fa-search"></i> Tìm kiếm nhà đầu tư
            </div>
            <div class="search-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="searchType">Tìm kiếm theo:</label>
                            <select id="searchType" class="form-control">
                                <option value="all">Tất cả (Tên, SĐT, SID, Số ĐK)</option>
                                <option value="full_name">Họ và tên</option>
                                <option value="phone">Số điện thoại</option>
                                <option value="sid">SID</option>
                                <option value="registration_number">Số đăng ký</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="searchInput">Từ khóa:</label>
                            <div class="input-group">
                                <input type="text" id="searchInput" class="form-control" placeholder="Nhập từ khóa tìm kiếm...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="searchBtn">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="summary-card" id="summaryCard" style="display: none;">
            <div class="summary-item">
                <div class="summary-label">Đã chọn</div>
                <div class="summary-value"><span id="selectedCount">0</span> người</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Tổng cổ tức chưa nhận</div>
                <div class="summary-value"><span id="totalDividend">0</span> đ</div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-list"></i> Danh sách nhà đầu tư</h5>
            </div>
            <div class="card-body">
                <div id="loadingSpinner" class="loading-spinner">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">Đang tìm kiếm...</p>
                </div>

                <div id="selectAllSection" class="select-all-section" style="display: none;">
                    <input type="checkbox" id="selectAllCheckbox" class="select-all-checkbox">
                    <label for="selectAllCheckbox" style="cursor: pointer; margin-bottom: 0;">
                        Chọn tất cả trên trang này
                    </label>
                </div>

                <div id="investorsList"></div>

                <div id="noResults" class="no-results" style="display: none;">
                    <i class="fas fa-search"></i>
                    <p>Không tìm thấy nhà đầu tư nào. Vui lòng thử lại với từ khóa khác.</p>
                </div>

                <div id="paginationWrapper" class="pagination-wrapper" style="display: none;">
                    <div class="pagination-info">
                        Hiển thị <span id="pageInfo">0</span>
                    </div>
                    <nav>
                        <ul class="pagination justify-content-center" id="pagination">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Selected Investors List Section -->
        <div id="selectedInvestorsSection" class="selected-investors-section" style="display: none;">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-check-square"></i> Danh sách nhà đầu tư đã chọn (<span id="selectedCountHeader">0</span>)</h5>
                </div>
                <div class="card-body">
                    <div id="selectedInvestorsList" class="selected-investors-list">
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Section -->
        <div class="payment-section" id="paymentSection" style="display: none;">
            <h5 class="mb-4"><i class="fas fa-money-bill-wave"></i> Thông tin thanh toán</h5>
            <form id="paymentForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="payment-form-group">
                            <label for="paymentDate">Ngày thanh toán <span class="text-danger">*</span></label>
                            <input type="date" id="paymentDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="payment-form-group">
                            <label for="transferDate">Ngày chuyển khoản</label>
                            <input type="date" id="transferDate" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="payment-form-group">
                    <label for="paymentNotes">Ghi chú</label>
                    <textarea id="paymentNotes" class="form-control" rows="3" placeholder="Nhập ghi chú về thanh toán (nếu có)"></textarea>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-confirm-payment">
                        <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    const API_SEARCH_URL = '{{ route("admin.securities.dividend.payment.search") }}';
    const API_PAYMENT_URL = '{{ route("admin.securities.dividend.payment.process") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';

    let currentPage = 1;
    let selectedInvestors = new Set();
    let allInvestorsData = [];

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        setupEventListeners();
        setTodayDate();
    });

    function setupEventListeners() {
        document.getElementById('searchBtn').addEventListener('click', performSearch);
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') performSearch();
        });

        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            selectAllOnPage(this.checked);
        });

        document.getElementById('paymentForm').addEventListener('submit', submitPayment);
    }

    function setTodayDate() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('paymentDate').value = today;
        document.getElementById('transferDate').value = today;
    }

    function performSearch() {
        const searchTerm = document.getElementById('searchInput').value.trim();
        const searchType = document.getElementById('searchType').value;

        if (!searchTerm && searchType === 'all') {
            toastr.warning('Vui lòng nhập từ khóa tìm kiếm!');
            return;
        }

        currentPage = 1;
        fetchInvestors(searchTerm, searchType, 1);
    }

    function fetchInvestors(searchTerm, searchType, page) {
        document.getElementById('loadingSpinner').style.display = 'block';
        document.getElementById('investorsList').innerHTML = '';
        document.getElementById('noResults').style.display = 'none';
        document.getElementById('paginationWrapper').style.display = 'none';
        document.getElementById('selectAllSection').style.display = 'none';

        fetch(API_SEARCH_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                search: searchTerm,
                search_by: searchType,
                page: page
            })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('loadingSpinner').style.display = 'none';

            if (data.success && data.data.length > 0) {
                allInvestorsData = data.data;
                displayInvestors(data.data);
                setupPagination(data.total, data.perPage, page);
                document.getElementById('selectAllSection').style.display = 'block';
                document.getElementById('selectAllCheckbox').checked = false;
            } else {
                document.getElementById('noResults').style.display = 'block';
            }
        })
        .catch(error => {
            document.getElementById('loadingSpinner').style.display = 'none';
            console.error('Error:', error);
            toastr.error('Lỗi khi tìm kiếm dữ liệu!');
        });
    }

    function displayInvestors(investors) {
        const container = document.getElementById('investorsList');
        container.innerHTML = '';

        investors.forEach(investor => {
            const isSelected = selectedInvestors.has(investor.id);
            const card = document.createElement('div');
            card.className = `investor-card ${isSelected ? 'selected' : ''}`;
            card.dataset.investorId = investor.id;
            card.innerHTML = `
                <div class="investor-row">
                    <input type="checkbox" class="investor-checkbox" data-investor-id="${investor.id}" ${isSelected ? 'checked' : ''}>
                    <div class="investor-info">
                        <div class="investor-name">
                            <i class="fas fa-user-circle"></i> ${escapeHtml(investor.full_name)}
                        </div>
                        <div class="investor-detail">
                            <strong>SĐT:</strong> ${escapeHtml(investor.phone || 'N/A')}
                        </div>
                        <div class="investor-detail">
                            <strong>SID:</strong> ${escapeHtml(investor.sid || 'N/A')}
                        </div>
                        <div class="investor-detail">
                            <strong>Số ĐK:</strong> ${escapeHtml(investor.registration_number || 'N/A')}
                        </div>
                        <div class="investor-detail">
                            <strong>Địa chỉ:</strong> ${escapeHtml(investor.address || 'N/A')}
                        </div>
                        <div class="investor-detail">
                            <strong>Ngân hàng:</strong> ${escapeHtml(investor.bank_name || 'N/A')} - ${escapeHtml(investor.bank_account || 'N/A')}
                        </div>
                        <div class="investor-unpaid">
                            <i class="fas fa-wallet"></i> Cổ tức chưa nhận: ${formatCurrency(investor.unpaid_dividend)} đ
                        </div>
                    </div>
                </div>
            `;

            card.addEventListener('click', function() {
                const checkbox = this.querySelector('.investor-checkbox');
                checkbox.checked = !checkbox.checked;
                toggleInvestorSelection(investor.id, checkbox.checked);
            });

            const checkbox = card.querySelector('.investor-checkbox');
            checkbox.addEventListener('change', function(e) {
                e.stopPropagation();
                toggleInvestorSelection(investor.id, this.checked);
            });

            container.appendChild(card);
        });
    }

    function toggleInvestorSelection(investorId, isSelected) {
        if (isSelected) {
            selectedInvestors.add(investorId);
        } else {
            selectedInvestors.delete(investorId);
        }

        updateUI();
    }

    function selectAllOnPage(selectAll) {
        allInvestorsData.forEach(investor => {
            if (selectAll) {
                selectedInvestors.add(investor.id);
            } else {
                selectedInvestors.delete(investor.id);
            }

            const checkbox = document.querySelector(`input[data-investor-id="${investor.id}"]`);
            if (checkbox) {
                checkbox.checked = selectAll;
            }
        });

        updateUI();
    }

    function updateUI() {
        // Update checkboxes visual state
        document.querySelectorAll('.investor-checkbox').forEach(checkbox => {
            const investorId = parseInt(checkbox.dataset.investorId);
            checkbox.checked = selectedInvestors.has(investorId);
            
            const card = checkbox.closest('.investor-card');
            if (checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });

        // Update summary
        updateSummary();

        // Display selected investors list
        displaySelectedInvestorsList();

        // Show/hide payment section
        if (selectedInvestors.size > 0) {
            document.getElementById('paymentSection').style.display = 'block';
            document.getElementById('summaryCard').style.display = 'block';
            document.getElementById('selectedInvestorsSection').style.display = 'block';
        } else {
            document.getElementById('paymentSection').style.display = 'none';
            document.getElementById('summaryCard').style.display = 'none';
            document.getElementById('selectedInvestorsSection').style.display = 'none';
        }
    }

    function updateSummary() {
        const count = selectedInvestors.size;
        let totalDividend = 0;

        allInvestorsData.forEach(investor => {
            if (selectedInvestors.has(investor.id)) {
                totalDividend += investor.unpaid_dividend;
            }
        });

        document.getElementById('selectedCount').textContent = count;
        document.getElementById('totalDividend').textContent = formatCurrency(totalDividend);
        document.getElementById('selectedCountHeader').textContent = count;
    }

    function displaySelectedInvestorsList() {
        const container = document.getElementById('selectedInvestorsList');
        container.innerHTML = '';

        // Get all selected investors from allInvestorsData
        const allSelectedInvestors = [];
        selectedInvestors.forEach(investorId => {
            const investor = allInvestorsData.find(inv => inv.id === investorId);
            if (investor) {
                allSelectedInvestors.push(investor);
            }
        });

        // Sort by name for consistent display
        allSelectedInvestors.sort((a, b) => a.full_name.localeCompare(b.full_name));

        if (allSelectedInvestors.length === 0) {
            container.innerHTML = '<p class="text-muted text-center" style="padding: 20px;">Chưa chọn nhà đầu tư nào</p>';
            return;
        }

        allSelectedInvestors.forEach(investor => {
            const card = document.createElement('div');
            card.className = 'selected-investor-card';
            card.dataset.investorId = investor.id;
            card.innerHTML = `
                <button type="button" class="selected-investor-remove" title="Bỏ chọn">
                    <i class="fas fa-times"></i>
                </button>
                <div class="selected-investor-info">
                    <div class="selected-investor-name">
                        <i class="fas fa-user-circle"></i>
                        ${escapeHtml(investor.full_name)}
                    </div>
                    <div class="selected-investor-detail">
                        <strong>SĐT:</strong> ${escapeHtml(investor.phone || 'N/A')}
                    </div>
                    <div class="selected-investor-detail">
                        <strong>SID:</strong> ${escapeHtml(investor.sid || 'N/A')}
                    </div>
                    <div class="selected-investor-detail">
                        <strong>Số ĐK:</strong> ${escapeHtml(investor.registration_number || 'N/A')}
                    </div>
                    <div class="selected-investor-dividend">
                        <i class="fas fa-wallet"></i> ${formatCurrency(investor.unpaid_dividend)} đ
                    </div>
                </div>
            `;

            const removeBtn = card.querySelector('.selected-investor-remove');
            removeBtn.addEventListener('click', function() {
                toggleInvestorSelection(investor.id, false);
            });

            container.appendChild(card);
        });
    }

    function setupPagination(total, perPage, currentPage) {
        const totalPages = Math.ceil(total / perPage);
        const container = document.getElementById('pagination');
        container.innerHTML = '';

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${currentPage - 1}); return false;">Trước</a>`;
        container.appendChild(prevLi);

        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            const li = document.createElement('li');
            li.className = 'page-item';
            li.innerHTML = `<a class="page-link" href="#" onclick="goToPage(1); return false;">1</a>`;
            container.appendChild(li);

            if (startPage > 2) {
                const dots = document.createElement('li');
                dots.className = 'page-item disabled';
                dots.innerHTML = `<span class="page-link">...</span>`;
                container.appendChild(dots);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>`;
            container.appendChild(li);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const dots = document.createElement('li');
                dots.className = 'page-item disabled';
                dots.innerHTML = `<span class="page-link">...</span>`;
                container.appendChild(dots);
            }

            const li = document.createElement('li');
            li.className = 'page-item';
            li.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${totalPages}); return false;">${totalPages}</a>`;
            container.appendChild(li);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${currentPage + 1}); return false;">Tiếp</a>`;
        container.appendChild(nextLi);

        // Page info
        document.getElementById('pageInfo').textContent = `${perPage * (currentPage - 1) + 1} - ${Math.min(perPage * currentPage, total)} / Tổng: ${total}`;
        document.getElementById('paginationWrapper').style.display = 'block';
    }

    function goToPage(page) {
        const searchTerm = document.getElementById('searchInput').value.trim();
        const searchType = document.getElementById('searchType').value;
        currentPage = page;
        fetchInvestors(searchTerm, searchType, page);
    }

    function submitPayment(e) {
        e.preventDefault();

        if (selectedInvestors.size === 0) {
            toastr.warning('Vui lòng chọn ít nhất một nhà đầu tư!');
            return;
        }

        const paymentDate = document.getElementById('paymentDate').value;
        const transferDate = document.getElementById('transferDate').value;
        const notes = document.getElementById('paymentNotes').value;

        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

        fetch(API_PAYMENT_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                investor_ids: Array.from(selectedInvestors),
                payment_date: paymentDate,
                transfer_date: transferDate,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Xác nhận thanh toán';

            if (data.success) {
                toastr.success(data.message);
                // Clear selection
                selectedInvestors.clear();
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                toastr.error(data.message || 'Lỗi khi xử lý thanh toán!');
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Xác nhận thanh toán';
            console.error('Error:', error);
            toastr.error('Lỗi khi xử lý thanh toán!');
        });
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('vi-VN').format(value);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
</script>
@endpush
