@extends('layouts.app')

@section('title','Dashboard')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="mb-0">My Dashboard</h4>
            <div>
                <button id="add-widget" class="btn btn-sm btn-primary">Add widget</button>
                <button id="save-layout" class="btn btn-sm btn-outline-secondary">Save layout</button>
            </div>
        </div>
        <div id="widget-picker" class="mb-2" style="display:none">
            <select id="available-widgets" class="form-select form-select-sm" style="max-width:320px;display:inline-block"></select>
            <button id="add-selected" class="btn btn-sm btn-success">Add</button>
        </div>
    <div id="dashboard-grid" class="row g-3">
            @php
        $widgetsByKey = collect($availableWidgets ?? [])->keyBy('key');
            @endphp
            @foreach(($layout ?? []) as $slot)
                @php $def = $widgetsByKey[$slot->widget_key] ?? null; @endphp
                <div class="col-md-{{ $slot->w ?? ($def->default_w ?? 6) }}" data-key="{{ $slot->widget_key }}" data-w="{{ $slot->w }}" data-h="{{ $slot->h }}">
                    @if($def)
                        @include($def->view, ['stats' => ($stats ?? [])])
                    @else
                        <div class="card"><div class="card-body">Unknown widget: {{ $slot->widget_key }}</div></div>
                    @endif
                </div>
            @endforeach
        </div>
    <hr class="my-4"/>
    </div>

    <div class="row">
        <div class="col-md-6 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-primary">
                                <svg width="24" height="24"
                                     viewBox="0 0 24 24" fill="none"
                                     xmlns="../../external.html?link=http://www.w3.org/2000/svg">
                                    <path opacity="0.4" d="M13 9H7" stroke="#4680FF" stroke-width="1.5"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                    <path
                                        d="M22.0002 10.9702V13.0302C22.0002 13.5802 21.5602 14.0302 21.0002 14.0502H19.0402C17.9602 14.0502 16.9702 13.2602 16.8802 12.1802C16.8202 11.5502 17.0602 10.9602 17.4802 10.5502C17.8502 10.1702 18.3602 9.9502 18.9202 9.9502H21.0002C21.5602 9.9702 22.0002 10.4202 22.0002 10.9702Z"
                                        stroke="#4680FF" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                    <path
                                        d="M17.48 10.55C17.06 10.96 16.82 11.55 16.88 12.18C16.97 13.26 17.96 14.05 19.04 14.05H21V15.5C21 18.5 19 20.5 16 20.5H7C4 20.5 2 18.5 2 15.5V8.5C2 5.78 3.64 3.88 6.19 3.56C6.45 3.52 6.72 3.5 7 3.5H16C16.26 3.5 16.51 3.50999 16.75 3.54999C19.33 3.84999 21 5.76 21 8.5V9.95001H18.92C18.36 9.95001 17.85 10.17 17.48 10.55Z"
                                        stroke="#4680FF" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Open Budgets</h6>
                        </div>
                        {{--                        <div class="flex-shrink-0 ms-3">--}}
                        {{--                            <div class="dropdown"><a--}}
                        {{--                                    class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#"--}}
                        {{--                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i--}}
                        {{--                                        class="ti ti-dots-vertical f-18"></i></a>--}}
                        {{--                                <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"--}}
                        {{--                                                                                href="#">Today</a> <a--}}
                        {{--                                        class="dropdown-item" href="#">Weekly</a> <a--}}
                        {{--                                        class="dropdown-item" href="#">Monthly</a></div>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                    </div>
                    <div class="bg-body p-3 mt-3 rounded">
                        <div class="mt-3 row align-items-center">
                            <div class="col-7">
                                <div id="all-earnings-graph"></div>
                            </div>
                            <div class="col-5">
                                <h5 class="mb-1 text-end">{{$openBudgets}}</h5>
                                {{--                                <p class="text-primary mb-0"><i class="ti ti-arrow-up-right"></i> 30.6%</p>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-warning">
                                <svg width="24" height="24"
                                     viewBox="0 0 24 24" fill="none"
                                     xmlns="../../external.html?link=http://www.w3.org/2000/svg">
                                    <path
                                        d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z"
                                        stroke="#E58A00" stroke-width="1.5" stroke-miterlimit="10"
                                        stroke-linecap="round" stroke-linejoin="round"/>
                                    <path opacity="0.6" d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5"
                                          stroke="#E58A00" stroke-width="1.5" stroke-miterlimit="10"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                    <path opacity="0.6" d="M8 13H12" stroke="#E58A00" stroke-width="1.5"
                                          stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path opacity="0.6" d="M8 17H16" stroke="#E58A00" stroke-width="1.5"
                                          stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total GL</h6>
                        </div>
                        {{--                        <div class="flex-shrink-0 ms-3">--}}
                        {{--                            <div class="dropdown"><a--}}
                        {{--                                    class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#"--}}
                        {{--                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i--}}
                        {{--                                        class="ti ti-dots-vertical f-18"></i></a>--}}
                        {{--                                <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"--}}
                        {{--                                                                                href="#">Today</a> <a--}}
                        {{--                                        class="dropdown-item" href="#">Weekly</a> <a--}}
                        {{--                                        class="dropdown-item" href="#">Monthly</a></div>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                    </div>
                    <div class="bg-body p-3 mt-3 rounded">
                        <div class="mt-3 row align-items-center">
                            <div class="col-7">
                                <div id="page-views-graph"></div>
                            </div>
                            <div class="col-5">
                                <h5 class="mb-1 text-end">{{$totalGLS}}</h5>
                                {{--                                <p class="text-warning mb-0"><i class="ti ti-arrow-up-right"></i> 30.6%</p>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-success">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M17 21H7C5.895 21 5 20.105 5 19V5C5 3.895 5.895 3 7 3H12.414L17 7.586V19C17 20.105 16.105 21 15 21Z"
                                        stroke="#DC2626" stroke-width="1.5" stroke-miterlimit="10"
                                        stroke-linecap="round" stroke-linejoin="round"/>
                                    <path opacity="0.4" d="M12 3V8H17" stroke="#DC2626" stroke-width="1.5"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                    <path opacity="0.4" d="M8 11H14" stroke="#DC2626" stroke-width="1.5"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                    <path opacity="0.4" d="M8 15H14" stroke="#DC2626" stroke-width="1.5"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Documents</h6>
                        </div>
                        {{--                        <div class="flex-shrink-0 ms-3">--}}
                        {{--                            <div class="dropdown"><a--}}
                        {{--                                    class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#"--}}
                        {{--                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i--}}
                        {{--                                        class="ti ti-dots-vertical f-18"></i></a>--}}
                        {{--                                <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"--}}
                        {{--                                                                                href="#">Today</a> <a--}}
                        {{--                                        class="dropdown-item" href="#">Weekly</a> <a--}}
                        {{--                                        class="dropdown-item" href="#">Monthly</a></div>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                    </div>
                    <div class="bg-body p-3 mt-3 rounded">
                        <div class="mt-3 row align-items-center">
                            <div class="col-7">
                                <div id="total-task-graph"></div>
                            </div>
                            <div class="col-5">
                                <h5 class="mb-1 text-end">0</h5>
                                {{--                                <p class="text-success mb-0"><i class="ti ti-arrow-up-right"></i> Recent</p>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-danger">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M18.5 15.5C18.5 17.433 16.433 19.5 14.5 19.5H7C5.067 19.5 3 17.433 3 15.5C3 13.567 5.067 11.5 7 11.5C7 9.567 8.567 8 10.5 8C12.433 8 14 9.567 14 11.5H14.5C16.433 11.5 18.5 13.567 18.5 15.5Z"
                                        stroke="#DC2626" stroke-width="1.5" stroke-miterlimit="10"
                                        stroke-linecap="round" stroke-linejoin="round"/>
                                    <path opacity="0.4" d="M10 14.5H14V10.5" stroke="#DC2626" stroke-width="1.5"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">File size</h6>
                        </div>
                        {{--                        <div class="flex-shrink-0 ms-3">--}}
                        {{--                            <div class="dropdown"><a--}}
                        {{--                                    class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#"--}}
                        {{--                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i--}}
                        {{--                                        class="ti ti-dots-vertical f-18"></i></a>--}}
                        {{--                                <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item"--}}
                        {{--                                                                                href="#">Today</a> <a--}}
                        {{--                                        class="dropdown-item" href="#">Weekly</a> <a--}}
                        {{--                                        class="dropdown-item" href="#">Monthly</a></div>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                    </div>
                    <div class="bg-body p-3 mt-3 rounded">
                        <div class="mt-3 row align-items-center">
                            <div class="col-7">
                                <div id="download-graph"></div>
                            </div>
                            <div class="col-5">
                                <h5 class="mb-1 text-end">0.00 GB</h5>
                                {{--                                <p class="text-danger mb-0"><i class="ti ti-arrow-up-right"></i> 30.6%</p>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-xxl-12 d-flex">
        <div class="card  flex-fill w-100">
            <div class="card-header pb-0">
                <h5 class="card-title ">Pending Approvals
                    <button class="btn btn-link float-end" type="button" onclick="fetchPendingApprovalsTable()"><i
                            class="fas fa-refresh"></i></button>
                </h5>
            </div>
            <div class="card-body pt-0">
                <table id="approvalsTable"
                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                    <thead>
                    <tr>
                        <th class="w-75">description</th>
                        <th>dated</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- <script src="{{ asset('assets/js/datatables.js') }}"></script> --}}
    <script>
        (function(){
            const grid = document.getElementById('dashboard-grid');
            const addBtn = document.getElementById('add-widget');
            const saveBtn = document.getElementById('save-layout');
            const picker = document.getElementById('widget-picker');
            const select = document.getElementById('available-widgets');
            const addSelectedBtn = document.getElementById('add-selected');

            async function fetchAvailable(){
                const res = await fetch("{{ route('user-dashboard.widgets') }}");
                const json = await res.json();
                return json;
            }

            function renderPicker(available){
                select.innerHTML = '';
                available.forEach(w => {
                    const opt = document.createElement('option');
                    opt.value = w.key; opt.textContent = `${w.name}${w.module? ' · '+w.module : ''}${w.type? ' · '+w.type : ''}`;
                    select.appendChild(opt);
                });
            }

            function addWidgetElement(widget){
                const col = document.createElement('div');
                col.className = `col-md-${widget.default_w || 6}`;
                col.dataset.key = widget.key;
                col.dataset.w = widget.default_w || 6;
                col.dataset.h = widget.default_h || 1;
                col.innerHTML = `<div class="card"><div class="card-body">Added widget: ${widget.name}. Save layout to persist.</div></div>`;
                grid.appendChild(col);
            }

            addBtn?.addEventListener('click', async ()=>{
                picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
                if (picker.style.display === 'block'){
                    const data = await fetchAvailable();
                    renderPicker(data.available || []);
                }
            });
            addSelectedBtn?.addEventListener('click', async ()=>{
                const data = await fetchAvailable();
                const key = select.value;
                const found = (data.available||[]).find(w=>w.key===key);
                if(found) addWidgetElement(found);
            });

            saveBtn?.addEventListener('click', async ()=>{
                const items = Array.from(grid.querySelectorAll('[data-key]'));
                const payload = { widgets: items.map((el, idx)=>({
                    widget_key: el.dataset.key,
                    x: 0, y: idx, w: parseInt(el.dataset.w||'6'), h: parseInt(el.dataset.h||'1'), sort_order: idx,
                })) };
                const res = await fetch("{{ route('user-dashboard.widgets.save') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(payload)
                });
                if(res.ok){
                    location.reload();
                } else {
                    alert('Failed to save layout');
                }
            });
        })();
    </script>
    <script>
        const converted = {!! json_encode(data_get($data,'leads.line.converted')) !!},
            labels = {!! json_encode(data_get($data,'leads.line.labels')) !!},
            statuses = {!! json_encode(data_get($data,'leads.donut.labels')) !!};
        let tasksTable = null, approvalsTable = null, leadStatus = [0, 0];
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            $.fn.dataTable.ext.errMode = 'none';


            var ctx = document.getElementById("leadChart").getContext("2d");

            const convertedColour = ctx.createLinearGradient(0, 0, 0, 225);
            convertedColour.addColorStop(0, "rgba(8,182,15,0.7)");
            convertedColour.addColorStop(1, "rgba(8,182,15, 0)");
            // Line chart
            new Chart(document.getElementById("leadChart"), {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Lead Won",
                        fill: true,
                        backgroundColor: convertedColour,
                        borderColor: window.theme.success,
                        data: converted
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        display: true
                    },
                    tooltips: {
                        intersect: false
                    },
                    hover: {
                        intersect: true
                    },
                    plugins: {
                        filler: {
                            propagate: false
                        }
                    },
                    scales: {
                        xAxes: [{
                            reverse: true,
                            gridLines: {
                                color: "rgba(0,0,0,0.0)"
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                min: 0,
                                precision: 0
                            },
                            display: true,
                            borderDash: [3, 3],
                            gridLines: {
                                color: "rgba(112,112,112,0.1)",
                                fontColor: "#fff"
                            }
                        }]
                    }
                }
            });

            fetchPendingApprovalsTable();

        });

        function fetchPendingApprovalsTable() {
            if (approvalsTable === null) {
                approvalsTable = $('#approvalsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[1, 'desc']],
                    ajax: {
                        url: '{{ route('pending-workflows') }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'description', name: 'Source'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "no pending workflow"
                    }
                });

                approvalsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading workflow.");
                    console.log(er);
                });
            } else {
                approvalsTable.ajax.reload();
            }
        }

        // Minimal client-side save of order and sizes (cols) using current DOM
        document.getElementById('saveDashboardLayout').addEventListener('click', function() {
            const grid = document.getElementById('dashboardGrid');
            const items = Array.from(grid.children);
            const payload = items.map((el, idx) => ({
                widget_key: el.getAttribute('data-key'),
                x: parseInt(el.getAttribute('data-x') || '0', 10),
                y: idx, // simple stacking by order
                w: parseInt(el.getAttribute('data-w') || '6', 10),
                h: parseInt(el.getAttribute('data-h') || '1', 10),
                sort_order: idx,
                config: {}
            }));
            fetch("{{ route('user-dashboard.widgets.save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ widgets: payload })
            }).then(r => r.json()).then(resp => {
                if (resp.status === 'ok') {
                    alert('Layout saved');
                }
            }).catch(() => alert('Failed to save layout'));
        });
    </script>
@endsection

