{{-- filepath: /Users/admin/Documents/code/thu_vien/laravel/vendor/hongdev/master-admin/resources/views/master-admin/layout/partials/header.blade.php --}}
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        {{-- Start Navbar Links --}}
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>

        {{-- End Navbar Links --}}
        <ul class="navbar-nav ms-auto">

            {{-- Fullscreen Toggle --}}
            <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none;"></i>
                </a>
            </li>
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                    <img src="{{ asset('/vendor/master-admin/assets/img/user2-160x160.jpg') }}"
                        class="user-image rounded-circle shadow-sm me-2" alt="User Image"
                        style="width: 40px; height: 40px; object-fit: cover;">
                    <span class="d-none d-md-inline fw-semibold">{{ Auth::guard('admin')->user()->name ?? 'User' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-2 shadow">
                    <li class="px-3 py-2 border-bottom">
                        <div class="d-flex align-items-center">
                            <img src="{{ asset('/vendor/master-admin/assets/img/user2-160x160.jpg') }}"
                                class="rounded-circle me-2" alt="User Image"
                                style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <h6 class="mb-0">{{ Auth::guard('admin')->user()->name ?? 'User' }}</h6>
                                <small class="text-muted">Administrator</small>
                            </div>
                        </div>
                    </li>
                    <li class="mt-2">
                        <a href="#" class="dropdown-item d-flex align-items-center">
                            <i class="fas fa-user-circle me-2 text-primary"></i> Hồ sơ cá nhân
                        </a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item d-flex align-items-center">
                            <i class="fas fa-cog me-2 text-secondary"></i> Cài đặt
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a href="{{ route('admin.logout') }}" class="dropdown-item d-flex align-items-center text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</nav>
