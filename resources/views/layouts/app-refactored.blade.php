<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts._partials._head')
    <title>{{ config('app.name') }} - @yield('title')</title>

    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
</head>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr"
      data-pc-theme_contrast="" data-pc-theme="light">

<!-- SIDEBAR - Always static, never reloads -->
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

<!-- HEADER - Static -->
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
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        <a href="javascript:void(0)" class="dropdown-item" onclick="layout_change('dark')">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-moon"></use>
                            </svg>
                            <span>Dark</span>
                        </a>
                        <a href="javascript:void(0)" class="dropdown-item" onclick="layout_change('light')">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-sun-1"></use>
                            </svg>
                            <span>Light</span>
                        </a>
                    </div>
                </li>
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                       role="button" aria-haspopup="false" aria-expanded="false">
                        <svg class="pc-icon">
                            <use xlink:href="#custom-notification"></use>
                        </svg>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        <a href="javascript:void(0)" class="dropdown-item">
                            <i class="ti ti-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </div>
                </li>
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                       role="button" aria-haspopup="false" aria-expanded="false">
                        <svg class="pc-icon">
                            <use xlink:href="#custom-user"></use>
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

<!-- MAIN CONTENT AREA - HTMX Target -->
<div class="pc-container">
    <div id="page-content">
        <!-- Loader scoped to content area -->
        <div class="loader-bg" style="display: none;">
            <div class="loader-track">
                <div class="loader-fill"></div>
            </div>
        </div>

        <div class="pc-content">
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
            @yield('content')
        </div>
    </div>
</div>

<!-- FOOTER - Static -->
<footer class="pc-footer">
    <div class="footer-wrapper container-fluid">
        <div class="row">
            <div class="col my-1">
                <p class="m-0">@include('layouts._partials._copyright')</p>
            </div>
        </div>
    </div>
</footer>

@include('layouts._partials._scripts')

<!-- Sidebar Navigation Script -->
<script>
    /**
     * HTMX Sidebar Navigation System
     * Handles sidebar state management and partial loading
     */
    (function () {
        'use strict';

        // Configuration
        const CONFIG = {
            sidebarSelector: '.pc-sidebar',
            contentSelector: '#page-content',
            loaderSelector: '#page-content .loader-bg',
            activeClass: 'active',
            triggerClass: 'pc-trigger',
            hasMenuClass: 'pc-hasmenu',
            submenuSelector: '.pc-submenu',
            sidebarLinkSelector: '.sidebar-link',
            sessionStorageKey: 'sidebarState'
        };

        // State management
        const state = {
            activeRoute: null,
            openMenus: new Set()
        };

        // Initialize
        function init() {
            setupHtmxEvents();
            setupSidebarClickHandlers();
            restoreSidebarState();
            highlightActiveRoute();
        }

        // Setup HTMX event handlers
        function setupHtmxEvents() {
            // Before request
            document.body.addEventListener('htmx:beforeRequest', function (event) {
                if (event.detail.target.id === 'page-content') {
                    showLoader();

                    // Optimistic UI update
                    const link = event.detail.elt;
                    if (link && link.classList.contains('sidebar-link')) {
                        updateActiveState(link);
                        expandParentMenus(link);
                    }
                }
            });

            // After request
            document.body.addEventListener('htmx:afterRequest', function (event) {
                if (event.detail.target.id === 'page-content') {
                    hideLoader();

                    // Update active route
                    const newUrl = event.detail.xhr.responseURL || window.location.href;
                    state.activeRoute = new URL(newUrl).pathname;

                    // Highlight active route and expand menus
                    setTimeout(() => {
                        highlightActiveRoute();
                        expandActiveMenus();
                        saveSidebarState();
                    }, 100);
                }
            });

            // Handle HTMX errors
            document.body.addEventListener('htmx:responseError', function (event) {
                console.error('HTMX request failed:', event.detail);
                hideLoader();

                // Fallback to full page reload
                const url = event.detail.xhr.responseURL || window.location.href;
                window.location.href = url;
            });
        }

        // Setup sidebar click handlers
        function setupSidebarClickHandlers() {
            document.addEventListener('click', function (event) {
                const link = event.target.closest(CONFIG.sidebarLinkSelector);
                if (!link) return;

                // Prevent default behavior for sidebar links
                event.preventDefault();

                const href = link.getAttribute('href');
                if (!href || href === 'javascript:void(0)') return;

                // Let HTMX handle the actual navigation
            });
        }

        // Update active state optimistically
        function updateActiveState(activeLink) {
            // Remove active class from all sidebar links
            document.querySelectorAll(`${CONFIG.sidebarSelector} ${CONFIG.sidebarLinkSelector}`).forEach(link => {
                link.closest('.pc-item').classList.remove(CONFIG.activeClass);
            });

            // Add active class to clicked link
            const parentItem = activeLink.closest('.pc-item');
            if (parentItem) {
                parentItem.classList.add(CONFIG.activeClass);
            }
        }

        // Expand parent menus for a given link
        function expandParentMenus(link) {
            const parentItem = link.closest('.pc-item');
            if (!parentItem) return;

            // Find all parent menus and expand them
            let currentItem = parentItem;
            while (currentItem) {
                const submenu = currentItem.querySelector(CONFIG.submenuSelector);
                if (submenu) {
                    submenu.style.display = 'block';
                    submenu.classList.add('show');

                    // Add trigger class to parent
                    const parentLink = currentItem.querySelector('.pc-link');
                    if (parentLink) {
                        currentItem.classList.add(CONFIG.triggerClass);
                    }
                }

                // Move to parent item
                currentItem = currentItem.parentElement?.closest('.pc-item');
            }
        }

        // Highlight the best matching route
        function highlightActiveRoute() {
            const currentPath = window.location.pathname;
            const sidebarLinks = document.querySelectorAll(`${CONFIG.sidebarSelector} ${CONFIG.sidebarLinkSelector}`);

            let bestMatch = null;
            let bestScore = 0;

            sidebarLinks.forEach(link => {
                const route = link.getAttribute('data-route');
                if (!route) return;

                const score = calculateRouteMatchScore(currentPath, route);
                if (score > bestScore) {
                    bestScore = score;
                    bestMatch = link;
                }
            });

            if (bestMatch) {
                updateActiveState(bestMatch);
                expandParentMenus(bestMatch);
            }
        }

        // Calculate route match score
        function calculateRouteMatchScore(currentPath, routePath) {
            if (currentPath === routePath) {
                return 100; // Exact match
            }

            if (currentPath.startsWith(routePath + '/')) {
                return routePath.length; // Prefix match
            }

            return 0;
        }

        // Expand menus for active route
        function expandActiveMenus() {
            const activeLink = document.querySelector(`${CONFIG.sidebarSelector} .${CONFIG.activeClass} .pc-link`);
            if (activeLink) {
                expandParentMenus(activeLink);
            }
        }

        // Show loader
        function showLoader() {
            const loader = document.querySelector(CONFIG.loaderSelector);
            if (loader) {
                loader.style.display = 'block';
            }
        }

        // Hide loader
        function hideLoader() {
            const loader = document.querySelector(CONFIG.loaderSelector);
            if (loader) {
                loader.style.display = 'none';
            }
        }

        // Save sidebar state to session storage
        function saveSidebarState() {
            try {
                const openMenus = [];
                document.querySelectorAll(`${CONFIG.sidebarSelector} .${CONFIG.triggerClass}`).forEach(item => {
                    const submenu = item.querySelector(CONFIG.submenuSelector);
                    if (submenu) {
                        openMenus.push(item.querySelector('.pc-link').getAttribute('data-route'));
                    }
                });

                const state = {
                    activeRoute: window.location.pathname,
                    openMenus: openMenus,
                    timestamp: Date.now()
                };
                sessionStorage.setItem(CONFIG.sessionStorageKey, JSON.stringify(state));
            } catch (e) {
                console.warn('Could not save sidebar state:', e);
            }
        }

        // Restore sidebar state from session storage
        function restoreSidebarState() {
            try {
                const savedState = sessionStorage.getItem(CONFIG.sessionStorageKey);
                if (savedState) {
                    const state = JSON.parse(savedState);

                    // Restore open menus
                    state.openMenus.forEach(route => {
                        const link = document.querySelector(`${CONFIG.sidebarSelector} a[data-route="${route}"]`);
                        if (link) {
                            const parentItem = link.closest('.pc-item');
                            if (parentItem) {
                                const submenu = parentItem.querySelector(CONFIG.submenuSelector);
                                if (submenu) {
                                    submenu.style.display = 'block';
                                    parentItem.classList.add(CONFIG.triggerClass);
                                }
                            }
                        }
                    });
                }
            } catch (e) {
                console.warn('Could not restore sidebar state:', e);
            }
        }

        // Public API
        window.SidebarNavigation = {
            highlightActiveRoute,
            expandActiveMenus,
            saveSidebarState,
            restoreSidebarState,
            getActiveRoute: () => state.activeRoute
        };

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

    })();
</script>

@stack('scripts')
</body>
</html>
