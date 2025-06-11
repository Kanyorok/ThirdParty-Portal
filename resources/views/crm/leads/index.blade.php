@php use App\Enums\LeadStatusEnum;use App\Enums\LeadTypeEnum; use App\Enums\LocalityTypeEnum; @endphp
@extends('layouts.app')

@section('title','Leads')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-6 col-md-3 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title">New 🎉</h5>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3 text-center lead-counter cursor-pointer" id="newLeads"
                        title="for the last 14 days"><i
                            class="fas fa-spinner fa-spin"></i></h1>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title">Hot {{ LeadStatusEnum::Hot->getIcon() }}</h5>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3 text-center lead-counter cursor-pointer" id="hotLeads"><i
                            class="fas fa-spinner fa-spin"></i></h1>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title">Warm {{ LeadStatusEnum::Warm->getIcon() }}</h5>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3 text-center lead-counter cursor-pointer" id="warmLeads"><i
                            class="fas fa-spinner fa-spin"></i></h1>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title">Won {{ LeadStatusEnum::Won->getIcon() }}</h5>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3 text-center lead-counter cursor-pointer" id="wonLeads"
                        title="for the last 14 days"><i
                            class="fas fa-spinner fa-spin"></i></h1>
                </div>
            </div>
        </div>

        {{-- <div class="col-sm-4 col-12">
             <div class="card">
                 <div class="card-body d-flex align-items-start row p-3">
                     <div class="col-6">
                         <button class="btn btn-outline-primary text-center w-100 modal-create-individual-lead"
                                 type="button"><i class="fas fa-user"></i>&nbsp;<i class="fas fa-plus"></i> <br>
                             Individual Lead
                         </button>
                     </div>
                     <div class="col-6">

                     </div>
                 </div>
             </div>
         </div>--}}
        <div class="card">
            <div class="card-header p-0">
                <div class="nav nav-pills card-header py-2">
                    <ul class="nav" role="tablist">
                        <li class="nav-item"><a class="nav-link active" href="#tab-active"
                                                data-bs-toggle="tab" role="tab" aria-selected="false"
                                                onclick="fetchActiveLeadsTable()"
                            >Active</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-leads-won"
                                                data-bs-toggle="tab" role="tab" aria-selected="false"
                                                onclick="fetchWonLeadsTable()"
                            >Won Pending Onboarding</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="tab-content p-0">
                    <div class="tab-pane m-2 active show" id="tab-active" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-4">
                                <span class="h3">Active Leads</span>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <button class="btn btn-outline-primary text-center w-100 mx-1 click-summary-data"
                                        type="button"
                                        data-click_url="{{ route('leads.create',['type'=>LeadTypeEnum::Individual->name]) }}"
                                        data-summary_title="Add Individual Lead">
                                    <i class="fas fa-user"></i>&nbsp;<i class="fas fa-plus"></i>
                                    Individual Lead
                                </button>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <button class="btn btn-outline-primary text-center w-100 mx-1 click-summary-data"
                                        type="button"
                                        data-click_url="{{ route('leads.create',['type'=>LeadTypeEnum::Company->name]) }}"
                                        data-summary_title="Add Corporate Lead">
                                    <i class="fas fa-briefcase"></i>&nbsp;<i class="fas fa-plus"></i>
                                    Corporate Lead
                                </button>
                            </div>
                            <div class="col-12 mt-2">
                                <table id="activeLeadsTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th class="d-none">otherNames</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Industry</th>
                                        <th>Status</th>
                                        <th>Last Contact</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane m-2" id="tab-leads-won" role="tabpanel">
                        <div class="row">
                            <div class="col-12">
                                <span class="h3">Won Leads </span> <small>pending on boarding</small>
                            </div>
                            <div class="col-12 mt-2">
                                <table id="wonLeadsTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th class="d-none">otherNames</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Industry</th>
                                        <th>Date Won</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')

    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        let activeLeadsTable = null, wonLeadsTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchActiveLeadsTable();


            $(document).on('click', '.lead-counter', function () {
                if ($(".lead-counter").html() !== '<i class="fas fa-spinner fa-spin"></i>') {
                    fetchLeadCounters();
                }
            });


            fetchLeadCounters();
        });

        function fetchLeadCounters() {
            $(".lead-counter").html('<i class="fas fa-spinner fa-spin"></i>');
            $.get("{{ route('leads.analytics') }}", function (data) {
                $('#newLeads').html(data.recent);
                $('#warmLeads').html(data.warm);
                $('#hotLeads').html(data.hot);
                $('#wonLeads').html(data.won);
            });
        }

        function fetchWonLeadsTable() {
            if (wonLeadsTable === null) {
                wonLeadsTable = $('#wonLeadsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[6, 'desc']],
                    columnDefs: [
                        {"className": "text-center", "targets": [3]},
                        {
                            "render": function (data, type, row) {
                                if (row.OtherNames === null) {
                                    return data
                                }
                                return data + " " + row.OtherNames;
                            },
                            "targets": 1 // the place of col2
                        },
                        {"visible": false, "targets": [2]}
                    ],
                    ajax: {
                        url: getDocumentUrl() + "?q=won",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {
                            data: {
                                _: "photo",
                                sort: "LeadID",
                            }, name: 'LeadID', searchable: false
                        },
                        {data: 'Name', name: 'Name'},
                        {data: 'OtherNames', name: 'OtherNames'},
                        {data: 'Type', name: 'Type'},
                        {data: 'location', name: 'location.Name', searchable: false},
                        {data: 'industry', name: 'industry.Description', searchable: false},
                        {data: 'ModifiedOn', name: 'ModifiedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "no leads found here"
                    }
                });

                wonLeadsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading leads.");
                    console.log(er);
                });
            } else {
                wonLeadsTable.ajax.reload();
            }
        }

        function fetchActiveLeadsTable() {
            if (activeLeadsTable === null) {
                activeLeadsTable = $('#activeLeadsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[7, 'desc']],
                    columnDefs: [
                        {"className": "text-center", "targets": [3]},
                        {
                            "render": function (data, type, row) {
                                if (row.OtherNames === null) {
                                    return data
                                }
                                return data + " " + row.OtherNames;
                            },
                            "targets": 1 // the place of col2
                        },
                        {"visible": false, "targets": [2]}
                    ],
                    ajax: {
                        url: getDocumentUrl(),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {
                            data: {
                                _: "photo",
                                sort: "LeadID",
                            }, name: 'LeadID', searchable: false
                        },
                        {data: 'Name', name: 'Name'},
                        {data: 'OtherNames', name: 'OtherNames'},
                        {data: 'Type', name: 'Type'},
                        {data: 'location', name: 'location.Name', searchable: false},
                        {data: 'industry', name: 'industry.Description', searchable: false},
                        {data: 'Status', name: 'Status'},
                        {data: 'LastContacted', name: 'LastContacted'},
                    ], "oLanguage": {
                        "sEmptyTable": "no leads found here"
                    }
                });

                activeLeadsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading leads.");
                    console.log(er);
                });
            } else {
                activeLeadsTable.ajax.reload();
            }
        }


    </script>
@endsection
