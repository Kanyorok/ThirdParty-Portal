@extends('layouts.app')
@section('title', 'Reassign Vehicle')

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
        <h4 class="mb-4">🔁 Reassign Vehicle</h4>

        <form action="{{ route('fleet.assignments.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Vehicle</label>
                    <select name="VehicleID" class="form-select" required>
                        <option value="">Select Vehicle</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->VehicleID }}">
                                {{ $vehicle->RegistrationNumber }} ({{ $vehicle->Make }} {{ $vehicle->Model }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Assign to Branch</label>
                    <select name="AssignedBranchID" class="form-select">
                        <option value="">-- Optional --</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->ID }}">{{ $branch->BranchName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Assign to User</label>
                    <select name="AssignedToUserID" class="form-select">
                        <option value="">-- Optional --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Assignment Date</label>
                    <input type="date" name="AssignmentDate" class="form-control" required>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="Purpose" class="form-control"
                           placeholder="e.g., Project Use / Branch Swap">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea name="Notes" class="form-control" rows="2" placeholder="Additional remarks..."></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary" type="submit">🚚 Reassign Vehicle</button>
            </div>
        </form>
    </div>
@endsection
