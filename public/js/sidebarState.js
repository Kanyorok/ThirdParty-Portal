// Sidebar state persistence for AblePro pc-navbar
// Keeps clicked item active and ancestors expanded across navigations
// Uses localStorage per user; idempotent restore

export const SidebarState = (() => {
    let cfg = {
        rootSelector: 'nav.pc-sidebar',
        itemSelector: 'li.pc-item',
        linkSelector: 'a.pc-link',
        submenuSelector: '.pc-submenu',
        activeItemClass: 'active',
        expandedItemClass: 'pc-trigger',
        userKey: 'guest'
    };

    const storageKey = () => `erp:sidebar:${cfg.userKey}`;
    const q = (sel, root = document) => root.querySelector(sel);
    const qa = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    function read() {
        try {
            return JSON.parse(localStorage.getItem(storageKey()) || '{}');
        } catch {
            return {};
        }
    }

    function write(state) {
        localStorage.setItem(storageKey(), JSON.stringify(state));
    }

    function normalizePath(p) {
        try {
            return new URL(p, location.href).pathname.replace(/\/+$|^\/+/, '/');
        } catch {
            return ('' + p).replace(/\/+$/, '');
        }
    }

    function getState() {
        const s = read();
        return {
            activeRouteId: s.activeRouteId ?? (window.__DEFAULT_ACTIVE_ROUTE__ || normalizePath(location.pathname)),
            openItemIds: Array.isArray(s.openItemIds) ? s.openItemIds : []
        };
    }

    function setActive(routeId) {
        const state = getState();
        state.activeRouteId = routeId || null;
        write(state);
    }

    function setOpenItemIds(ids) {
        const state = getState();
        state.openItemIds = Array.from(new Set(ids || []));
        write(state);
    }

    function computeAncestorItemIds(itemEl, root) {
        const ids = new Set();
        let current = itemEl;
        while (current && current !== root) {
            if (current.matches && current.matches(cfg.itemSelector)) {
                const pid = current.dataset.parentId;
                if (pid) ids.add(pid);
            }
            current = current.parentElement;
        }
        return Array.from(ids);
    }

    function clearActiveMarks(root) {
        qa(`${cfg.itemSelector}.${cfg.activeItemClass}`, root).forEach(li => li.classList.remove(cfg.activeItemClass));
    }

    // Scroll helpers: keep the active item visible inside the sidebar
    function getScrollContainer(root) {
        // Prefer inner scroll area if present (AblePro uses .navbar-content)
        return q('.navbar-content', root) || root;
    }

    function isInView(el, container, margin = 64) {
        try {
            const er = el.getBoundingClientRect();
            const cr = (container === document || container === document.body)
                ? {top: 0, bottom: (window.innerHeight || document.documentElement.clientHeight)}
                : container.getBoundingClientRect();
            return er.top >= cr.top + margin && er.bottom <= cr.bottom - margin;
        } catch {
            return true;
        }
    }

    function scrollIntoCenterIfNeeded(el, root) {
        try {
            const container = getScrollContainer(root);
            if (!isInView(el, container)) {
                el.scrollIntoView({behavior: 'smooth', block: 'center', inline: 'nearest'});
            }
        } catch {
        }
    }

    function applyToDom() {
        const root = q(cfg.rootSelector) || document;
        const {activeRouteId, openItemIds} = getState();

        clearActiveMarks(root);

        // Expand saved ancestors
        openItemIds.forEach(id => {
            const li = q(`${cfg.itemSelector}[data-item-id="${id}"]`, root);
            if (li) li.classList.add(cfg.expandedItemClass);
        });

        // Mark active item
        if (activeRouteId) {
            const link = q(`${cfg.linkSelector}[data-route-id="${activeRouteId}"]`, root) || q(`${cfg.linkSelector}[data-route="${activeRouteId}"]`, root);
            const item = link?.closest(cfg.itemSelector);
            if (item) item.classList.add(cfg.activeItemClass);
            if (item) {
                const ids = computeAncestorItemIds(item, root);
                ids.forEach(id => {
                    const li = q(`${cfg.itemSelector}[data-item-id="${id}"]`, root);
                    if (li) li.classList.add(cfg.expandedItemClass);
                });
                // After classes are applied, ensure the active link is visible
                if (link) {
                    setTimeout(() => scrollIntoCenterIfNeeded(link, root), 0);
                }
            }
        }
    }

    function handleSidebarClick(e) {
        const link = e.target.closest(cfg.linkSelector);
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

        const routeId = link.dataset.routeId || link.dataset.route || normalizePath(href);
        setActive(normalizePath(routeId));

        const item = link.closest(cfg.itemSelector);
        const root = q(cfg.rootSelector) || document;
        const openIds = item ? computeAncestorItemIds(item, root) : [];
        setOpenItemIds(openIds);

        applyToDom();
    }

    function bind() {
        const root = q(cfg.rootSelector) || document;
        root.addEventListener('click', handleSidebarClick, true);

        window.addEventListener('popstate', applyToDom);
        document.addEventListener('partial:loaded', applyToDom);
        if (window.htmx) document.body.addEventListener('htmx:afterSwap', applyToDom);
    }

    function init(options = {}) {
        cfg = {...cfg, ...options};
        // Seed data-route-id where missing
        (q(cfg.rootSelector) || document).querySelectorAll(cfg.linkSelector).forEach(a => {
            if (!a.dataset.routeId && a.dataset.route) a.dataset.routeId = normalizePath(a.dataset.route);
        });
        applyToDom();
        bind();
    }

    return {init, restore: applyToDom, setActive, setOpenItemIds};
})();

