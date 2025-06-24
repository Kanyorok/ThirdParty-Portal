@extends('layouts.app')
@section('title', 'Lease Schedule')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <h1>Edit Lease Schedule</h1>
    <form action="{{ route('schedulelease.update', $leaseschedules->id) }}" method="POST">
        @csrf
        @method('PUT')

         <div class="col-md-6">
                            <label class="form-label">Select Lease Agreement</label>
                            <select name="leaseID" class="form-select" required>
                                <option>--Select the tenant</option>
                                @foreach ($newtenants as $newtenant)
                                    <option value="{{ $newtenant->Id }}">{{ $newtenant->TenantName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Frequency</label>
                            <input type="text" class="form-control" value="Monthly" name="PaymentFrequency">
                        </div>
                    </div>

                    <!-- Financial Parameters -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" value="2025-05-01" name="StartDate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" value="2026-04-30" name="EndDate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Base Rent per Period (KES)</label>
                            <input type="number" class="form-control" value="25000" name="BaseRent">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Service Charge (KES)</label>
                            <input type="number" class="form-control" value="1500" name="ServiceCharge">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Parking Fee (KES)</label>
                            <input type="number" class="form-control" value="2000" name="ParkingFee">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Other Charges (KES)</label>
                            <input type="number" class="form-control" value="0" name="OtherCharges">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Update Lease Schedule</button>
                    <a href="{{ route('schedulelease.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@endsection
