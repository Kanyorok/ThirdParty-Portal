<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="@yield('description')">
<meta name="keywords" content="">
<meta name="msapplication-TileColor" content="#3e17a0">
<meta name="msapplication-TileImage" content="{{ asset('assets/img/icons/mstile-144x144.png') }}') }}">
<meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">
<meta name="theme-color" content="#2e3192">
<link rel="icon" href="{{ asset('assets/img/CBT-Logo.jpg') }}" type="image/x-icon"><!-- [Font] Family -->
<link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}" id="main-font-link">
<link rel="stylesheet" href="{{ asset('assets/fonts/phosphor/duotone/style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}"><!-- [Feather Icons] https://feathericons.com -->
<link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
<link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
<link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}"><!-- [Template CSS Files] -->
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
{{--<script src="{{ asset('assets/js/tech-stack.js') }}"></script> --}}
<link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/dataTables/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
{{-- Use CDN for Notyf CSS to match CDN JS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

{{-- Added as it is used in Property and Insurance beautification --}}
{{-- Include Bootstrap Icons --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
{{-- Include DataTables CSS --}}
{{-- Removed duplicate DataTables CSS, using Bootstrap5 version above --}}
@yield('styles')

