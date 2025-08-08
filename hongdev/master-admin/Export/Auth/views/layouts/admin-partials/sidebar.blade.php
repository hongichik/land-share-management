{{-- filepath: /Users/admin/Documents/code/thu_vien/laravel/vendor/hongdev/master-admin/resources/views/master-admin/layout/partials/sidebar.blade.php --}}
<aside class="app-sidebar {{ config('admin.sidebar.theme.class', 'bg-body-secondary shadow') }}" data-bs-theme="{{ config('admin.sidebar.theme.dark_mode', true) ? 'dark' : 'light' }}">
    {{-- Sidebar Brand --}}
    <div class="sidebar-brand">
        <a href="{{ url(config('admin.sidebar.brand.url', '/admin')) }}" class="brand-link">
            <img src="{{ asset(config('admin.sidebar.brand.logo', 'vendor/master-admin/assets/img/logoIT.png')) }}" 
                 alt="{{ config('admin.sidebar.brand.text', 'Admin') }} Logo" 
                 class="brand-image">
            <span class="brand-text fw-light">{{ config('admin.sidebar.brand.text', 'Master Admin') }}</span>
        </a>
    </div>

    {{-- Sidebar Wrapper --}}
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            {{-- Sidebar Menu --}}
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false" id="navigation">
                @foreach(config('admin.sidebar.menu', []) as $menuItem)
                    @if(isset($menuItem['permission']) && !auth()->user()->can($menuItem['permission']))
                        @continue
                    @endif
                    
                    <li class="nav-item {{ isset($menuItem['submenu']) ? 'has-treeview' : '' }} {{ isset($menuItem['active']) && request()->is($menuItem['active']) ? 'menu-open' : '' }}">
                        @if (isset($menuItem['route']))
                            <a href="{{ route($menuItem['route']) }}" class="nav-link {{ Request::is($menuItem['active']) ? 'active' : '' }}">
                        @elseif (isset($menuItem['url']))
                            <a href="{{ url($menuItem['url']) }}" class="nav-link {{ Request::is($menuItem['active']) ? 'active' : '' }}">
                        @else
                            <a href="#" class="nav-link">
                        @endif
                            <i class="nav-icon {{ $menuItem['icon'] ?? 'bi bi-circle' }}"></i>
                            <p>
                                {{ $menuItem['text'] }}
                                @if(isset($menuItem['submenu']))
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                @endif
                                @if(isset($menuItem['badge']))
                                    <span class="nav-badge badge text-bg-{{ $menuItem['badge']['type'] ?? 'secondary' }} me-3">
                                        {{ $menuItem['badge']['text'] }}
                                    </span>
                                @endif
                            </p>
                        </a>
                        
                        @if(isset($menuItem['submenu']))
                            <ul class="nav nav-treeview">
                                @foreach($menuItem['submenu'] as $submenu)
                                    @if(isset($submenu['permission']) && !auth()->user()->can($submenu['permission']))
                                        @continue
                                    @endif
                                    
                                    <li class="nav-item">
                                        @if (isset($submenu['route']))
                                            <a href="{{ route($submenu['route']) }}" class="nav-link {{ Request::is($submenu['active']) ? 'active' : '' }}">
                                        @elseif (isset($submenu['url']))
                                            <a href="{{ url($submenu['url']) }}" class="nav-link {{ Request::is($submenu['active']) ? 'active' : '' }}">
                                        @else
                                            <a href="#" class="nav-link">
                                        @endif
                                            <i class="nav-icon {{ $submenu['icon'] ?? 'bi bi-circle' }}"></i>
                                            <p>{{ $submenu['text'] }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>