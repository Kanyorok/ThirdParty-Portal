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
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('home') }}" class="b-brand text-primary d-flex align-items-center">
                <img src="{{ asset('assets/img/carft.png') }}" class="img-fluid" alt="logo" width="58" height="48">
                <div class="ms-3">
                    <div class="h2 mb-0 text-decoration-none">
                        {{ config('app.name') }}
                    </div>
                    <div class="small text-muted text-center">Thinking.Crafting.Transforming</div>
                </div>
            </a>
        </div>

        <div class="navbar-content">
            <div class="card pc-user-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 ">
                            {!! auth()->user()->getImage('class="avatar-1 user-avtar wid-45 hei-45 rounded-circle"
                            alt="user-image"') !!}
                        </div>
                        <div class="flex-grow-1 ms-3 me-2">
                            <h6 class="mb-0">{{ auth()->user()->UserID }}</h6>
                            <small data-i18n="Administrator">{{ auth()->user()->role()?->name }}</small><br>
                            <small data-i18n="Administrator">
                                {{ session('LoginBranchName') ? 'Branch: ' . session('LoginBranchName') : 'No branch selected' }}
                            </small>
                        </div>
                        <a class="btn btn-icon btn-link-secondary avtar collapsed" data-bs-toggle="collapse"
                           href="#pc_sidebar_userlink" aria-expanded="false">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-sort-outline"></use>
                            </svg>
                        </a>
                    </div>
                    <div class="pc-user-links collapse" id="pc_sidebar_userlink">
                        <div class="pt-3">
                            <a href="{{ route('profile') }}"><i class="ti ti-user"></i> <span>My Account</span>
                            </a>
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="ti ti-power"></i> <span data-i18n="Logout">Logout</span>
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts._partials._navbar')
        </div>
    </div>
</nav>
<header class="pc-header">
    <div class="header-wrapper">
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item pc-sidebar-collapse"><a href="#" class="pc-head-link ms-0" id="sidebar-hide"><i
                            class="ti ti-menu-2"></i></a></li>
                <li class="pc-h-item pc-sidebar-popup"><a href="#" class="pc-head-link ms-0" id="mobile-collapse"><i
                            class="ti ti-menu-2"></i></a></li>
                <li class="pc-h-item d-none d-md-inline-flex">
                    @yield('search-form')
                </li>

            </ul>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                       role="button" aria-haspopup="false" aria-expanded="false">
                        <svg class="pc-icon">
                            <use xlink:href="#custom-sun-1"></use>
                        </svg>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown"><a href="javascript:void(0)"
                                                                                  class="dropdown-item"
                                                                                  onclick="layout_change('dark')">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-moon"></use>
                            </svg>
                            <span>Dark</span> </a><a href="javascript:void(0)" class="dropdown-item"
                                                     onclick="layout_change('light')">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-sun-1"></use>
                            </svg>
                            <span>Light</span> </a><a href="javascript:void(0)" class="dropdown-item"
                                                      onclick="layout_change_default()">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-setting-2"></use>
                            </svg>
                            <span>Default</span></a></div>
                </li>
                <li class="pc-h-item">
                    <a href="#" class="pc-head-link me-0" data-bs-toggle="offcanvas" data-bs-target="#announcement"
                       aria-controls="announcement">
                        <svg class="pc-icon">
                            <use xlink:href="#custom-flash"></use>
                        </svg>
                    </a>
                </li>
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                       role="button" aria-haspopup="false" aria-expanded="false">
                        <svg class="pc-icon">
                            <use xlink:href="#custom-notification"></use>
                        </svg>
                        {{-- <span class="badge bg-success pc-h-badge">3</span>--}}
                    </a>
                    <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header d-flex align-items-center justify-content-between">
                            <h5 class="m-0">Notifications</h5><a href="javascript:void(0)"
                                                                 class="btn btn-link btn-sm disabled">Mark all
                                read</a>
                        </div>
                        <div class="dropdown-body text-wrap header-notification-scroll position-relative">
                            <p class="text-span text-center my-3">No Notifications here</p>
                        </div>
                        <div class="text-center py-2"><a href="javascript:void(0)"
                                                         class="link-danger disabled">Clear
                                all Notifications</a>
                        </div>
                    </div>
                </li>
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                       role="button" aria-haspopup="false" aria-expanded="false">
                        {!! auth()->user()->getImage('class="avatar-1 user-avtar" alt="user-image"') !!}
                        <svg class="pc-icon">
                            <use xlink:href="#custom-setting-2"></use>
                        </svg>

                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        <a href="{{ route('profile') }}" class="dropdown-item">
                            <i class="ti ti-user"></i> <span>My Account</span>
                        </a>
                        <a href="javascript:void(0)" class="dropdown-item"><i class="ti ti-headset"></i>
                            <span>Support</span>
                        </a>

                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="dropdown-item">
                            <i class="ti ti-power"></i> <span data-i18n="Logout">Logout</span>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

<div class="pc-container">
    <div class="pc-content" id="mainBodyContent">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-12 col-sm-6">
                        <div class="page-header-title"><h3 class="mb-0">@yield('title')</h3></div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <ul class="breadcrumb float-end">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            @yield('breadcrumbs')
                            <li class="breadcrumb-item" aria-current="page">@yield('title')</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
        @yield('content')
    </div>
</div>
<footer class="pc-footer">
    <div class="footer-wrapper container-fluid">
        <div class="row">
            <div class="col my-1">
                <p class="m-0">@include('layouts._partials._copyright')</p>
            </div>
            <div class="col-auto my-1">
                {{--<ul class="list-inline footer-link mb-0">
                    <li class="list-inline-item"><a
                            href="../../external.html?link=https://ableproadmin.com/index.html">Home</a></li>
                    <li class="list-inline-item"><a
                            href="../../external.html?link=https://phoenixcoded.gitbook.io/able-pro/"
                            target="_blank">Documentation</a></li>
                    <li class="list-inline-item"><a
                            href="../../external.html?link=https://phoenixcoded.authordesk.app/"
                            target="_blank">Support</a></li>
                </ul>--}}

            </div>
        </div>
    </div>
</footer>
@include('layouts._partials._scripts')

{{-- @stack('scripts') --}}
</body>
</html>
