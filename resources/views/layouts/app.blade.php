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
                @php
                $org = \App\Models\Settings\APICredential::query()->where('Integration', \App\Enums\Core\IntegrationsEnum::Organization->value)->latest('Id')->first();
                $branding = $org?->Configuration;
                $logo = is_object($branding) && isset($branding->logo) ? $branding->logo : 'assets/img/carft.png';
                $name = is_object($branding) && isset($branding->name) ? $branding->name : config('app.name');
                $motto = is_object($branding) && isset($branding->motto) ? $branding->motto : 'Thinking.Crafting.Transforming';
                @endphp
                <a href="{{ route('home') }}" class="b-brand text-primary d-flex align-items-center">
                    <img src="{{ asset($logo) }}" class="img-fluid" alt="logo" width="58" height="48">
                    <div class="ms-3">
                        <div class="h2 mb-0 text-decoration-none">
                            {{ $name }}
                        </div>
                        <div class="small text-muted text-center">{{ $motto }}</div>
                    </div>
                </a>
            </div>

            <div class="navbar-content">
                <div class="card pc-user-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 ">
                                @auth
                                {!! auth()->user()->getImage('class="avatar-1 user-avtar wid-45 hei-45 rounded-circle"
                                alt="user-image"') !!}
                                @else
                                <img src="{{ asset('assets/img/user/avatar-1.jpg') }}" class="avatar-1 user-avtar wid-45 hei-45 rounded-circle" alt="guest-image">
                                @endauth
                            </div>
                            <div class="flex-grow-1 ms-3 me-2">
                                @auth
                                <h6 class="mb-0">{{ auth()->user()->UserID }}</h6>
                                <small>{{ session('LoginRoleName')??'?' }}</small><br>
                                <small>{{ session('LoginBranchName') ?: 'No branch ?' }}</small>
                                @else
                                <h6 class="mb-0">Guest</h6>
                                <small>Visitor</small>
                                @endauth
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
                            {{-- <span class="badge bg-success pc-h-badge">3</span> --}}
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
                            <div class="text-center py-2"><a href="javascript:void(0)" class="link-danger disabled">Clear
                                    all Notifications</a>
                            </div>
                        </div>
                    </li>
                    <li class="dropdown pc-h-item">
                        <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            @auth
                            {!! auth()->user()->getImage('class="avatar-1 user-avtar" alt="user-image"') !!}
                            @else
                            <img src="{{ asset('assets/img/user/avatar-1.jpg') }}" class="avatar-1 user-avtar" alt="guest-image">
                            @endauth
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
                            <div class="page-header-title">
                                <h3 class="mb-0">@yield('title')</h3>
                            </div>
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
            @if (View::hasSection('page-alerts'))
                @yield('page-alerts')
            @endif
            @yield('content')
        </div>
    </div>
    <footer class="pc-footer">
        <div class="footer-wrapper container-fluid">
            <div class="row">
                <div class="col my-1">
                    <p class="m-0">@include('layouts._partials._copyright')</p>
                </div>
                <div class="col-auto my-1 d-flex align-items-center gap-3">
                    <span id="footer-datetime" class="text-muted small"></span>
                    <span class="text-muted small">|</span>
                    <span class="text-muted small">version v1.0.0</span>
                </div>
            </div>
        </div>
    </footer>
    @include('layouts._partials._scripts')

    {{-- Partial navigation init registry --}}
    <script src="{{ asset('js/partial-init.js') }}" defer></script>
    <script src="{{ asset('js/partial-widgets.js') }}" defer></script>
    <script src="{{ asset('js/partial-forms.js') }}" defer></script>

    @stack('scripts')

    <script>
        // Refresh Feather icons after partial content loads
        document.addEventListener('partial:loaded', function() {
            if (window.feather && typeof window.feather.replace === 'function') {
                try {
                    window.feather.replace();
                } catch (e) {}
            }
        });
    </script>
    <script type="module">
        import {
            SidebarState
        } from '/js/sidebarState.js';

        SidebarState.init({
            rootSelector: 'nav.pc-sidebar',
            itemSelector: 'li.pc-item',
            linkSelector: 'a.pc-link',
            submenuSelector: '.pc-submenu',
            activeItemClass: 'active',
            expandedItemClass: 'pc-trigger',
            userKey: '{{ auth()->id() ?? "guest" }}'
        });
        document.addEventListener('partial:loaded', () => SidebarState.restore());
    </script>

    <script>
        // Footer DateTime (user timezone in browser)
        (function updateFooterDateTime() {
            const el = document.getElementById('footer-datetime');
            if (!el) return;
            const now = new Date();
            // Format: YYYY-MM-DD HH:MM:SS (24h)
            const pad = n => n.toString().padStart(2, '0');
            const formatted = `${pad(now.getDate())}-${pad(now.getMonth() + 1)}-${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
            el.textContent = `System Time: ${formatted}`;
            setTimeout(updateFooterDateTime, 1000);
        })();

        // Keep sidebar static: load only #mainBodyContent for internal sidebar navigation
        (function() {
            const sidebar = document.querySelector('nav.pc-sidebar');
            const contentId = 'mainBodyContent';

            function sameOrigin(url) {
                try {
                    const u = new URL(url, location.href);
                    return u.origin === location.origin;
                } catch (e) {
                    return false;
                }
            }

            function runScripts(container) {
                if (!container) return;
                // Execute inline and external scripts found inside the new content
                const scripts = Array.from(container.querySelectorAll('script'));
                scripts.forEach(old => {
                    const s = document.createElement('script');
                    if (old.src) {
                        s.src = old.src;
                        // preserve execution order for external scripts
                        s.async = false;
                    } else {
                        s.textContent = old.textContent;
                    }
                    document.body.appendChild(s);
                    // remove the original to avoid duplication
                    old.parentNode && old.parentNode.removeChild(old);
                });
            }

            async function ajaxNavigate(url, addToHistory = true) {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-Partial': '1',
                            'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
                        }
                    });
                    
                    // Handle 419 Session Expired error
                    if (res.status === 419) {
                        console.warn('Session expired (419), redirecting to login');
                        window.location.href = '/login?expired=1';
                        return;
                    }
                    
                    if (!res.ok) {
                        console.warn('AJAX navigate failed with status:', res.status);
                        window.location.href = url;
                        return;
                    }
                    const text = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(text, 'text/html');
                    const newContent = doc.getElementById(contentId);
                    if (!newContent) {
                        window.location.href = url;
                        return;
                    }
                    const target = document.getElementById(contentId);
                    if (!target) {
                        window.location.href = url;
                        return;
                    }
                    target.innerHTML = newContent.innerHTML;
                    const newTitle = doc.querySelector('title');
                    if (newTitle) document.title = newTitle.innerText;
                    if (addToHistory) history.pushState({
                        url: url
                    }, '', url);
                    window.scrollTo(0, 0);
                    runScripts(target);
                    try {
                        sessionStorage.setItem('activeSidebarRoute', new URL(url, location.href).pathname);
                    } catch (e) {}
                    document.dispatchEvent(new CustomEvent('partial:loaded', {
                        detail: {
                            url
                        }
                    }));
                } catch (err) {
                    console.error('AJAX navigate failed, falling back', err);
                    window.location.href = url;
                }
            }

            if (sidebar) {
                sidebar.addEventListener('click', function(ev) {
                    const a = ev.target.closest && ev.target.closest('a');
                    if (!a) return;
                    const href = a.getAttribute('href');
                    if (!href) return;
                    if (href.startsWith('#') || href.startsWith('javascript:')) return;
                    if (a.target && a.target !== '_self') return;
                    const isAjaxMarked = a.hasAttribute('data-ajax') && a.getAttribute('data-ajax') === '1';
                    const inGeneratedNavbar = !!a.closest('.pc-navbar');
                    if (!isAjaxMarked && !inGeneratedNavbar) return;
                    if (a.hasAttribute('data-no-ajax')) return;
                    if (a.getAttribute('onclick')) return;
                    if (a.getAttribute('href') && a.getAttribute('href').includes('/logout')) return;
                    if (!sameOrigin(href)) return;
                    if ((ev.button && ev.button !== 0) || ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey) return;

                    ev.preventDefault();
                    try {
                        sessionStorage.setItem('activeSidebarRoute', new URL(href, location.href).pathname);
                    } catch (e) {}
                    ajaxNavigate(href);
                });
            }

            window.addEventListener('popstate', function(ev) {
                ajaxNavigate(location.href, false);
            });
        })();

        // Highlight and scroll active sidebar item
        (function() {
            function normalizePath(p) {
                try {
                    return new URL(p, location.href).pathname.replace(/\/+$|^\/+/g, '/');
                } catch (e) {
                    return ('' + p).replace(/\/+$/, '');
                }
            }

            function highlightAndScrollActive() {
                const sidebar = document.querySelector('nav.pc-sidebar');
                if (!sidebar) return;
                const navRoot = sidebar.querySelector('.pc-navbar') || sidebar;
                navRoot.querySelectorAll('a.pc-link:not([data-route])').forEach(a => {
                    try {
                        a.setAttribute('data-route', new URL(a.getAttribute('href'), location.href).pathname);
                    } catch (e) {}
                });
                const anchors = Array.from(navRoot.querySelectorAll('a[data-route]'));
                const current = normalizePath(location.pathname);
                let match = anchors.find(a => normalizePath(a.dataset.route) === current);

                if (!match) {
                    let best = null,
                        bestLen = 0;
                    anchors.forEach(a => {
                        const p = normalizePath(a.dataset.route);
                        if (current.startsWith(p) && p.length > bestLen && p !== '/') {
                            best = a;
                            bestLen = p.length;
                        }
                    });
                    match = best;
                }

                if (!match) {
                    const stored = sessionStorage.getItem('activeSidebarRoute');
                    if (stored) match = anchors.find(a => normalizePath(a.dataset.route) === normalizePath(stored));
                }

                if (!match) return;

                match.classList.add('active');
                let el = match.closest('.pc-item') || match.parentElement;
                while (el && el !== navRoot) {
                    if (el.classList && el.classList.contains('pc-item')) {
                        el.classList.add('active');
                        if (el.classList.contains('pc-hasmenu')) el.classList.add('pc-trigger');
                    }
                    el = el.parentElement;
                }

                try {
                    const rect = match.getBoundingClientRect();
                    const vpH = window.innerHeight || document.documentElement.clientHeight;
                    if (rect.top < 80 || rect.bottom > vpH - 40) {
                        match.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                } catch (e) {}
            }
            highlightAndScrollActive();
            document.addEventListener('partial:loaded', highlightAndScrollActive);
        })();

        // Global DataTable Protection
        (function() {
            'use strict';
            var checkJQuery = setInterval(function() {
                if (typeof jQuery === 'undefined') return;
                clearInterval(checkJQuery);
                var $ = jQuery;

                function setupDataTableObserver() {
                    var sidebarToggleButtons = document.querySelectorAll('#sidebar-hide, #mobile-collapse');
                    if (sidebarToggleButtons.length) {
                        sidebarToggleButtons.forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                setTimeout(function() {
                                    if ($.fn.DataTable) $.fn.DataTable.tables({
                                        visible: true,
                                        api: true
                                    }).columns.adjust();
                                }, 400);
                            });
                        });
                    }
                    var resizeTimer;
                    $(window).on('resize', function() {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(function() {
                            if ($.fn.DataTable) $.fn.DataTable.tables({
                                visible: true,
                                api: true
                            }).columns.adjust();
                        }, 250);
                    });
                }
                $(document).ready(setupDataTableObserver);
                document.addEventListener('partial:loaded', function() {
                    setTimeout(setupDataTableObserver, 100);
                    setTimeout(function() {
                        if ($.fn.DataTable) $.fn.DataTable.tables({
                            visible: true,
                            api: true
                        }).columns.adjust();
                    }, 500);
                });
            }, 100);
        })();
    </script>

    @include('partials.session-timeout')
    <script>
        (function() {
            // Re-init partials logic listener if needed
        })();
    </script>
</body>

</html>
