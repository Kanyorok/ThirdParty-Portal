@extends('layouts.app')
@section('title', 'Add Calendar Entry')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">➕ Add Compliance Calendar Entry</h4>

    <form method="POST" action="{{ route('legal.compliance.calendar.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="Title" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Obligation</label>
                <select name="ObligationID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($obligations as $id => $title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Start Date *</label>
                <input type="date" name="StartDate" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">End Date</label>
                <input type="date" name="EndDate" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Owner</label>
                <select name="OwnerID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($owners as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>


            <div class="col-md-3 mb-3">
                <div class="form-check mt-4">
                    <input type="checkbox" name="IsRecurring" class="form-check-input" value="1">
                    <label class="form-check-label">Recurring</label>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Recurrence Type</label>
                <select name="RecurrenceType" class="form-select">
                    <option value="">-- None --</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Quarterly">Quarterly</option>
                    <option value="Annually">Annually</option>
                </select>
            </div>

            <div class="col-12 mb-3">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <button class="btn btn-success">💾 Save</button>
        <a href="{{ route('legal.compliance.calendar.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
