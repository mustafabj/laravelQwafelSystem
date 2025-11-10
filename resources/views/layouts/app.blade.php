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
                @if (auth()->check() && auth()->user()->office)
                    {{ auth()->user()->office->officeName }}
                @endif
            </a>
            <div class="dropdown">
                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ asset('image/user.png') }}" alt="user" width="32" height="32"
                        class="rounded-circle">
                    <span>{{ auth()->user()->name }}</span>
                </button>

                <ul class="dropdown-menu dropdown-menu-start">
                    <li><a class="dropdown-item" href="{{ url('admin') }}">لوحة التحكم</a></li>
                    <li><a class="dropdown-item" href="{{ route('logout') }}">تسجيل الخروج</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    @yield('content')

    <!-- Global Modal -->
    <div class="modal fade" id="appModal" tabindex="-1" aria-labelledby="modelTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content responsive-table">
                <!-- Body -->
                <div class="modal-body" id="appModalBody"></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    @routes
    @vite(['resources/js/loader.js'])
</body>

</html>
