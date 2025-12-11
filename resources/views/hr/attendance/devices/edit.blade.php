@extends('layouts.app')

@section('title', 'Edit Device/Channel')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Device / Channel</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.attendance.devices.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.attendance.devices.update', $device->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Device Code</label>
                        <input type="text" class="form-control" value="{{ $device->DeviceCode }}" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $device->Name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Channel *</label>
                        <select name="Channel" class="form-select" required>
                            @foreach(['Android','Biometric','Web'] as $ch)
                                <option value="{{ $ch }}" @selected(old('Channel', $device->Channel) == $ch)>{{ $ch }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Allowed IPs (comma separated)</label>
                        <input type="text" name="AllowedIPs" class="form-control" value="{{ old('AllowedIPs', $device->AllowedIPs) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Allowed Locations (GPS or names)</label>
                        <input type="text" name="AllowedLocations" class="form-control" value="{{ old('AllowedLocations', $device->AllowedLocations) }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="IsActive" @checked(old('IsActive', $device->IsActive))>
                            <label class="form-check-label" for="IsActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">Save Device</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
