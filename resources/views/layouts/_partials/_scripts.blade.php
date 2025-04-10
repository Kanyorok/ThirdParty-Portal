@php use App\Enums\Core\PermissionEnum; @endphp
<div class="offcanvas offcanvas-end " {{--data-bs-scroll="true" data-bs-backdrop="false"--}} tabindex="-1"
     id="offcanvasMain" aria-labelledby="offcanvasMainLabel">
    <div class="offcanvas-header border-bottom border-1 pb-0"><h3 id="offcanvasMainLabel" class="h3 pb-1"></h3>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="offcanvasMainBody"></div>
</div>
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
{{--<script src="../assets/js/plugins/apexcharts.min.js"></script>
<script src="../assets/js/widgets/all-earnings-graph.js"></script>
<script src="../assets/js/widgets/page-views-graph.js"></script>
<script src="../assets/js/widgets/total-task-graph.js"></script>
<script src="../assets/js/widgets/download-graph.js"></script>
<script src="../assets/js/widgets/customer-rate-graph.js"></script>
<script src="../assets/js/widgets/tasks-graph.js"></script>
<script src="../assets/js/widgets/total-income-graph.js"></script>--}}
<!-- [Page Specific JS] end --><!-- Required Js -->
<script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/i18next.min.js') }}"></script>
{{--<script src="{{ asset('assets/js/plugins/i18nextHttpBackend.min.js') }}"></script>--}}
<script src="{{ asset('assets/js/icon/custom-font.js') }}"></script>
<script src="{{ asset('assets/js/script.js') }}"></script>
<script src="{{ asset('assets/js/theme.js') }}"></script>
{{--<script src="../assets/js/multi-lang.js') }}"></script>--}}
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

{{--<script>layout_change('light');</script>
<script>change_box_container('false');</script>
<script>layout_caption_change('true');</script>
<script>layout_rtl_change('false');</script>
<script>preset_change('preset-1');</script>
<script>main_layout_change('vertical');</script>--}}
<script src="{{ asset('assets/js/_pages.js') }}"></script>
@auth
    <script>
        window.csrf_token = '{{ csrf_token() }}';
        window.bsOffcanvas = null;
        window.windowIdleTime = 300;
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
            @if (session('status')) nSuccess('{!! session('status') !!} ');
            @endif
            @if (session('success')) nSuccess('{!! session('success') !!} ');
            @endif
            @if(session('fail')) nError('{!!  session('fail') !!}');
            @endif
            $(document).on("click", ".clear-balance", (function () {
                "**********" === $(this).html() ? $(this).html($(this).data("bal")) : $(this).html("**********")
            }));
            $(document).on('dblclick', '.dbl-click-redirect-data', function () {
                window.location.href = $(this).data('dbl_click_url');
            });
            $(document).on('click', '.click-redirect-data', function () {
                window.location.href = $(this).data('click_url');
            });
            $(document).on('dblclick', '.dbl-click-summary-data', function () {
                const url = $(this).data('dbl_click_url'), title = $(this).data('summary_title');
                showOffCanvasMain(title.toString(), url.toString());
            });
            $(document).on('click', '.click-summary-data', function () {
                const url = $(this).data('click_url'), title = $(this).data('summary_title');
                showOffCanvasMain(title.toString(), url.toString());
            });

            @if(config('app.debug')===false && !auth()->user()->can(PermissionEnum::UsersSessions)) setInterval(timerIncrement, 1000); // 1 second @endif

            // Zero the idle timer on any action.
            $(this).bind('mousemove keydown scroll click', function () {
                window.windowIdleTime = 300;
                $("#sessionInactivity").addClass("d-none");
            });
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
            if (window.windowIdleTime === 3) {
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

        function showOffCanvasMain(title, url) {
            document.getElementById('offcanvasMainLabel').innerHTML = title;
            $("#offcanvasMainBody").html('<div class="text-center my-4"><div class="spinner-grow text-secondary me-2" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            window.bsOffcanvas.show();
            $.get(url, function (data) {
                $("#offcanvasMainBody").html(data);
            }).fail(function (jqXHR) {
                nError(jqXHR.responseJSON.message);
                window.bsOffcanvas.hide();
            });
        }
    </script>
@endauth
@yield('scripts')
