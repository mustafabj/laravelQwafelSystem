<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'QwafelSystem') }}</title>
    
        {{-- <link rel="icon" href="{{ Vite::asset('resources/images/alia-logo.png') }}">
        <link rel="apple-touch-icon" href="{{ Vite::asset('resources/images/alia-logo.png') }}"> --}}
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css'])
    </head>
    

<body data-route="{{ Route::currentRouteName() }}">
    <!-- Start Header -->
    <div class="main-header noPrint">
        <div class="container">
            <a href="#" class="logo">
                {{-- Example: get office name from DB --}}
                @if(auth()->check() && auth()->user()->office)
                {{ auth()->user()->office->officeName }}
                @endif
            </a>

            <div class="user" id="user">
                <img src="{{ asset('image/user.png') }}" alt="user" />

                @auth
                    <span>{{ auth()->user()->name }}</span>
                @endauth

                <div class="profile_menu" id="profile_menu">
                    <ul class="links">
                        <li><a href="{{ url('admin') }}">لوحة التحكم</a></li>
                        <li><a href="{{ route('logout') }}">تسجيل الخروج</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    @yield('content')

    <!-- Global Modal -->
    <div class="modal fade" id="appModal" tabindex="-1" aria-labelledby="appModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="appModalLabel">تفاصيل الارسالية</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="appModalBody">
            <div class="text-center py-5 text-muted">Loading...</div>
            </div>
            <div class="modal-footer" id="appModalFooter">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    @routes
    @vite(['resources/js/loader.js'])
</body>
</html>
