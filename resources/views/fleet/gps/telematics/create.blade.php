@extends('layouts.app')
@section('title', 'Add Telematics Device')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">➕ Register Telematics / GPS Device</h4>

        <form action="{{ route('fleet.telematics.store') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="VehicleID" class="form-label">Vehicle</label>
                    <select name="VehicleID" id="VehicleID" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->VehicleID }}">
                                {{ $vehicle->RegistrationNumber }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="DeviceID" class="form-label">Device ID</label>
                    <input type="text" name="DeviceID" id="DeviceID" class="form-control" required
                           placeholder="e.g. GPS123456789">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="ProviderName" class="form-label">Provider</label>
                    <input type="text" name="ProviderName" id="ProviderName" class="form-control" required
                           placeholder="e.g. TrackMe Ltd.">
                </div>

                <div class="col-md-6">
                    <label for="InstallDate" class="form-label">Installation Date</label>
                    <input type="date" name="InstallDate" id="InstallDate" class="form-control" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="SubscriptionStatus" class="form-label">Subscription Status</label>
                    <select name="SubscriptionStatus" id="SubscriptionStatus" class="form-select" required>
                        <option value="Active">Active</option>
                        <option value="Expired">Expired</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="RenewalDate" class="form-label">Renewal Date</label>
                    <input type="date" name="RenewalDate" id="RenewalDate" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label for="Notes" class="form-label">Notes</label>
                <textarea name="Notes" id="Notes" rows="3" class="form-control"
                          placeholder="Any relevant details e.g. install position, SIM card etc."></textarea>
            </div>

            <h5 class="mt-4">🔌 Integration (Optional)</h5>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="TrackingURL" class="form-label">Tracking URL</label>
                    <input type="url" name="TrackingURL" id="TrackingURL" class="form-control"
                           placeholder="e.g. https://api.provider.com/track">
                </div>

                <div class="col-md-6">
                    <label for="APIKey" class="form-label">API Key / Token</label>
                    <input type="text" name="APIKey" id="APIKey" class="form-control" placeholder="e.g. abcd1234token">
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success">
                    💾 Save Device
                </button>
            </div>
        </form>
    </div>
@endsection
