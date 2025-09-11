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
  <footer class="pc-footer">
    <div class="footer-wrapper container-fluid">
      <div class="row">
        <div class="col my-1">
          <p class="m-0">@include('layouts._partials._copyright')</p>
        </div>
        <div class="col-auto my-1">
          {{-- <ul class="list-inline footer-link mb-0">
                    <li class="list-inline-item"><a
                            href="../../external.html?link=https://ableproadmin.com/index.html">Home</a></li>
                    <li class="list-inline-item"><a
                            href="../../external.html?link=https://phoenixcoded.gitbook.io/able-pro/"
                            target="_blank">Documentation</a></li>
                    <li class="list-inline-item"><a
                            href="../../external.html?link=https://phoenixcoded.authordesk.app/"
                            target="_blank">Support</a></li>
                </ul> --}}

        </div>
      </div>
    </div>
  </footer>
  @include('layouts._partials._scripts')

  @stack('scripts')

  <script>
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
          const res = await fetch(url, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              // Custom header to indicate we want the content fragment (not a DataTables/ajax payload)
              'X-Partial': '1'
            }
          });
          if (!res.ok) {
            window.location.href = url;
            return;
          }
          const text = await res.text();
          const parser = new DOMParser();
          const doc = parser.parseFromString(text, 'text/html');
          const newContent = doc.getElementById(contentId);
          if (!newContent) {
            // fallback to full navigation
            window.location.href = url;
            return;
          }
          const target = document.getElementById(contentId);
          if (!target) {
            window.location.href = url;
            return;
          }
          // replace inner HTML
          target.innerHTML = newContent.innerHTML;
          // update title if available
          const newTitle = doc.querySelector('title');
          if (newTitle) document.title = newTitle.innerText;
          // update breadcrumbs etc by letting server-rendered HTML take effect
          if (addToHistory) history.pushState({
            url: url
          }, '', url);
          window.scrollTo(0, 0);
          // execute any scripts inside the loaded fragment
          runScripts(target);
          // dispatch a helpful event for page-specific init
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
          // ignore links that should not be handled
          const href = a.getAttribute('href');
          if (!href) return;
          if (href.startsWith('#') || href.startsWith('javascript:')) return;
          if (a.target && a.target !== '_self') return;
          // Only intercept links explicitly marked for ajax or links coming from generated navbar
          const isAjaxMarked = a.hasAttribute('data-ajax') && a.getAttribute('data-ajax') === '1';
          const inGeneratedNavbar = !!a.closest('.pc-navbar');
          if (!isAjaxMarked && !inGeneratedNavbar) return;
          if (a.hasAttribute('data-no-ajax')) return;
          if (a.getAttribute('onclick')) return; // e.g., logout
          // logout link often submits a form; don't intercept
          if (a.getAttribute('href') && a.getAttribute('href').includes('/logout')) return;
          // ensure same origin
          if (!sameOrigin(href)) return;
          // Only intercept GET
          if ((ev.button && ev.button !== 0) || ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey) return;

          ev.preventDefault();
          ajaxNavigate(href);
        });
      }

      window.addEventListener('popstate', function(ev) {
        const url = location.href;
        // do not push history here
        ajaxNavigate(url, false);
      });
    })();

    // Highlight and scroll active sidebar item on load and after partial navigation
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
        const anchors = Array.from(navRoot.querySelectorAll('a[href]:not([href^="#"]):not([href^="javascript:"])'));
        const current = normalizePath(location.href);
        let match = null;
        for (const a of anchors) {
          try {
            const p = normalizePath(a.href);
            if (p === current) {
              match = a;
              break;
            }
          } catch (e) {}
        }
        // fallback: try startsWith
        if (!match) {
          for (const a of anchors) {
            try {
              const p = normalizePath(a.href);
              if (current.startsWith(p) && p !== '/') {
                match = a;
                break;
              }
            } catch (e) {}
          }
        }

        // remove existing markers
        navRoot.querySelectorAll('.pc-item.active, a.active').forEach(el => el.classList.remove('active'));

        if (!match) return;

        // mark the matching anchor and its ancestor .pc-item elements active
        match.classList.add('active');
        let el = match.closest('.pc-item') || match.parentElement;
        while (el && el !== navRoot) {
          if (el.classList && el.classList.contains('pc-item')) {
            el.classList.add('active');
          }
          // if the parent is a submenu, also mark its parent pc-item
          el = el.parentElement;
        }

        // scroll the matched anchor into view within the sidebar
        try {
          // prefer scrolling the anchor into center of visible area
          match.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'nearest'
          });
        } catch (e) {
          // ignore
        }
      }

      document.addEventListener('DOMContentLoaded', highlightAndScrollActive);
      document.addEventListener('partial:loaded', function() {
        // allow DOM to settle
        setTimeout(highlightAndScrollActive, 50);
      });
      // also run immediately in case DOMContentLoaded already fired
      if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(highlightAndScrollActive, 10);
      }
    })();
  </script>
</body>

</html>
