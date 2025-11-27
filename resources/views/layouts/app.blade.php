<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'QwafelSystem') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css'])
</head>

<body data-route="{{ Route::currentRouteName() }}">
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-box"></i>
                </div>
                <div class="sidebar-title">نظام قوافل</div>
            </div>

            <nav class="sidebar-menu">
                <div class="menu-section">
                    <div class="menu-section-title">القائمة الرئيسية</div>
                    <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span class="menu-item-text">لوحة التحكم</span>
                    </a>
                    <a href="{{ route('wizard') }}" class="menu-item {{ request()->routeIs('wizard') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle"></i>
                        <span class="menu-item-text">الارساليات والسفريات</span>
                    </a>
                </div>

                <div class="menu-section">
                    <div class="menu-section-title">الارساليات</div>
                    <a href="{{ route('dashboard') }}#parcels" class="menu-item">
                        <i class="fas fa-box"></i>
                        <span class="menu-item-text">جميع الارساليات</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-inbox"></i>
                        <span class="menu-item-text">الواردة</span>
                        <span class="menu-badge">{{ $pendingCount ?? 0 }}</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-paper-plane"></i>
                        <span class="menu-item-text">الصادرة</span>
                    </a>
                </div>

                <div class="menu-section">
                    <div class="menu-section-title">السفريات</div>
                    <a href="{{ route('dashboard') }}#tickets" class="menu-item">
                        <i class="fas fa-bus"></i>
                        <span class="menu-item-text">جميع السفريات</span>
                    </a>
                </div>

                <div class="menu-section">
                    <div class="menu-section-title">الإعدادات</div>
                    <a href="{{ route('profile.edit') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <i class="fas fa-user"></i>
                        <span class="menu-item-text">الملف الشخصي</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">@yield('page-title', 'لوحة التحكم')</h1>
                </div>

                <div class="header-right">
                    <div class="header-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="بحث سريع...">
                    </div>

                    <div class="user-menu" id="userMenu">
                        <button class="user-button">
                            <div class="user-avatar">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="user-info">
                                <span class="user-name">{{ auth()->user()->name }}</span>
                                <span class="user-role">
                                    @if (auth()->check() && auth()->user()->office)
                                        {{ auth()->user()->office->officeName }}
                                    @else
                                        مستخدم
                                    @endif
                                </span>
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </button>

                        <div class="user-dropdown">
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>الملف الشخصي</span>
                            </a>
                            <a href="{{ route('dashboard') }}" class="dropdown-item">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>لوحة التحكم</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item dropdown-item-button">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>تسجيل الخروج</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Wrapper -->
            <div class="content-wrapper">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Global Modal -->
    <div class="modal fade" id="appModal" tabindex="-1" aria-labelledby="modelTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" id="appModalContent">
                <div id="appModalBody"></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    @routes
    @vite(['resources/js/loader.js'])
</body>

</html>
