@extends('layouts.app')
@section('title', 'Log Repair Entry')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">🔧 Log Repair Entry</h4>

        <form method="POST" action="{{ route('fleet.repair_logs.store') }}">
            @csrf

            <div class="row g-3">
                <!-- Vehicle -->
                <div class="col-md-6">
                    <label for="VehicleID" class="form-label">Vehicle<span class="text-danger">*</span></label>
                    <select name="VehicleID" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->Id }}" {{ old('VehicleID') == $vehicle->Id ? 'selected' : '' }}>
                                {{ $vehicle->RegistrationNo }}
                            </option>
                        @endforeach
                    </select>
                    @error('VehicleID')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Repair Type -->
                <div class="col-md-6">
                    <label for="RepairType" class="form-label">Repair Type<span class="text-danger">*</span></label>
                    <select name="RepairType" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        @foreach($repairType as $type)
                            <option value="{{ $type->ID }}" {{ old('RepairType') == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                    @error('RepairType')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Repair Date -->
                <div class="col-md-6">
                    <label for="RepairDate" class="form-label">Repair Date<span class="text-danger">*</span></label>
                    <input type="date" name="RepairDate" class="form-control" value="{{ old('RepairDate') }}" required>
                    @error('RepairDate')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Linked Schedule -->
                <div class="col-md-6">
                    <label for="ScheduleID" class="form-label">Linked Maintenance Schedule (optional)</label>
                    <select name="ScheduleID" class="form-select">
                        <option value="">-- None --</option>
                        @foreach($schedules as $schedule)
                            <option
                                value="{{ $schedule->Id }}" {{ old('ScheduleID') == $schedule->Id ? 'selected' : '' }}>
                                {{ $schedule->ScheduleID ?? 'Unknown' }}
                            </option>
                        @endforeach
                    </select>
                    @error('ScheduleID')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Vendor -->
               <div class="col-md-6">
                <label for="VendorID" class="form-label">Vendor Name<span class="text-danger">*</span></label>
                <select name="VendorID" class="form-select @error('VendorID') is-invalid @enderror">
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $vendor)
                    <option value="{{ $vendor->Id }}" 
                        {{ old('VendorID', $repair->VendorID ?? '') == $vendor->Id ? 'selected' : '' }}>
                        {{ $vendor->party->ThirdPartyName ?? 'Unknown Vendor' }}
                    </option>
                    @endforeach
                </select>
                @error('VendorID')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
                    
                    

                <!-- Cost -->
                <div class="col-md-6">
                    <label for="Cost" class="form-label">Cost (KES)<span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="Cost" class="form-control" value="{{ old('Cost') }}">
                    @error('Cost')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Description -->
                <div class="col-md-12">
                    <label for="Description" class="form-label">Description<span class="text-danger">*</span></label>
                    <textarea name="Description" class="form-control" rows="3">{{ old('Description') }}</textarea>
                    @error('Description')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="col-md-12">
                    <label for="Notes" class="form-label">Notes</label>
                    <textarea name="Notes" class="form-control" rows="2">{{ old('Notes') }}</textarea>
                    @error('Notes')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">💾 Save Repair Log</button>
            </div>
        </form>
    </div>
@endsection
