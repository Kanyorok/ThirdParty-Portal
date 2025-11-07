@php use App\Enums\Core\PermissionEnum; @endphp
<style>
    #offcanvasMain { /* allow dynamic width via CSS var */ }
    #offcanvasMain .offcanvas-resize-handle {
        position: absolute;
        left: 0;
        top: 0;
        width: 6px;
        height: 100%;
        cursor: ew-resize;
        z-index: 5;
        background: transparent;
    }
    body.resizing-offcanvas { cursor: ew-resize; user-select: none; }
    /* Slightly increase default width for better baseline */
    @media (min-width: 576px) {
        #offcanvasMain { --bs-offcanvas-width: 560px; }
    }

    /* Desktop-mode styling when offcanvas is wide enough */
    #offcanvasMain.offcanvas-desktop .offcanvas-header {
        position: sticky;
        top: 0;
        z-index: 2;
        background: var(--bs-body-bg);
    }
    #offcanvasMain.offcanvas-desktop .offcanvas-body {
        padding: 1.5rem 2rem;
    }
    #offcanvasMain.offcanvas-desktop { border-left: 1px solid var(--bs-border-color); }
</style>
<div class="offcanvas offcanvas-end " {{--data-bs-scroll="true" data-bs-backdrop="false"--}} tabindex="-1"
     id="offcanvasMain" aria-labelledby="offcanvasMainLabel">
    <div class="offcanvas-header border-bottom border-1 pb-0"><h3 id="offcanvasMainLabel" class="h3 pb-1"></h3>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-resize-handle" title="Drag to resize"></div>
    <div class="offcanvas-body" id="offcanvasMainBody"></div>
</div>
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/i18next.min.js') }}"></script>
<script src="{{ asset('assets/js/icon/custom-font.js') }}"></script>
<script src="{{ asset('assets/js/script.js') }}"></script>
<script src="{{ asset('assets/js/theme.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
<script src="{{ asset('assets/libs/dataTables/dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/dataTables/bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/libs/notyf/notyf.min.js') }}"></script>
{{--<script>layout_change('light');</script>
<script>change_box_container('false');</script>
<script>layout_caption_change('true');</script>
<script>layout_rtl_change('false');</script>
<script>preset_change('preset-1');</script>
<script>main_layout_change('vertical');</script>--}}
<script src="{{ asset('assets/js/_pages.js') }}"></script>
@auth
    @yield('script')
    <script>
        window.csrf_token = '{{ csrf_token() }}';
        window.bsOffcanvas = null;
        window.windowIdleTime = {{ (int) config('session.lifetime', 20) * 60 }};
        window.smsMaxLimit = 168;
        $(function () {
            jQuery.fn.fadeOutAndRemove = function (speed) {
                $(this).fadeOut(speed, function () {
                    $(this).remove();
                })
            }
            if (document.querySelectorAll('.sidebar .active').length > 1) {
                document.querySelectorAll('.sidebar .active')[0].scrollIntoView({behavior: "smooth", block: "center"});
            }

            window.bsOffcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasMain'));
            // Initialize resizable offcanvas width with persisted value
            try {
                const oc = document.getElementById('offcanvasMain');
                const saved = parseInt(localStorage.getItem('offcanvasWidth') || '0', 10);
                if (saved && saved > 320 && saved < window.innerWidth) {
                    oc.style.setProperty('--bs-offcanvas-width', saved + 'px');
                }

                const DESKTOP_MIN = 900; // px threshold for desktop styling
                function updateOffcanvasModeFromWidth(widthPx) {
                    if (widthPx >= DESKTOP_MIN) {
                        oc.classList.add('offcanvas-desktop');
                    } else {
                        oc.classList.remove('offcanvas-desktop');
                    }
                }
                // Initialize mode from saved width or computed style
                (function initMode() {
                    let current = saved;
                    if (!current) {
                        const comp = getComputedStyle(oc).getPropertyValue('--bs-offcanvas-width');
                        const n = parseInt((comp||'').toString().replace('px',''), 10);
                        if (n) current = n;
                    }
                    if (current) updateOffcanvasModeFromWidth(current);
                })();

                let isResizing = false;
                const handle = oc.querySelector('.offcanvas-resize-handle');
                handle.addEventListener('mousedown', function (e) {
                    isResizing = true;
                    document.body.classList.add('resizing-offcanvas');
                    e.preventDefault();
                });
                document.addEventListener('mouseup', function () {
                    if (!isResizing) return;
                    isResizing = false;
                    document.body.classList.remove('resizing-offcanvas');
                });
                document.addEventListener('mousemove', function (e) {
                    if (!isResizing) return;
                    const total = window.innerWidth || document.documentElement.clientWidth;
                    // offcanvas-end -> width spans from right edge to cursor x position
                    let newWidth = total - e.clientX;
                    const minW = 360, maxW = Math.floor(total * 0.95);
                    if (newWidth < minW) newWidth = minW;
                    if (newWidth > maxW) newWidth = maxW;
                    oc.style.setProperty('--bs-offcanvas-width', newWidth + 'px');
                    try { localStorage.setItem('offcanvasWidth', String(newWidth)); } catch (err) {}
                    updateOffcanvasModeFromWidth(newWidth);
                });
                window.addEventListener('resize', function(){
                    // Re-evaluate when window size changes to keep UX consistent
                    const comp = getComputedStyle(oc).getPropertyValue('--bs-offcanvas-width');
                    const n = parseInt((comp||'').toString().replace('px',''), 10);
                    if (n) updateOffcanvasModeFromWidth(n);
                });
            } catch (e) {
            }
            @if (session('status')) nSuccess('{!! session('status') !!} ');
            @endif
            @if (session('success')) nSuccess('{!! session('success') !!} ');
            @endif
            @if(session('fail')) nError('{!!  session('fail') !!}');
            @endif
            @if(session('warning')) nWarning('{!!  session('warning') !!}');
            @endif
            $(document).on("click", ".clear-balance", (function () {
                "**********" === $(this).html() ? $(this).html($(this).data("bal")) : $(this).html("**********")
            }));

            $(document).on('dblclick', '.dbl-click-redirect-data', function () {
                window.location.href = $(this).data('dbl_click_url');
            });
            $(document).on('click', '.click-redirect-data', function (event) {
                if (event.detail !== 1) {
                    return;
                }
                window.location.href = $(this).data('click_url');
            });
            $(document).on('dblclick', '.dbl-click-summary-data', function () {
                const url = $(this).data('dbl_click_url'), title = $(this).data('summary_title');
                showOffCanvasMain(title.toString(), url.toString());
            });
            $(document).on('click', '.click-summary-data', function (event) {
                if (event.detail !== 1) {
                    return;
                }
                const url = $(this).data('click_url'), title = $(this).data('summary_title');
                showOffCanvasMain(title.toString(), url.toString());
            });

            @if(!auth()->user()->can(PermissionEnum::UsersSessions)) setInterval(timerIncrement, 1000); @endif

            // Detect offline -> when back online, force a timeout to avoid stale sessions across networks
            window.addEventListener('online', function () {
                try {
                    $.post("{{ route('timeout') }}", {_token: window.csrf_token}).always(function () {
                        window.location.reload();
                    });
                } catch (e) {
                    window.location.reload();
                }
            });

            // When going offline, immediately treat session as expiring
            window.addEventListener('offline', function () {
                try {
                    nWarning('Connection lost. Your session will end when connection is restored.');
                } catch (e) {
                }
                try {
                    window.windowIdleTime = 0;
                } catch (e) {
                }
            });

            // Zero the idle timer on any action.
            $(this).bind('mousemove keydown scroll click', function () {
                window.windowIdleTime = {{ (int) config('session.lifetime', 20) * 60 }};
                $("#sessionInactivity").addClass("d-none");
            });

            // Initialize date pickers globally on page load
            if (typeof initGlobalDatePickers === 'function') {
                initGlobalDatePickers();
            }
        });

        function timerIncrement() {
            window.windowIdleTime--;

            let Seconds = window.windowIdleTime % 60;
            let Minutes = (window.windowIdleTime - Seconds) / 60;
            if (window.windowIdleTime < 120 && window.windowIdleTime > 30) {
                $("#sessionInactivity").removeClass("d-none");
            }
            if (window.windowIdleTime === 60) {
                nWarning("Session Expiring in 1 Minute.");
            }
            if (window.windowIdleTime <= 3) {
                $("#sessionInactivity").addClass("d-none");
                $.ajax({
                    url: "{{ route('timeout') }}",
                    type: 'POST',
                    dataType: 'json',
                    data: [{name: '_token', value: window.csrf_token}],
                    success: function (data) {
                        nWarning(data.message);
                        window.setTimeout(function () {
                            window.location.reload();
                        }, 3000)
                    }, error: function (request) {
                        formRequest(request, false);
                    }
                });
            }
            Seconds = Seconds < 10 ? "0" + Seconds : Seconds;

            $("#sessionInactivitySeconds").html(Seconds);
            $("#sessionInactivityMinutes").html("0" + Minutes);
        }

        // Background heartbeat: ensure stale sessions are kicked promptly
        (function () {
            function ping() {
                try {
                    fetch("{{ route('auth.heartbeat') }}", {credentials: 'include'})
                        .then(function (r) {
                            if (!r.ok) {
                                window.location.href = "{{ route('login') }}";
                            }
                        })
                        .catch(function () { /* offline - middleware will handle next request */
                        });
                } catch (e) {
                }
            }

            setInterval(ping, 15000);
            window.addEventListener('online', ping);
        })();

        // Global Flatpickr initialization for date-only inputs
        function initGlobalDatePickers() {
            try {
                if (window.flatpickr) {
                    var dateInputs = document.querySelectorAll('input[type="date"], input.flatpickr-date');
                    dateInputs.forEach(function (inputEl) {
                        if (inputEl._flatpickr) {
                            return; // already initialized
                        }

                        // if author marked input with data-disable-past, instruct flatpickr to disable past days
                        var opts = {
                            // Keep submitted value as ISO (server-friendly), show dd/mm/yyyy to users
                            dateFormat: 'Y-m-d',
                            altInput: true,
                            altFormat: 'd/m/Y',
                            allowInput: true
                        };

                        if (inputEl.dataset && inputEl.dataset.disablePast && String(inputEl.dataset.disablePast) === 'true') {
                            opts.minDate = 'today';
                            // on mobile, prevent native datepicker so flatpickr controls appearance
                            inputEl.type = 'text';
                        }

                        flatpickr(inputEl, opts);
                    });
                }
            } catch (e) {
                console.warn('Flatpickr init failed:', e);
            }
        }

        function showOffCanvasMain(title, url) {
            document.getElementById('offcanvasMainLabel').innerHTML = title;
            $("#offcanvasMainBody").html('<div class="text-center my-4"><div class="spinner-grow text-secondary me-2" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            window.bsOffcanvas.show();
            $.get(url, function (data) {
                $("#offcanvasMainBody").html(data);
                // Re-init date pickers for dynamically loaded content
                if (typeof initGlobalDatePickers === 'function') {
                    initGlobalDatePickers();
                }
            }).fail(function (jqXHR) {
                nError(jqXHR.responseJSON.message);
                window.bsOffcanvas.hide();
            });
        }
    </script>
@endauth
@yield('scripts')
