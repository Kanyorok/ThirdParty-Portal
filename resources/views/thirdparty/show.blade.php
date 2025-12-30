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
                            <i class="ti ti-unlink me-2"></i> Customer Profile</a>
                    </li>
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
                                        {!! $party->getImage('class="rounded-circle img-fluid wid-70" alt="User image""') !!}
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
                                        <p class="mb-0">{{ $location }} </p>
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
                            <div class="card-header">
                                <h5>Bank Details</h5>
                            </div>
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
                            <div class="card-header">
                                <h5>Users</h5>
                            </div>
                            <div class="card-body">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab-Supplier" role="tabpanel" aria-labelledby="profile-tab-Tenant">
                <div class="row">
                    <div class="col-12">
                        @if(isset($supplierStats))
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-4">Active Orders</h6>
                                        <div class="row d-flex align-items-center">
                                            <div class="col-9">
                                                <h3 class="f-w-300 d-flex align-items-center m-b-0">
                                                    <i class="feather icon-shopping-cart text-c-green f-30 m-r-10"></i>
                                                    {{ $supplierStats['active_orders'] }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-4">Total Order Value</h6>
                                        <div class="row d-flex align-items-center">
                                            <div class="col-9">
                                                <h3 class="f-w-300 d-flex align-items-center m-b-0">
                                                    <i class="feather icon-bar-chart-2 text-c-blue f-30 m-r-10"></i>
                                                    {{ number_format($supplierStats['total_order_value'], 2) }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-4">Pending Tenders</h6>
                                        <div class="row d-flex align-items-center">
                                            <div class="col-9">
                                                <h3 class="f-w-300 d-flex align-items-center m-b-0">
                                                    <i class="feather icon-file-text text-c-yellow f-30 m-r-10"></i>
                                                    {{ $supplierStats['pending_tenders'] }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h5>Payroll Details</h5>
                            </div>
                            <div class="card-body">
                                <!-- Existing Payroll Content -->
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning" role="alert">
                            <h4 class="alert-heading">Not a Supplier!</h4>
                            <p>This Third Party does not have a Supplier profile. They cannot be assigned Purchase Orders or participate in Tenders.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab-2" role="tabpanel" aria-labelledby="profile-tab-3">
                <div class="row">
                    <div class="col-12">
                        @if(isset($tenantStats))
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-4">Active Leases</h6>
                                        <div class="row d-flex align-items-center">
                                            <div class="col-9">
                                                <h3 class="f-w-300 d-flex align-items-center m-b-0">
                                                    <i class="feather icon-home text-c-purple f-30 m-r-10"></i>
                                                    {{ $tenantStats['active_leases'] }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-4">Monthly Rent Roll</h6>
                                        <div class="row d-flex align-items-center">
                                            <div class="col-9">
                                                <h3 class="f-w-300 d-flex align-items-center m-b-0">
                                                    <i class="feather icon-credit-card text-c-red f-30 m-r-10"></i>
                                                    {{ number_format($tenantStats['monthly_rent_roll'], 2) }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-info" role="alert">
                            <h4 class="alert-heading">Not a Tenant!</h4>
                            <p>This Third Party does not have a Tenant profile.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab-3" role="tabpanel" aria-labelledby="profile-tab-4">
                <div class="row">
                    <div class="col-12">
                        @if(isset($customerStats))
                        <div class="alert alert-success">
                            <i class="feather icon-check-circle me-2"></i> This Third Party is a registered Customer.
                            <!-- Add more customer specific stats here later -->
                        </div>
                        @else
                        <div class="alert alert-secondary" role="alert">
                            <h4 class="alert-heading">Not a Customer!</h4>
                            <p>This Third Party does not have a Customer profile.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab-4" role="tabpanel" aria-labelledby="profile-tab-5">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Documents Related</h5>
                            </div>
                            <div class="card-body">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab-5" role="tabpanel" aria-labelledby="profile-tab-6">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h5>Attrition (Deactivate Third Party)</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-danger">
                                    <i class="feather icon-alert-triangle me-2"></i>
                                    <strong>Warning:</strong> Deactivating this Third Party will:
                                    <ul>
                                        <li>Prevent any users associated with this party from logging in.</li>
                                        <li>Hide this party from selection lists for new transactions (Orders, Leases, etc.).</li>
                                        <li>This action is reversible by an Administrator.</li>
                                    </ul>
                                </div>

                                <form action="{{ route('thirdparty.parties.deactivate', $party->Id) }}" method="POST" onsubmit="return confirm('Are you sure you want to deactivate this Third Party?');">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="reason" class="form-label">Reason for Deactivation <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="reason" name="reason" rows="3" required placeholder="Please provide a valid reason for deactivation..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="feather icon-slash me-2"></i> Deactivate Third Party
                                    </button>
                                </form>
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
    $(function() {

    });
</script>
@endsection