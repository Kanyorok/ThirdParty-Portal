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
                    <label for="VehicleID" class="form-label">Vehicle</label>
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
                    <label for="RepairType" class="form-label">Repair Type</label>
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
                    <label for="RepairDate" class="form-label">Repair Date</label>
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
                    <label for="Vendor" class="form-label">Vendor</label>
                    <input type="text" name="Vendor" class="form-control" value="{{ old('Vendor') }}">
                    @error('Vendor')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Cost -->
                <div class="col-md-6">
                    <label for="Cost" class="form-label">Cost (KES)</label>
                    <input type="number" step="0.01" name="Cost" class="form-control" value="{{ old('Cost') }}">
                    @error('Cost')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Description -->
                <div class="col-md-12">
                    <label for="Description" class="form-label">Description</label>
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
