@extends('layouts.app')
@section('title', 'Edit Trip')

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
        <h4 class="mb-4">✏️ Edit Trip</h4>

        <form method="POST" action="{{ route('fleet.trip_logs.update', $parentTrip->Id) }}">
            @csrf
            @method('PUT')

            {{-- ================== Parent Trip ================== --}}
            <h5 class="fw-bold">🚍 Parent Trip</h5>
            <hr>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="TripType" class="form-label">Trip Type</label>
                    <select name="TripType" id="TripType" class="form-select" required>
                        <option value="">-- Select Trip Type --</option>
                        @foreach($tripTypes as $type)
                            <option value="{{ $type->ID }}"
                                {{ old('TripType', $parentTrip->TripType) == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="TripCode" class="form-label">Trip Code</label>
                    <input type="text" name="TripCode" id="TripCode" class="form-control"
                           value="{{ old('TripCode', $parentTrip->TripCode) }}" readonly>
                </div>

                <div class="col-md-4">
                    <label for="VehicleType" class="form-label">Vehicle Type</label>
                    <select name="VehicleType" id="VehicleType" class="form-select" required>
                        <option value="">-- Select Vehicle Type --</option>
                        @foreach($vehicleTypes as $type)
                            <option value="{{ $type->ID }}"
                                {{ old('VehicleType', $parentTrip->VehicleType) == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="LoadType" class="form-label">Load Type</label>
                    <select name="LoadType" id="LoadType" class="form-select">
                        <option value="">-- Select Load Type --</option>
                        @foreach($loadTypes as $type)
                            <option value="{{ $type->ID }}"
                                {{ old('LoadType', $parentTrip->LoadType) == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="TripStartDate" class="form-label">Trip Start Date</label>
                    <input type="date" name="TripStartDate" id="TripStartDate" class="form-control"
                           value="{{ old('TripStartDate', $parentTrip->TripStartDate ? \Carbon\Carbon::parse($parentTrip->TripStartDate)->format('Y-m-d') : '') }}"
                           required>
                </div>

                <div class="col-md-4">
                    <label for="StartTime" class="form-label">Start Time</label>
                    <input type="time" name="StartTime" id="StartTime" class="form-control"
                           value="{{ old('StartTime', $parentTrip->StartTime) }}">
                </div>

                <div class="col-md-4">
                    <label for="TripEndDate" class="form-label">Trip End Date</label>
                    <input type="date" name="TripEndDate" id="TripEndDate" class="form-control"
                           value="{{ old('TripEndDate', $parentTrip->TripEndDate ? \Carbon\Carbon::parse($parentTrip->TripEndDate)->format('Y-m-d') : '') }}">
                </div>

                <div class="col-md-4">
                    <label for="EndTime" class="form-label">End Time</label>
                    <input type="time" name="EndTime" id="EndTime" class="form-control"
                           value="{{ old('EndTime', $parentTrip->EndTime) }}">
                </div>

                <div class="col-md-4">
                    <label for="StartLocation" class="form-label">Start Location</label>
                    <input type="text" name="StartLocation" id="StartLocation"
                           class="form-control" value="{{ old('StartLocation', $parentTrip->StartLocation) }}">
                </div>

                <div class="col-md-4">
                    <label for="EndLocation" class="form-label">End Location</label>
                    <input type="text" name="EndLocation" id="EndLocation"
                           class="form-control" value="{{ old('EndLocation', $parentTrip->EndLocation) }}">
                </div>

                <div class="col-md-12">
                    <label for="Notes" class="form-label">Notes</label>
                    <textarea name="Notes" id="Notes" class="form-control"
                              rows="3">{{ old('Notes', $parentTrip->Notes) }}</textarea>
                </div>
            </div>

            {{-- ================== Child Trips ================== --}}
            <div class="mt-5">
                <h5 class="fw-bold">🚌 Child Trips</h5>
                <hr>

                <div id="childTripsContainer">
                    @foreach($childTrips as $child)
                        <div class="child-trip-row p-3 mb-3 border rounded">
                            <h6 class="fw-bold">Child Trip #{{ $loop->iteration }}</h6>
                            {{-- key child trips by their database Id --}}
                            <input type="hidden" name="childTrips[{{ $child->Id }}][Id]" value="{{ $child->Id }}">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Start Location</label>
                                    <input type="text" name="childTrips[{{ $child->Id }}][StartLocation]"
                                           class="form-control"
                                           value="{{ old("childTrips.{$child->Id}.StartLocation", $child->StartLocation) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">End Location</label>
                                    <input type="text" name="childTrips[{{ $child->Id }}][EndLocation]"
                                           class="form-control"
                                           value="{{ old("childTrips.{$child->Id}.EndLocation", $child->EndLocation) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Trip Start Date</label>
                                    <input type="date" name="childTrips[{{ $child->Id }}][TripStartDate]"
                                           class="form-control"
                                           value="{{ old("childTrips.{$child->Id}.TripStartDate", $child->TripStartDate ? \Carbon\Carbon::parse($child->TripStartDate)->format('Y-m-d') : '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Trip End Date</label>
                                    <input type="date" name="childTrips[{{ $child->Id }}][TripEndDate]"
                                           class="form-control"
                                           value="{{ old("childTrips.{$child->Id}.TripEndDate", $child->TripEndDate ? \Carbon\Carbon::parse($child->TripEndDate)->format('Y-m-d') : '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" name="childTrips[{{ $child->Id }}][StartTime]"
                                           class="form-control"
                                           value="{{ old("childTrips.{$child->Id}.StartTime", $child->StartTime) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">End Time</label>
                                    <input type="time" name="childTrips[{{ $child->Id }}][EndTime]" class="form-control"
                                           value="{{ old("childTrips.{$child->Id}.EndTime", $child->EndTime) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Purpose</label>
                                    <input type="text" name="childTrips[{{ $child->Id }}][Purpose]" class="form-control"
                                           value="{{ old("childTrips.{$child->Id}.Purpose", $child->Purpose) }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Notes</label>
                                    <textarea name="childTrips[{{ $child->Id }}][Notes]"
                                              class="form-control">{{ old("childTrips.{$child->Id}.Notes", $child->Notes) }}</textarea>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm mt-2 remove-child">Remove</button>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-outline-primary mt-3" id="addChildTripBtn">
                    ➕ Add Child Trip
                </button>
            </div>

            {{-- Save --}}
            <div class="mt-4">
                <button class="btn btn-success" type="submit">💾 Update Trip</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        const childTripsContainer = document.getElementById('childTripsContainer');
        const addChildTripBtn = document.getElementById('addChildTripBtn');

        // function to add NEW child rows (without Id, meaning they'll be treated as new)
        function addChildTripRow(index, data = {}) {
            const wrapper = document.createElement('div');
            wrapper.classList.add('child-trip-row', 'p-3', 'mb-3', 'border', 'rounded');

            wrapper.innerHTML = `
        <h6 class="fw-bold">New Child Trip</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Start Location</label>
                <input type="text" name="childTrips[new_${index}][StartLocation]" class="form-control" value="${data.StartLocation ?? ''}">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Location</label>
                <input type="text" name="childTrips[new_${index}][EndLocation]" class="form-control" value="${data.EndLocation ?? ''}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Trip Start Date</label>
                <input type="date" name="childTrips[new_${index}][TripStartDate]" class="form-control" value="${data.TripStartDate ?? ''}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Trip End Date</label>
                <input type="date" name="childTrips[new_${index}][TripEndDate]" class="form-control" value="${data.TripEndDate ?? ''}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Start Time</label>
                <input type="time" name="childTrips[new_${index}][StartTime]" class="form-control" value="${data.StartTime ?? ''}">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Time</label>
                <input type="time" name="childTrips[new_${index}][EndTime]" class="form-control" value="${data.EndTime ?? ''}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Purpose</label>
                <input type="text" name="childTrips[new_${index}][Purpose]" class="form-control" value="${data.Purpose ?? ''}">
            </div>
            <div class="col-md-8">
                <label class="form-label">Notes</label>
                <textarea name="childTrips[new_${index}][Notes]" class="form-control">${data.Notes ?? ''}</textarea>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm mt-2 remove-child">Remove</button>
    `;

            wrapper.querySelector('.remove-child').addEventListener('click', () => wrapper.remove());
            childTripsContainer.appendChild(wrapper);
        }

        addChildTripBtn.addEventListener('click', () => {
            const index = childTripsContainer.querySelectorAll('.child-trip-row').length;
            addChildTripRow(index);
        });
    </script>
@endsection
