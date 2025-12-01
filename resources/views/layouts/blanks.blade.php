<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts._partials._head')
    <title>{{ config('app.name') }} - @yield('title')</title>
    <style>
        /* background overlay + fixed cover */
        .auth-main {
            background: linear-gradient(rgba(255, 253, 253, 0.95), rgba(255, 253, 253, 0.95)),
            url({{ asset('assets/img/BRERP_Logo_small.png') }}) left/cover no-repeat fixed;
            height: 95vh;
        }

        /* simple footer styling */
        .site-footer {
            background: #f8f9fa;
        }
    </style>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr"
      data-pc-theme_contrast="" data-pc-theme="light">
<div class="loader-bg">
    <div class="loader-track">
        <div class="loader-fill"></div>
    </div>
</div>
<main class="auth-main flex-fill d-flex align-items-center justify-content-center">
    @yield('content')
</main>
<footer class="site-footer mt-auto text-center py-3 text-black">
    &copy; {{ date('Y') }} Bankers Realm. All rights reserved. Craft Silicon Ltd
</footer>
@include('layouts._partials._scripts')
</body>
</html>
