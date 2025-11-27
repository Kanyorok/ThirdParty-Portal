@extends('layouts.app')
@section('title', 'Log New Trip')

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
        <h4 class="mb-4">📘 Log New Trip</h4>

        <form method="POST" action="{{ route('fleet.trip_logs.store') }}">
            @csrf
            @if ($parentTrip)
                <input type="hidden" name="ParentTripID" value="{{ $parentTrip->Id }}">
            @endif

            {{-- ================== Parent Trip ================== --}}
            <h5 class="fw-bold">🚍 Parent Trip</h5>
            <hr>
            <div class="row g-3">

                {{-- Trip Type --}}
                <div class="col-md-4">
                    <label for="TripType" class="form-label">Trip Type<span class="text-danger">*</span></label>
                    <select name="TripType" id="TripType" class="form-select"
                            required {{ $parentTrip ? 'disabled' : '' }}>
                        <option value="">-- Select Trip Type --</option>
                        @foreach($tripTypes as $type)
                            <option value="{{ $type->ID }}"
                                {{ old('TripType', $parentTrip->TripType ?? $tripLog->TripType ?? '') == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                    @if ($parentTrip)
                        <input type="hidden" name="TripType" value="{{ $parentTrip->TripType }}">
                    @endif
                </div>

                {{-- Trip Code --}}
                <div class="col-md-4" id="tripCodeContainer">
                    <label for="TripCode" class="form-label">Trip Code</label>
                    @if ($parentTrip)
                        {{-- Display saved TripCode for parent trip --}}
                        <input type="text" class="form-control" 
                               value="{{ $parentTrip->TripCode }}" readonly>
                        <input type="hidden" name="TripCode" value="{{ $parentTrip->TripCode }}">
                    @else
                        <select name="TripCode" id="TripCode" class="form-select">
                            <option value="">-- Select Trip Code --</option>
                        </select>
                    @endif
                </div>

                {{-- Purpose (only if Trip Type = Other) --}}
                <div class="col-md-4 d-none" id="purposeContainer">
                    <label for="Purpose" class="form-label">Purpose</label>
                    <input type="text" name="Purpose" id="Purpose" class="form-control"
                           value="{{ old('Purpose', $parentTrip->Purpose ?? $tripLog->Purpose ?? '') }}"
                           maxlength="255">
                </div>

                {{-- Vehicle Type --}}
                <div class="col-md-4">
                    <label for="VehicleType" class="form-label">Vehicle Type<span class="text-danger">*</span></label>
                    <select name="VehicleType" id="VehicleType" class="form-select"
                            required {{ $parentTrip ? 'disabled' : '' }}>
                        <option value="">-- Select Vehicle Type --</option>
                        @foreach($vehicleTypes as $type)
                            <option value="{{ $type->ID }}"
                                {{ old('VehicleType', $parentTrip->VehicleType ?? $tripLog->VehicleType ?? '') == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                    @if ($parentTrip)
                        <input type="hidden" name="VehicleType" value="{{ $parentTrip->VehicleType }}">
                    @endif
                </div>

                {{-- Load Type --}}
                <div class="col-md-4">
                    <label for="LoadType" class="form-label">Load Type</label>
                    <select name="LoadType" id="LoadType" class="form-select" {{ $parentTrip ? 'disabled' : '' }}>
                        <option value="">-- Select Load Type --</option>
                        @foreach($loadTypes as $type)
                            <option value="{{ $type->ID }}"
                                {{ old('LoadType', $parentTrip->LoadType ?? $tripLog->LoadType ?? '') == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                    @if ($parentTrip)
                        <input type="hidden" name="LoadType" value="{{ $parentTrip->LoadType }}">
                    @endif
                </div>

             
                {{-- Dates / Times --}}
                <div class="col-md-4">
                    <label for="TripStartDate" class="form-label">Trip Start Date<span class="text-danger">*</span></label>
                    <input type="date" name="TripStartDate" id="TripStartDate" class="form-control"
                           value="{{ old('TripStartDate', $parentTrip->TripStartDate ?? $tripLog->TripStartDate ?? '') }}"
                           required {{ $parentTrip ? 'readonly' : '' }}>
                </div>

                <div class="col-md-4">
                    <label for="StartTime" class="form-label">Start Time</label>
                    <input type="time" name="StartTime" id="StartTime" class="form-control"
                           value="{{ old('StartTime', $parentTrip->StartTime ?? $tripLog->StartTime ?? '') }}"
                        {{ $parentTrip ? 'readonly' : '' }}>
                </div>

                <div class="col-md-4">
                    <label for="TripEndDate" class="form-label">Trip End Date</label>
                    <input type="date" name="TripEndDate" id="TripEndDate" class="form-control"
                           value="{{ old('TripEndDate', $parentTrip->TripEndDate ?? $tripLog->TripEndDate ?? '') }}"
                        {{ $parentTrip ? 'readonly' : '' }}>
                </div>

                <div class="col-md-4">
                    <label for="EndTime" class="form-label">End Time</label>
                    <input type="time" name="EndTime" id="EndTime" class="form-control"
                           value="{{ old('EndTime', $parentTrip->EndTime ?? $tripLog->EndTime ?? '') }}"
                        {{ $parentTrip ? 'readonly' : '' }}>
                </div>

                {{-- Locations --}}
                <div class="col-md-4">
                    <label for="StartLocation" class="form-label">Start Location</label>
                    <input type="text" name="StartLocation" id="StartLocation"
                           class="form-control"
                           value="{{ old('StartLocation', $parentTrip->StartLocation ?? $tripLog->StartLocation ?? '') }}"
                           maxlength="255" {{ $parentTrip ? 'readonly' : '' }}>
                </div>

                <div class="col-md-4">
                    <label for="EndLocation" class="form-label">End Location</label>
                    <input type="text" name="EndLocation" id="EndLocation"
                           class="form-control"
                           value="{{ old('EndLocation', $parentTrip->EndLocation ?? $tripLog->EndLocation ?? '') }}"
                           maxlength="255" {{ $parentTrip ? 'readonly' : '' }}>
                </div>

                {{-- Notes --}}
                <div class="col-12">
                    <label for="Notes" class="form-label">Notes</label>
                    <textarea name="Notes" id="Notes" class="form-control" rows="3" 
                              {{ $parentTrip ? 'readonly' : '' }}>{{ old('Notes', $parentTrip->Notes ?? $tripLog->Notes ?? '') }}</textarea>
                </div>
            </div>

            @if ($parentTrip)
                {{-- ================== Child Trips ================== --}}
                <div class="mt-5">
                    <h5 class="fw-bold">🚌 Child Trips</h5>
                    <hr>

                    <div id="childTripsContainer">
                        {{-- Display existing child trips if any --}}
                        @if(isset($parentTrip->childTrips) && $parentTrip->childTrips->count() > 0)
                            @foreach($parentTrip->childTrips as $index => $childTrip)
                                <div class="child-trip-row p-3 mb-3 border rounded">
                                    <h6 class="fw-bold">Child Trip #{{ $index + 1 }}</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Start Location</label>
                                            <input type="text" name="childTrips[{{ $index }}][StartLocation]" class="form-control"
                                                value="{{ $childTrip->StartLocation }}" maxlength="255" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">End Location</label>
                                            <input type="text" name="childTrips[{{ $index }}][EndLocation]" class="form-control"
                                                value="{{ $childTrip->EndLocation }}" maxlength="255" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Trip Start Date<span class="text-danger">*</span></label>
                                            <input type="date" name="childTrips[{{ $index }}][TripStartDate]" class="form-control"
                                                value="{{ $childTrip->TripStartDate }}" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Trip End Date<span class="text-danger">*</span></label>
                                            <input type="date" name="childTrips[{{ $index }}][TripEndDate]" class="form-control"
                                                value="{{ $childTrip->TripEndDate }}" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Start Time</label>
                                            <input type="time" name="childTrips[{{ $index }}][StartTime]" class="form-control"
                                                value="{{ $childTrip->StartTime }}" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">End Time</label>
                                            <input type="time" name="childTrips[{{ $index }}][EndTime]" class="form-control"
                                                value="{{ $childTrip->EndTime }}" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Purpose</label>
                                            <input type="text" name="childTrips[{{ $index }}][Purpose]" class="form-control"
                                                value="{{ $childTrip->Purpose }}" maxlength="255" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Notes</label>
                                            <textarea name="childTrips[{{ $index }}][Notes]" class="form-control" readonly>{{ $childTrip->Notes }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    {{-- Only show add button if we're creating new child trips --}}
                    @if(!isset($parentTrip->childTrips) || $parentTrip->childTrips->count() == 0)
                        <button type="button" class="btn btn-outline-primary mt-3" id="addChildTripBtn">
                            ➕ Add Child Trip
                        </button>
                    @endif
                </div>
            @endif

            {{-- Save --}}
            <div class="mt-4">
                <button class="btn btn-success" type="submit">💾 Save Trip</button>
                <a href="{{ route('fleet.trip_logs.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        const tripTypeSelect = document.getElementById('TripType');
        const tripCodeSelect = document.getElementById('TripCode');
        const tripStartDateInput = document.getElementById('TripStartDate');
        const tripEndDateInput = document.getElementById('TripEndDate');
        const startTimeInput = document.getElementById('StartTime');
        const endTimeInput = document.getElementById('EndTime');
        const startLocationInput = document.getElementById('StartLocation');
        const endLocationInput = document.getElementById('EndLocation');
        const childTripsContainer = document.getElementById('childTripsContainer');
        const addChildTripBtn = document.getElementById('addChildTripBtn');

        const tripCodeContainer = document.getElementById('tripCodeContainer');
        const purposeContainer = document.getElementById('purposeContainer');
        const purposeInput = document.getElementById('Purpose');

        // Set minimum dates to today
        const today = new Date().toISOString().split('T')[0];
        if (tripStartDateInput && !tripStartDateInput.value) {
            tripStartDateInput.min = today;
        }
        if (tripEndDateInput) {
            tripEndDateInput.min = today;
        }

        /**
         * Load approved transfers or campaigns into TripCode select
         */
        function loadApprovedTrips() {
            // Only run if we're not in parent trip view
            @if(!$parentTrip)
                let selectedText = tripTypeSelect.options[tripTypeSelect.selectedIndex]?.text?.toLowerCase() || '';
                if (tripCodeSelect) {
                    tripCodeSelect.innerHTML = '<option value="">-- Select Trip Code --</option>';
                }

                // Reset containers
                if (tripCodeContainer && !tripCodeContainer.classList.contains('d-none')) {
                    tripCodeContainer.classList.remove('d-none');
                }
                purposeContainer.classList.add('d-none');
                
                if (tripCodeSelect) {
                    tripCodeSelect.required = false;
                }
                purposeInput.required = false;

                // If "Other" -> show Purpose field instead
                if (selectedText.includes('other')) {
                    tripCodeContainer.classList.add('d-none');
                    purposeContainer.classList.remove('d-none');
                    purposeInput.required = true;
                    return;
                }

                const selectedId = "{{ old('TripCode', $tripLog->TripCode ?? '') }}";

                if (selectedText.includes('inventory transfer')) {
                    fetch("{{ route('fleet.trip_logs.approved_transfers') }}")
                        .then(res => res.json())
                        .then(data => {
                            if (tripCodeSelect) {
                                data.forEach(trf => {
                                    let opt = document.createElement('option');
                                    opt.value = trf.Id;
                                    opt.text = `${trf.TransferId} (${trf.FromBranch} → ${trf.ToBranch})`;
                                    opt.dataset.transferdate = trf.TransferDate;
                                    opt.dataset.frombranch = trf.FromBranch ?? '';
                                    opt.dataset.tobranch = trf.ToBranch ?? '';
                                    if (opt.value == selectedId) opt.selected = true;
                                    tripCodeSelect.appendChild(opt);
                                });
                                if (tripCodeSelect.value) tripCodeSelect.dispatchEvent(new Event('change'));
                            }
                        });
                }

                if (selectedText.includes('marketing campaign')) {
                    fetch("{{ route('fleet.trip_logs.approved_campaigns') }}")
                        .then(res => res.json())
                        .then(data => {
                            if (tripCodeSelect) {
                                data.forEach(cmp => {
                                    let opt = document.createElement('option');
                                    opt.value = cmp.Id;
                                    opt.text = `${cmp.PlannerID} - ${cmp.Name}`;
                                    opt.dataset.starton = cmp.StartOn;
                                    opt.dataset.endon = cmp.EndOn;
                                    opt.dataset.activities = JSON.stringify(cmp.Activities ?? []);
                                    if (opt.value == selectedId) opt.selected = true;
                                    tripCodeSelect.appendChild(opt);
                                });
                                if (tripCodeSelect.value) tripCodeSelect.dispatchEvent(new Event('change'));
                            }
                        });
                }
            @endif
        }

        if (tripTypeSelect && !tripTypeSelect.disabled) {
            tripTypeSelect.addEventListener('change', loadApprovedTrips);
        }

        if (tripCodeSelect) {
            tripCodeSelect.addEventListener('change', function () {
                let selected = this.options[this.selectedIndex];
                if (!selected || !selected.value) return;

                // Handle transfers
                if (selected.dataset.transferdate) {
                    tripStartDateInput.value = selected.dataset.transferdate;
                    startLocationInput.value = selected.dataset.frombranch;
                    endLocationInput.value = selected.dataset.tobranch;
                }

                // Handle marketing campaigns
                if (selected.dataset.starton) {
                    tripStartDateInput.value = selected.dataset.starton;
                    tripEndDateInput.value = selected.dataset.endon;

                    let activities = [];
                    try {
                        activities = JSON.parse(selected.dataset.activities || '[]');
                    } catch {
                    }

                    // Auto-create child trips for each activity
                    if (childTripsContainer) {
                        childTripsContainer.innerHTML = '';
                        activities.forEach((act, i) => addChildTripRow(i, act));
                    }
                }
            });
        }

        /**
         * Add a child trip row
         */
        function addChildTripRow(index, activity = {}) {
            const selectedTripTypeText = tripTypeSelect?.options[tripTypeSelect.selectedIndex]?.text || '';

            const wrapper = document.createElement('div');
            wrapper.classList.add('child-trip-row', 'p-3', 'mb-3', 'border', 'rounded');

            wrapper.innerHTML = `
        <h6 class="fw-bold">Child Trip #${index + 1}</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Start Location</label>
                <input type="text" name="childTrips[${index}][StartLocation]" class="form-control"
                    value="${activity.StartLocation ?? ''}" maxlength="255">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Location</label>
                <input type="text" name="childTrips[${index}][EndLocation]" class="form-control"
                    value="${activity.EndLocation ?? ''}" maxlength="255">
            </div>
            <div class="col-md-4">
                <label class="form-label">Trip Start Date<span class="text-danger">*</span></label>
                <input type="date" name="childTrips[${index}][TripStartDate]" class="form-control child-start-date"
                    value="${activity.StartOn ?? ''}" required min="${today}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Trip End Date<span class="text-danger">*</span></label>
                <input type="date" name="childTrips[${index}][TripEndDate]" class="form-control child-end-date"
                    value="${activity.EndOn ?? ''}" required min="${today}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Start Time</label>
                <input type="time" name="childTrips[${index}][StartTime]" class="form-control child-start-time"
                    value="${activity.StartTime ?? ''}">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Time</label>
                <input type="time" name="childTrips[${index}][EndTime]" class="form-control child-end-time"
                    value="${activity.EndTime ?? ''}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Purpose</label>
                <input type="text" name="childTrips[${index}][Purpose]" class="form-control"
                    value="${activity.Purpose ?? selectedTripTypeText}" maxlength="255">
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="childTrips[${index}][Notes]" class="form-control">${activity.Notes ?? ''}</textarea>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm mt-2 remove-child">Remove</button>
    `;

            // Add validation for child trip dates and times
            const startDateInput = wrapper.querySelector('.child-start-date');
            const endDateInput = wrapper.querySelector('.child-end-date');
            const startTimeInput = wrapper.querySelector('.child-start-time');
            const endTimeInput = wrapper.querySelector('.child-end-time');

            startDateInput?.addEventListener('change', function() {
                endDateInput.min = this.value;
            });

            startTimeInput?.addEventListener('change', function() {
                if (startDateInput.value === endDateInput.value && endTimeInput.value) {
                    if (this.value > endTimeInput.value) {
                        endTimeInput.value = '';
                    }
                }
            });

            wrapper.querySelector('.remove-child').addEventListener('click', () => wrapper.remove());
            if (childTripsContainer) {
                childTripsContainer.appendChild(wrapper);
            }
        }

        // Manual add button (empty child trip)
        addChildTripBtn?.addEventListener('click', () => {
            const index = childTripsContainer.querySelectorAll('.child-trip-row').length;
            addChildTripRow(index);
        });

        // Validate end date is after start date
        tripStartDateInput?.addEventListener('change', function() {
            if (tripEndDateInput.value && tripEndDateInput.value < this.value) {
                tripEndDateInput.value = this.value;
            }
            tripEndDateInput.min = this.value;
        });

        // Validate end time is after start time when dates are the same
        startTimeInput?.addEventListener('change', function() {
            if (tripStartDateInput.value === tripEndDateInput.value && endTimeInput.value) {
                if (this.value > endTimeInput.value) {
                    endTimeInput.value = '';
                }
            }
        });

        // Run on page load
        window.addEventListener('DOMContentLoaded', () => {
            @if(!$parentTrip)
                if (!tripStartDateInput.value) {
                    tripStartDateInput.value = today;
                }
                loadApprovedTrips();
            @endif
        });
    </script>
@endsection