<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)">
    <meta name="description" content="@yield('meta_description', 'Admin Dashboard powered by AdminLTE')">
    <meta name="supported-color-schemes" content="light dark">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        crossorigin="anonymous" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('vendor/master-admin/assets/css/adminlte.css') }}">
    @stack('styles')
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
        {{-- Include Header --}}
        @include('layouts.admin-partials.header')

        {{-- Include Sidebar --}}
        @include('layouts.admin-partials.sidebar')

        {{-- Main Content Area --}}
        <main class="app-main">
            {{-- Content Header (Breadcrumbs) --}}
            @hasSection('page_title')
                <div class="app-content-header">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">

                                <h3 class="mb-0">@yield('page_title')</h3>
                            </div>
                        </div>
                    </div>
                </div>
            @endif


            {{-- Main Content --}}
            <div class="app-content">
                <div class="container-fluid">
                    <div class="notifications">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('info'))
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                {{ session('info') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                    </div>


                    @yield('content')
                </div>
            </div>
        </main>

        {{-- Include Footer --}}
        @include('layouts.admin-partials.footer')
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('vendor/master-admin/assets/js/adminlte.js') }}"></script>

    {{-- OverlayScrollbars Config --}}
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper"
        const Default = {
            scrollbarTheme: "os-theme-light",
            scrollbarAutoHide: "leave",
            scrollbarClickScroll: true
        }
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER)
            if (
                sidebarWrapper &&
                OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll
                    }
                })
            }
        })
    </script>

    {{-- Image path fix --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cssLink = document.querySelector('link[href*="css/adminlte.css"]');
            if (!cssLink) return;

            const cssHref = cssLink.getAttribute('href');
            const deploymentPath = cssHref.slice(0, cssHref.indexOf('css/adminlte.css'));

            document.querySelectorAll('img[src^="/assets/"]').forEach(img => {
                const originalSrc = img.getAttribute('src');
                if (originalSrc) {
                    const relativeSrc = originalSrc.slice(1);
                    img.src = deploymentPath + relativeSrc;
                }
            });
        });
    </script>

    {{-- Session debugging script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Make sure alerts are visible and styled correctly
            document.querySelectorAll('.notifications .alert').forEach(alert => {
                console.log('Alert found:', alert);
                // Ensure alerts are visible with correct styling
                alert.style.display = 'block';
                alert.style.zIndex = '9999';
                alert.style.position = 'relative';
                
                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }, 5000);
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
