<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts._partials._head')
    <title>{{ config('app.name') }} - @yield('title')</title>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr"
      data-pc-theme_contrast="" data-pc-theme="light">
<div class="loader-bg">
    <div class="loader-track">
        <div class="loader-fill"></div>
    </div>
</div>
<div class="auth-main">
    @yield('content')
</div>
@include('layouts._partials._scripts')
</body>
</html>
