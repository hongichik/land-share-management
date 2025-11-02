@extends('layouts.layout-master')

@section('title', 'Master Admin Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<!-- Hero Welcome Section -->
<div class="row">
    <div class="col-12">
        <div class="hero-section">
            <div class="hero-content">
                <div class="hero-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <h1 class="hero-title">Chào mừng đến với Master Admin</h1>
                <p class="hero-subtitle">Bảng điều khiển quản trị hệ thống</p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Bảo mật cao</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-bolt"></i>
                        <span>Hiệu suất tối ưu</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Thống kê realtime</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feature Cards -->
<div class="row mt-4">
    <div class="col-lg-3 col-md-6">
        <div class="feature-card feature-card-primary">
            <div class="feature-icon">
                <i class="fas fa-users-cog"></i>
            </div>
            <div class="feature-content">
                <h3>Quản lý người dùng</h3>
                <p>Kiểm soát toàn bộ hệ thống người dùng</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="feature-card feature-card-success">
            <div class="feature-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="feature-content">
                <h3>Quản lý dữ liệu</h3>
                <p>Xử lý và phân tích dữ liệu hệ thống</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="feature-card feature-card-info">
            <div class="feature-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="feature-content">
                <h3>Báo cáo & Thống kê</h3>
                <p>Phân tích chi tiết hiệu suất hệ thống</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="feature-card feature-card-warning">
            <div class="feature-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="feature-content">
                <h3>Cấu hình hệ thống</h3>
                <p>Tùy chỉnh và cài đặt hệ thống</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title">
                    <i class="fas fa-rocket mr-2"></i>
                    Thao tác nhanh
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="quick-action-btn">
                            <div class="action-icon bg-primary">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <span>Thêm Admin</span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="quick-action-btn">
                            <div class="action-icon bg-success">
                                <i class="fas fa-file-export"></i>
                            </div>
                            <span>Xuất dữ liệu</span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="quick-action-btn">
                            <div class="action-icon bg-info">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <span>Đồng bộ</span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="quick-action-btn">
                            <div class="action-icon bg-warning">
                                <i class="fas fa-tools"></i>
                            </div>
                            <span>Bảo trì</span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="quick-action-btn">
                            <div class="action-icon bg-danger">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <span>Bảo mật</span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="quick-action-btn">
                            <div class="action-icon bg-purple">
                                <i class="fas fa-palette"></i>
                            </div>
                            <span>Giao diện</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Status -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title">
                    <i class="fas fa-server mr-2"></i>
                    Trạng thái hệ thống
                </h3>
            </div>
            <div class="card-body">
                <div class="system-status">
                    <div class="status-item">
                        <div class="status-icon status-online">
                            <i class="fas fa-circle"></i>
                        </div>
                        <div class="status-info">
                            <h5>Server Web</h5>
                            <span class="text-success">Hoạt động bình thường</span>
                        </div>
                    </div>
                    <div class="status-item">
                        <div class="status-icon status-online">
                            <i class="fas fa-circle"></i>
                        </div>
                        <div class="status-info">
                            <h5>Cơ sở dữ liệu</h5>
                            <span class="text-success">Kết nối ổn định</span>
                        </div>
                    </div>
                    <div class="status-item">
                        <div class="status-icon status-warning">
                            <i class="fas fa-circle"></i>
                        </div>
                        <div class="status-info">
                            <h5>Bộ nhớ cache</h5>
                            <span class="text-warning">Sử dụng 78%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title">
                    <i class="fas fa-clock mr-2"></i>
                    Thông tin session
                </h3>
            </div>
            <div class="card-body text-center">
                <div class="session-info">
                    <div class="session-time">
                        <h2 id="current-time">--:--:--</h2>
                        <p class="text-muted">Thời gian hiện tại</p>
                    </div>
                    <div class="session-details">
                        <small class="text-muted">
                            Phiên làm việc: <strong>{{ now()->format('d/m/Y') }}</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 60px 40px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 30px;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

.hero-title {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 15px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.hero-subtitle {
    font-size: 1.2rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1rem;
    opacity: 0.9;
}

/* Feature Cards */
.feature-card {
    background: white;
    border-radius: 15px;
    padding: 30px 20px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    margin-bottom: 20px;
    border: none;
    height: 100%;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.feature-card-primary { border-top: 4px solid #007bff; }
.feature-card-success { border-top: 4px solid #28a745; }
.feature-card-info { border-top: 4px solid #17a2b8; }
.feature-card-warning { border-top: 4px solid #ffc107; }

.feature-icon {
    font-size: 3rem;
    margin-bottom: 20px;
    color: #667eea;
}

.feature-card-primary .feature-icon { color: #007bff; }
.feature-card-success .feature-icon { color: #28a745; }
.feature-card-info .feature-icon { color: #17a2b8; }
.feature-card-warning .feature-icon { color: #ffc107; }

.feature-content h3 {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}

.feature-content p {
    color: #666;
    font-size: 0.9rem;
}

/* Modern Card */
.modern-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.modern-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    padding: 20px;
}

/* Quick Actions */
.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.quick-action-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
}

.action-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-bottom: 10px;
}

.bg-purple { background-color: #6f42c1; }

.quick-action-btn span {
    font-size: 0.9rem;
    font-weight: 500;
    text-align: center;
}

/* System Status */
.system-status {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
}

.status-icon {
    font-size: 1.2rem;
}

.status-online { color: #28a745; }
.status-warning { color: #ffc107; }
.status-error { color: #dc3545; }

.status-info h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.status-info span {
    font-size: 0.85rem;
}

/* Session Info */
.session-info {
    padding: 20px;
}

.session-time h2 {
    font-size: 2.5rem;
    font-weight: bold;
    color: #667eea;
    margin-bottom: 5px;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-stats {
        gap: 15px;
    }
    
    .stat-item {
        font-size: 0.9rem;
    }
    
    .feature-card {
        margin-bottom: 15px;
    }
}

@media (max-width: 576px) {
    .hero-section {
        padding: 40px 20px;
    }
    
    .hero-title {
        font-size: 1.8rem;
    }
    
    .hero-icon {
        font-size: 3rem;
    }
}

/* Animation */
.feature-card, .quick-action-btn, .status-item {
    animation: fadeInUp 0.6s ease forwards;
}

.feature-card:nth-child(2) { animation-delay: 0.1s; }
.feature-card:nth-child(3) { animation-delay: 0.2s; }
.feature-card:nth-child(4) { animation-delay: 0.3s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Update current time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('vi-VN');
        $('#current-time').text(timeString);
    }
    
    updateTime();
    setInterval(updateTime, 1000);
    
    // Add click animations to quick action buttons
    $('.quick-action-btn').on('click', function() {
        $(this).addClass('animate__animated animate__pulse');
        setTimeout(() => {
            $(this).removeClass('animate__animated animate__pulse');
        }, 1000);
    });
    
    console.log('Master Admin Dashboard loaded successfully');
});
</script>
@endpush