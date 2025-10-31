@extends('layouts.app')
@section('title', 'Edit Calendar Entry')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <h4 class="mb-3">✏️ Edit Compliance Calendar Entry</h4>

        <form method="POST" action="{{ route('legal.compliance.calendar.update', $entry->Id) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="Title" value="{{ $entry->Title }}" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Obligation</label>
                    <select name="ObligationID" class="form-select">
                        <option value="">-- None --</option>
                        @foreach($obligations as $id => $title)
                            <option value="{{ $id }}" {{ $entry->ObligationID == $id ? 'selected' : '' }}>
                                {{ $title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="StartDate" value="{{ $entry->StartDate }}" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="EndDate" value="{{ $entry->EndDate }}" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Owner</label>
                    <select name="OwnerID" class="form-select">
                        <option value="">-- None --</option>
                        @foreach($owners as $id => $name)
                            <option value="{{ $id }}" {{ $entry->OwnerID == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="IsRecurring" class="form-check-input"
                               value="1" {{ $entry->IsRecurring ? 'checked' : '' }}>
                        <label class="form-check-label">Recurring</label>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Recurrence Type</label>
                    <select name="RecurrenceType" class="form-select">
                        <option value="">-- None --</option>
                        <option value="Monthly" {{ $entry->RecurrenceType == 'Monthly' ? 'selected' : '' }}>Monthly
                        </option>
                        <option value="Quarterly" {{ $entry->RecurrenceType == 'Quarterly' ? 'selected' : '' }}>
                            Quarterly
                        </option>
                        <option value="Annually" {{ $entry->RecurrenceType == 'Annually' ? 'selected' : '' }}>Annually
                        </option>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="Description" class="form-control" rows="3">{{ $entry->Description }}</textarea>
                </div>

                <div class="col-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="IsActive" class="form-check-input"
                               value="1" {{ !$entry->DeletedOn ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            </div>

            <button class="btn btn-success">💾 Update</button>
            <a href="{{ route('legal.compliance.calendar.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
