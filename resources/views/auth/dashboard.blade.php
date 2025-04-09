@extends('layouts.app')

@section('title','Dashboard')

@section('content')
    <div class="row">
        <div class="col-12 col-md-6 col-xl d-flex">
            <div class="card flex-fill">
                <a class="card-body py-4 text-decoration-none" href="{{ route('campaigns.index') }}">
                    <div class="float-end">
                        <i class="align-middle fas fa-copyright"></i>
                    </div>
                    <h4 class="mb-2"><small>active</small> Campaigns</h4>
                    <h2 class="text-center"> {{ data_get($data,'campaigns.active') }}</h2>
                </a>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl d-flex">
            <div class="card flex-fill">
                <a class="card-body text-decoration-none" href="{{ route('tickets.index') }}">
                    <div class="float-end">
                        <i class="align-middle" data-feather="check-square"></i>
                    </div>
                    <h4 class="mb-2"><small>open</small> Tickets</h4>
                    <h2 class="text-center"> {{ data_get($data,'tickets.active') }}</h2>
                </a>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl d-flex">
            <div class="card flex-fill">
                <a class="card-body text-decoration-none" href="{{ route('schedule.index') }}">
                    <div class="float-end">
                        <i class="align-middle" data-feather="phone"></i>
                    </div>
                    <h4 class="mb-2"><small>scheduled</small> Calls </h4>
                    <h2 class="text-center"> {{ data_get($data,'schedule.calls') }}</h2>
                </a>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl d-flex">
            <div class="card flex-fill">
                <a class="card-body text-decoration-none" href="{{ route('schedule.index') }}">
                    <div class="float-end">
                        <i class="align-middle" data-feather="calendar"></i>
                    </div>
                    <h4 class="mb-2"><small>upcoming</small> Meetings </h4>
                    <h2 class="text-center"> {{ data_get($data,'schedule.appointments') }}</h2>
                </a>
            </div>
        </div>
        {{--<div class="col-12 col-md-6 col-xl d-flex">
            <div class="card flex-fill">
                <a class="card-body text-decoration-none" href="{{ route('leads.index') }}">
                    <div class="float-end">
                        <i class="align-middle fa-regular fa-address-book"></i>
                    </div>
                    <h4 class="mb-2"><small>open</small> Leads </h4>
                    <h2 class="text-center"> {{ data_get($data,'leads.total') }}</h2>
                </a>
            </div>
        </div>--}}
    </div>

    <div class="row">
        <div class="col-12 col-lg-8 d-flex">
            <div class="card flex-fill w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Won Leads</h5>
                </div>
                <div class="card-body p-1 m-0">
                    <div class="chart chart-lg">
                        <canvas id="leadChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 d-flex">
            <div class="card flex-fill w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Leads Status</h5>
                </div>
                <div class="card-body d-flex pt-0 row">
                    <div class="py-3 col-12">
                        <div class="chart chart-sm">
                            <canvas id="leadsStatusChart"></canvas>
                        </div>
                    </div>
                    <div class="col-12">
                        <table class="table mb-0">
                            <tbody>
                            @foreach(data_get($data,'leads.donut.labels') as $status)
                                <tr>
                                    <td>
                                        {{ $status }}
                                    </td>
                                    <td class="text-end" id="{{$status}}Value">0</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-xxl-5 d-flex">
            <div class="card  flex-fill w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tasks Due</h5>
                </div>
                <div class="card-body">
                    <table id="tasksTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead class="d-none">
                        <tr>
                            <th>Task</th>
                            <th>actions</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-xxl-7 d-flex">
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
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>
        const converted = {!! json_encode(data_get($data,'leads.line.converted')) !!},
            labels = {!! json_encode(data_get($data,'leads.line.labels')) !!},
            statuses = {!! json_encode(data_get($data,'leads.donut.labels')) !!};
        let tasksTable = null, approvalsTable = null, leadStatus = [0, 0];
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchTasksTable();

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

            fetchLeadCounters();
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
        function fetchTasksTable() {
            if (tasksTable === null) {
                tasksTable = $('#tasksTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    dom: 'rtip',
                    ajax: {
                        url: '{{ route('tasks.index') }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'Notes', name: 'Notes'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no tasks found"
                    }
                });

                tasksTable.on('error', function (er) {
                    nWarning("an issue occurred while loading tasks.");
                    console.log(er);
                });
            } else {
                tasksTable.ajax.reload();
            }
        }

        function fetchLeadCounters() {
            $.get("{{ route('leads.analytics') }}", function (data) {
                $('#WarmValue').html(data.warm);
                $('#HotValue').html(data.hot);
                leadStatus = [data.warm, data.hot];
                new Chart(document.getElementById("leadsStatusChart"), {
                    type: "pie",
                    data: {
                        labels: statuses,
                        datasets: [{
                            data: leadStatus,
                            backgroundColor: [
                                window.theme.warning,
                                window.theme.danger,
                            ],
                            borderWidth: 1,
                            borderColor: window.theme.black
                        }]
                    },
                    options: {
                        responsive: !window.MSInputMethodContext,
                        maintainAspectRatio: false,
                        legend: {
                            display: false
                        },
                        cutoutPercentage: 70
                    }
                });

            });
        }
    </script>
@endsection
