<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="@yield('description')">
<meta name="keywords" content="">
<link rel="preconnect" href="https://fonts.gstatic.com">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/icons/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/icons/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/img/icons/android-icon-192x192.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/icons/favicon-16x16.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<link rel="mask-icon" href="{{ asset('assets/img/icons/safari-pinned-tab.svg') }}" color="#3e17a0">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
<meta name="msapplication-TileColor" content="#3e17a0">
<meta name="msapplication-TileImage" content="{{ asset('assets/img/icons/mstile-144x144.png') }}') }}">
<meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">
<meta name="theme-color" content="#2e3192">
<link href="{{ asset('assets/css/light.css') }}" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/plugins/notyf/notyf.min.css') }}">
@yield('styles')
