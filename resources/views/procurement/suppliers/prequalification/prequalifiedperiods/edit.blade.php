@extends('layouts.app')

@section('title', 'Edit Prequalification Period')

@section('content')
    <div class="card">
        <div class="card-header bg-info text-dark">Edit Prequalification Period</div>
        <div class="card-body">

            <form method="POST" action="{{ route('preqrounds.update', $period->Id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Round Name</label>
                    <input type="text" name="Title" value="{{ old('Title', $period->Title) }}" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="start-date">Start Date</label>
                    <input type="text" id="start-date" name="StartDate"
                           value="{{ old('StartDate', \Carbon\Carbon::parse($period->StartDate)->format('d/m/Y')) }}"
                           class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="end-date">End Date</label>
                    <input type="text" id="end-date" name="EndDate"
                           value="{{ old('EndDate', \Carbon\Carbon::parse($period->EndDate)->format('d/m/Y')) }}"
                           class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description / Notes</label>
                    <textarea name="Description" class="form-control"
                              rows="3">{{ old('Description', $period->Description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Max Vendors to Prequalify per Category</label>
                    <input type="number" name="MaxVendors" class="form-control"
                           value="{{ old('MaxVendors', $period->MaxVendors) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="Status" class="form-select">
                        @foreach(\App\Enums\Procurement\PrequalificationPeriodEnum::cases() as $status)
                            <option
                                value="{{ $status->value }}" {{ old('Status', $period->Status->Label()) == $status->value ? 'selected' : '' }}>
                                {{ $status->Label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="{{ route('preqrounds.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#start-date", {
            dateFormat: "d/m/Y",
            allowInput: true
        });

        flatpickr("#end-date", {
            dateFormat: "d/m/Y",
            allowInput: true
        });
    </script>

@endsection
