@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title')
    Campaign {{ Str::limit($campaign->Label,50) }}
@endsection
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css"
          integrity="sha512-ngQ4IGzHQ3s/Hh8kMyG4FC74wzitukRMIcTOoKT3EyzFZCILOPF0twiXOQn75eDINUfKBYmzYn2AA8DkAk8veQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center">{{ $campaign->Label }} </h2>
                    <p class="text-center">{{ $campaign->Type->name }}</p>
                    <p class="text-center"><b>Status: </b> {{ $campaign->Status->name }}</p>
                    <p class="text-center">Contacts: <b>{{ number_format($campaign->contacts()->count()) }}</b></p>
                    <p class="text-center">{{ $campaign->Notes }}</p>

                    @include('snippets.behind_scenes',['model'=>$campaign])
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xxl-9">
            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">Content</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-0" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchCampaignContactsTable()">Contacts </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane  active m-2" id="tab-1" role="tabpanel">
                        @if($hasProgress)
                            <h3 id="campaignLoanDetails" class="text-center"></h3>
                            <div class="progress mb-3" style="height: 20px;">
                                <div id="campaignProgress"
                                     class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                     style="width: 0" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        @else
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="py-3 col-12">
                                        <div class="chart chart-sm">
                                            <canvas id="campaignStatusChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="py-3 col-12">
                                        <h1 class="text-center">{{ data_get($data,'rate') }}%</h1>
                                        <table class="table mb-0">
                                            <tbody>
                                            @foreach(data_get($data,'labels') as $index=>$status)
                                                <tr>
                                                    <td>
                                                        {{ $status }}
                                                    </td>
                                                    <td class="text-end">{{ number_format(data_get($data,'data' )[$index]) }}</td>
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td>
                                                    Total
                                                </td>
                                                <td class="text-end">{{ number_format($campaign->contacts()->count()) }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <h3>Status: <b class="text-success">{{ $campaign->Status->name }}</b></h3>
                        {!! $campaign->Details !!}
                    </div>
                    <div class="tab-pane m-2" id="tab-0" role="tabpanel">
                        <table id="campaignContactsTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Party</th>
                                <th>Status</th>
                                <th>Dated</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"
            integrity="sha512-6F1RVfnxCprKJmfulcxxym1Dar5FsT/V2jiEUvABiaEiFWoQ8yHvqRM/Slf0qJKiwin6IDQucjXuolCfCKnaJQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>
        const contacts = {!! json_encode(data_get($data,'data')) !!},
            labels = {!! json_encode(data_get($data,'labels')) !!};
        let campaignContactsTable = null, campaignWorkflowTable = null, progressInterval = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            @if($hasProgress)
            fetchProgress();
            @else
            new Chart(document.getElementById("campaignStatusChart"), {
                type: "pie",
                data: {
                    labels: labels,
                    datasets: [{
                        data: contacts,
                        backgroundColor: [
                            window.theme.danger,
                            window.theme.success,
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
            @endif

        });

        @if($hasProgress)
        function fetchProgress() {
            if (progressInterval !== null) {
                clearInterval(progressInterval);
            }
            $.get("{{ route('loans-campaigns.edit', [$list->slug, $campaign->CampaignID]) }}", function (data) {
                $("#campaignLoanDetails").html(data.description);
                $("#campaignProgress").width(data.progress + '%').html('<small id="progress-status">' + data.description + '</small>');
                if (data.progress > 99) {
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 3000)
                } else {
                    progressInterval = setInterval(function () {
                        fetchProgress();
                    }, 5000);
                }
            });
        }
        @endif

        function fetchCampaignContactsTable() {
            if (campaignContactsTable === null) {
                campaignContactsTable = $('#campaignContactsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('campaigns.contacts',[$campaign->CampaignID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'party', name: 'party'},
                        {data: 'Status', name: 'Status'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "no contacts found here"
                    }
                });

                campaignContactsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading contacts.");
                    console.log(er);
                });
            } else {
                campaignContactsTable.ajax.reload();
            }
        }
    </script>
@endsection
