@extends('layouts.app')
@section('title', 'contactmanagement')
@section('content')
<div class="container mt-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h4>Asset Policy Manager</h4>
<a href="{{ route('coveredassetmanagement.create') }}" class="btn btn-sm btn-success">+ ADD New Premium Payment Entry</a>
</div>

<div class="container">
    <h2 class="mb-4">Assign Policy to Covered Asset</h2>

    <ul class="nav nav-tabs" id="assetTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="fixed-tab" data-bs-toggle="tab" data-bs-target="#fixed" type="button" role="tab">Fixed Asset</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="property-tab" data-bs-toggle="tab" data-bs-target="#property" type="button" role="tab">Property</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="vehicle-tab" data-bs-toggle="tab" data-bs-target="#vehicle" type="button" role="tab">Vehicle</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button" role="tab">Staff Member</button>
        </li>
    </ul>

    <div class="tab-content p-4 border border-top-0" id="assetTabsContent">
        <!-- Fixed Asset -->
        <div class="tab-pane fade show active" id="fixed" role="tabpanel">
            <form>
                <div class="mb-3">
                    <label for="fixedAssetId" class="form-label">Asset ID</label>
                    <input type="text" class="form-control" id="fixedAssetId" placeholder="Enter Asset ID">
                </div>
                <div class="mb-3">
                    <label for="fixedPolicy" class="form-label">Select Policy</label>
                    <select class="form-select" id="fixedPolicy">
                        <option selected disabled>Choose a policy</option>
                        <option>Maintenance Policy</option>
                        <option>Depreciation Policy</option>
                        <option>Warranty Policy</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Assign Policy</button>
            </form>
        </div>

        <!-- Property -->
        <div class="tab-pane fade" id="property" role="tabpanel">
            <form>
                <div class="mb-3">
                    <label for="propertyId" class="form-label">Property ID</label>
                    <input type="text" class="form-control" id="propertyId" placeholder="Enter Property ID">
                </div>
                <div class="mb-3">
                    <label for="propertyPolicy" class="form-label">Select Policy</label>
                    <select class="form-select" id="propertyPolicy">
                        <option selected disabled>Choose a policy</option>
                        <option>Fire Insurance</option>
                        <option>Theft Insurance</option>
                        <option>Natural Disaster Policy</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Assign Policy</button>
            </form>
        </div>

        <!-- Vehicle -->
        <div class="tab-pane fade" id="vehicle" role="tabpanel">
            <form>
                <div class="mb-3">
                    <label for="vehicleId" class="form-label">Vehicle Number</label>
                    <input type="text" class="form-control" id="vehicleId" placeholder="Enter Vehicle Number">
                </div>
                <div class="mb-3">
                    <label for="vehiclePolicy" class="form-label">Select Policy</label>
                    <select class="form-select" id="vehiclePolicy">
                        <option selected disabled>Choose a policy</option>
                        <option>Comprehensive Insurance</option>
                        <option>Third-party Liability</option>
                        <option>Fleet Maintenance</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Assign Policy</button>
            </form>
        </div>

        <!-- Staff Member -->
        <div class="tab-pane fade" id="staff" role="tabpanel">
            <form>
                <div class="mb-3">
                    <label for="staffId" class="form-label">Employee ID</label>
                    <input type="text" class="form-control" id="staffId" placeholder="Enter Employee ID">
                </div>
                <div class="mb-3">
                    <label for="staffPolicy" class="form-label">Select Policy</label>
                    <select class="form-select" id="staffPolicy">
                        <option selected disabled>Choose a policy</option>
                        <option>Health Insurance</option>
                        <option>Life Insurance</option>
                        <option>Disability Coverage</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Assign Policy</button>
            </form>
        </div>
    </div>
</div>
@endsection