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
                <a href="{{ route('home') }}" class="b-brand text-primary">
                    <img src="{{ asset('assets/img/icons/android-icon-36x36.png') }}" class="img-fluid " alt="logo">
                    <span class="ms-3 h3 text-decoration-none"> {{ config('app.name') }}</span>
                    <span class="badge bg-light-success rounded-pill ms-2 theme-version">v0.0.1</span></a>
            </div>
            <div class="navbar-content">
                <div class="card pc-user-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                {!! auth()->user()->getImage('class="avatar-1 user-avtar wid-45 rounded-circle"
                                alt="user-image"') !!}
                            </div>
                            <div class="flex-grow-1 ms-3 me-2">
                                <h6 class="mb-0" data-i18n="Jonh Smith">{{ auth()->user()->UserID }}</h6>
                                <small data-i18n="Administrator">{{ auth()->user()->role()?->name }}</small>
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
                                <a href="{{ route('profile') }}"><i class="ti ti-user"></i> <span
                                        data-i18n="My Account">My Account</span>
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
                <ul class="pc-navbar">
                    <li class="pc-item {{ request()->is('/')?'active':'' }}"><a href="{{ route('home') }}"
                            class="pc-link"><span class="pc-micon">
                                <i data-feather="home" class="pc-icon"></i>
                                <use xlink:href="#custom-fatrows"></use>
                                </svg>
                            </span><span class="pc-mtext" data-i18n="Data">Home</span></a></li>
                    <li class="pc-item pc-hasmenu"><a href="#!" class="pc-link"><span class="pc-micon"><svg
                                    class="pc-icon">
                                    <use xlink:href="#custom-layer"></use>
                                </svg> </span><span class="pc-mtext" data-i18n="Online Courses">Procurement </span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                        <ul class="pc-submenu">
                            <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                        Procurement Plan
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                    <li class="pc-item"><a class="pc-link" href="{{ route('procurement-periods.index') }}" data-i18n="Procurement List">Procurement Period</a></li>
                                </ul>
                            </li>
                            <li class="pc-item pc-hasmenu">
                                <a class="pc-link" href="#!">
                                    <span data-i18n="Requisitions">Purchase Requisition
                                    </span>
                                    <span class="pc-arrow">
                                        <i data-feather="chevron-right">
                                        </i>
                                    </span>
                                </a>
                                <ul class="pc-submenu">
                                    <li class="pc-item"><a class="pc-link" href="{{route('requisitionItem.index')}}"
                                            data-i18n="List">Requisition List</a></li>
                                    <li class="pc-item"><a class="pc-link" href="{{route('requisitionItem.create')}}"
                                            data-i18n="Apply">Requisition Form</a></li>
                                    <li class="pc-item"><a class="pc-link" href="../admins/course-teacher-add.html"
                                            data-i18n="Add">Add</a></li>
                                </ul>
                            </li>
                            <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Teacher">Items
                                        Catalogue</span> <span class="pc-arrow"><i
                                            data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('items.create') }}" data-i18n="List">Add New
                                            Item</a>
                                    </li>
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('items.index') }}" data-i18n="Apply">Manage
                                            Items</a>
                                    </li>
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('categories.index') }}" data-i18n="Add">Item
                                            Categories</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Teacher">
                                        Procurement Modes
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('procurement-modes.index') }}"
                                            data-i18n="List">List Modes</a>
                                    </li>
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('procurement-modes.create') }}"
                                            data-i18n="Apply">Add Mode</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Teacher">
                                        Tenders
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('tendering-process.create') }}"
                                            data-i18n="Create Tender">Create Tender</a>
                                    </li>
                                    <li class="pc-item">
                                        <a class="pc-link" href="{{ route('tendering-process.index') }}"
                                            data-i18n="Tender List">Tender List</a>
                                    </li>
                                    <li class="pc-item pc-hasmenu">
                                        <a class="pc-link" href="#!">
                                            <span data-i18n="Auditors">Auditors</span>
                                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                        </a>
                                        <ul class="pc-submenu">
                                            <li class="pc-item">
                                                <a class="pc-link" href="{{ route('sasra-auditors.index') }}" data-i18n="SASRA List">SASRA Auditor List</a>
                                            </li>
                                            <li class="pc-item">
                                                <a class="pc-link" href="{{ route('sasra-auditors.import') }}" data-i18n="Upload">Upload Auditor List</a>
                                            </li>
                                            <li class="pc-item">
                                                <a class="pc-link" href="{{ route('engaged-auditors.index') }}" data-i18n="Engaged">Engaged Auditors</a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                        Suppliers
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                    <li class="pc-item"><a class="pc-link" href="{{ route('suppliers.index') }}" data-i18n="List">Supplier List</a></li>
                                </ul>
                            </li>

                        </ul>
                    </li>
                </ul>
                </li>
                </ul>
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
                        <form class="form-search"><i class="search-icon">
                                <svg class="pc-icon">
                                    <use xlink:href="#custom-search-normal-1"></use>
                                </svg>
                            </i><input type="search" class="form-control" placeholder="Ctrl + K"></form>
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
                                class="dropdown-item" onclick="layout_change('dark')">
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
        <div class="pc-content">
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

</body>

</html>