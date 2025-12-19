@extends('layouts.app')

@section('title')
    {{ $party->ThirdPartyName }}
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('thirdparty.parties.index') }}">Third Parties</a></li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body py-0">
                    <ul class="nav nav-tabs profile-tabs" id="thirdPartyTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#tab-0" role="tab"
                               aria-selected="false" tabindex="-1">
                                <i class="ti ti-user me-2"></i>Party Details</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link " id="profile-tab-2" href="#tab-Supplier" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <i class="fas fa-people-carry-box me-2"></i> Supplier Profile </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="profile-tab-3" href="#tab-2" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <i class="fas fa-building"></i> &nbsp; Tenant Profile</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="profile-tab-4" href="#tab-3" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <i class="ti ti-unlink me-2"></i> Customer Profile</a></li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="profile-tab-5" href="#tab-4" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <i class="ti ti-files me-2"></i> Documents Related</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="profile-tab-6" href="#tab-5" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <i class="ti ti-logout me-2"></i> Attrition</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content">
                <div class="tab-pane active show" id="tab-0" role="tabpanel" aria-labelledby="profile-tab-1">
                    <div class="row">
                        <div class="col-lg-4 col-xxl-3">
                            <div class="card">
                                <div class="card-body position-relative">
                                    <div class="text-center mt-3">
                                        <div class="chat-avtar d-inline-flex mx-auto">
                                            {!! $party->getImage('class="rounded-circle img-fluid wid-70"  alt="User image""') !!}
                                        </div>
                                        <h5 class="mb-0">{{ $party->ThirdPartyName }}</h5>
                                        <p class="text-muted text-sm">{{ $party->TradingName }}</p>
                                        <hr class="my-3 border border-secondary-subtle">
                                        <div class="d-inline-flex align-items-center justify-content-start w-100 mb-3">
                                            <i class="ti ti-mail me-2"></i>
                                            <p class="mb-0"><a href="javascript:void(0)"
                                                               data-info="{{ route('employees.store', [$party->EmployeeID]) }}~{{ $party->full_name }}~{{ $party->Email }}"
                                                               class="send-mail-to-action">{{ $party->Email }}</a>
                                            </p>
                                        </div>
                                        <div class="d-inline-flex align-items-center justify-content-start w-100 mb-3">
                                            <i class="ti ti-phone me-2"></i>
                                            <p class="mb-0"><a href="javascript:void(0)"
                                                               data-info="{{ route('employees.store', [$party->EmployeeID]) }}~{{ $party->full_name }}~{{ $party->Phone }}"
                                                               class=" send-message-to-action">{{ $party->Phone }}</a>
                                            </p>
                                        </div>
                                        <div class="d-inline-flex w-100 mb-3">
                                            <i class="ti ti-map-pin me-2"></i>
                                            <p class="mb-0">{{ $location }}  </p>
                                        </div>

                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><b>Physical Location</b><span
                                                    class="float-end">{{ $party->PhysicalAddress }} </span></li>
                                            <li class="list-group-item"><b>Business Type</b><span
                                                    class="float-end">{{ $party->businessType->Description }} </span>
                                            </li>
                                            @if(!empty($party->Website))
                                                <li class="list-group-item"><b>Website </b><a
                                                        href="{{ $party->Website }}" target="_blank"
                                                        class="float-end"> {{ $party->Website }} </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    @include('snippets.behind_scenes',['model' => $party])
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 col-xxl-9">
                            <div class="card">
                                <div class="card-header"><h5>Bank Details</h5></div>
                                <div class="card-body">
                                    <table id="productsTable"
                                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Dated</th>
                                            <th>action</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header"><h5>Users</h5></div>
                                <div class="card-body">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-Supplier" role="tabpanel" aria-labelledby="profile-tab-Tenant">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Payroll Details</h5></div>
                                <div class="card-body">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-2" role="tabpanel" aria-labelledby="profile-tab-3">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Leaves</h5></div>
                                <div class="card-body">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-3" role="tabpanel" aria-labelledby="profile-tab-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Next of Kin</h5></div>
                                <div class="card-body">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-4" role="tabpanel" aria-labelledby="profile-tab-5">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Documents Related</h5></div>
                                <div class="card-body">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-5" role="tabpanel" aria-labelledby="profile-tab-6">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Attrition</h5></div>
                                <div class="card-body">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('snippets.actions.mailto')
    @include('snippets.actions.sms')
    <script src='{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}'></script>
    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function () {

        });
    </script>
@endsection
