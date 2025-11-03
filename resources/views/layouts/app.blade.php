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
    
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    

<body>
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

    <!-- Footer Modal -->
    <div class="modal iteams" id="myModal">
        <div class="responsive-table modal-content" id="tbody">
            <div class="head">
                <h1 id="modelTitle"></h1>
                <span id="close">اغلاق</span>
            </div>
            <div id="bodyForm"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</body>
</html>
