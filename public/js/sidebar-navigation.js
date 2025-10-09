/**
 * HTMX Sidebar Navigation System
 * Handles sidebar state management, partial loading, and prevents full page reloads
 */
(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        sidebarSelector: '.pc-sidebar',
        contentSelector: '#mainBodyContent',
        loaderSelector: '.loader-bg',
        activeClass: 'active',
        triggerClass: 'pc-trigger',
        hasMenuClass: 'pc-hasmenu',
        submenuSelector: '.pc-submenu',
        sidebarLinkSelector: '.sidebar-link',
        collapseSelector: '.collapse',
        sessionStorageKey: 'sidebarState'
    };

    // State management
    const state = {
        activeRoute: null,
        openMenus: new Set(),
        isNavigating: false
    };

    // Initialize
    function init() {
        setupHtmxEvents();
        setupSidebarClickHandlers();
        setupMenuExpansion();
        restoreSidebarState();
        highlightActiveRoute();
        
        // Initial load
        document.addEventListener('DOMContentLoaded', function() {
            highlightActiveRoute();
            restoreSidebarState();
        });
    }

    // Setup HTMX event handlers
    function setupHtmxEvents() {
        // Before request
        document.body.addEventListener('htmx:beforeRequest', function(event) {
            if (event.detail.target.id === 'mainBodyContent') {
                state.isNavigating = true;
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
        document.body.addEventListener('htmx:afterRequest', function(event) {
            if (event.detail.target.id === 'mainBodyContent') {
                state.isNavigating = false;
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
                
                // Dispatch custom event for other scripts
                document.dispatchEvent(new CustomEvent('partial:loaded', {
                    detail: { url: newUrl, target: 'mainBodyContent' }
                }));
            }
        });

        // Handle HTMX errors
        document.body.addEventListener('htmx:responseError', function(event) {
            console.error('HTMX request failed:', event.detail);
            state.isNavigating = false;
            hideLoader();
            
            // Fallback to full page reload
            const url = event.detail.xhr.responseURL || window.location.href;
            window.location.href = url;
        });
    }

    // Setup sidebar click handlers for optimistic UI updates
    function setupSidebarClickHandlers() {
        document.addEventListener('click', function(event) {
            const link = event.target.closest(CONFIG.sidebarLinkSelector);
            if (!link) return;

            // Prevent default behavior for sidebar links
            event.preventDefault();
            
            const href = link.getAttribute('href');
            if (!href || href === 'javascript:void(0)') return;

            // Let HTMX handle the actual navigation
            // The hx-get attribute will trigger the request
        });
    }

    // Setup menu expansion handlers
    function setupMenuExpansion() {
        document.addEventListener('click', function(event) {
            const arrow = event.target.closest('.pc-arrow');
            if (!arrow) return;

            const parentItem = arrow.closest('.pc-item');
            if (!parentItem) return;

            const submenu = parentItem.querySelector(CONFIG.submenuSelector);
            if (!submenu) return;

            event.preventDefault();
            event.stopPropagation();

            // Toggle submenu
            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
                parentItem.classList.remove(CONFIG.triggerClass);
            } else {
                submenu.style.display = 'block';
                parentItem.classList.add(CONFIG.triggerClass);
            }

            saveSidebarState();
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

    // Handle internal page links (like +Create Category buttons)
    function setupInternalLinkHandlers() {
        document.addEventListener('click', function(event) {
            const link = event.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
            if (link.target && link.target !== '_self') return;
            if (link.hasAttribute('data-no-ajax')) return;

            // Check if this is an internal link that should use HTMX
            const isInternalLink = href.startsWith('/') || href.includes(window.location.hostname);
            if (!isInternalLink) return;

            // Check if this link is NOT in the sidebar (to avoid double handling)
            const isSidebarLink = link.closest(CONFIG.sidebarSelector);
            if (isSidebarLink) return;

            // Add HTMX attributes to internal links
            event.preventDefault();
            
            link.setAttribute('hx-get', href);
            link.setAttribute('hx-target', '#mainBodyContent');
            link.setAttribute('hx-push-url', 'true');
            link.setAttribute('hx-swap', 'innerHTML');
            
            // Trigger HTMX request
            htmx.trigger(link, 'click');
        });
    }

    // Public API
    window.SidebarNavigation = {
        highlightActiveRoute,
        expandActiveMenus,
        saveSidebarState,
        restoreSidebarState,
        getActiveRoute: () => state.activeRoute,
        isNavigating: () => state.isNavigating
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Setup internal link handlers
    setupInternalLinkHandlers();

})();
