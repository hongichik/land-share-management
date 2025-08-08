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
        </ul>
    </div>
</nav>