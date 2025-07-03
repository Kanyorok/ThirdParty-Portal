@extends('layouts.app')

@section('title', 'New Prequalification Period')

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white">New Prequalification Period</div>
        <div class="card-body">

            <form method="POST" action="{{ route('preqrounds.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Round Name</label>
                    <input type="text" name="Title" class="form-control @error('Title') is-invalid @enderror"
                           value="{{ old('Title') }}"
                           placeholder="e.g. 2025 Annual Supplier Prequalification">
                    @error('Title')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="start-date">Start Date</label>
                    <input type="text" id="start-date" name="StartDate"
                           value="{{ old('StartDate') }}"
                           class="form-control @error('StartDate') is-invalid @enderror"
                           placeholder="dd/mm/yyyy">
                    @error('StartDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="end-date">End Date</label>
                    <input type="text" id="end-date" name="EndDate"
                           value="{{ old('EndDate') }}"
                           class="form-control @error('EndDate') is-invalid @enderror"
                           placeholder="dd/mm/yyyy">
                    @error('EndDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Description / Notes</label>
                    <textarea name="Description" class="form-control @error('Description') is-invalid @enderror"
                              rows="3"
                              placeholder="Optional notes...">{{ old('Description') }}</textarea>
                    @error('Description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Max Vendors to Prequalify per Category</label>
                    <input type="number" name="MaxVendors" min="1"
                           class="form-control @error('MaxVendors') is-invalid @enderror"
                           value="{{ old('MaxVendors') }}"
                           placeholder="e.g., 10">
                    @error('MaxVendors')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="Status" class="form-select @error('Status') is-invalid @enderror">
                        @foreach (\App\Enums\Procurement\PrequalificationPeriodEnum::cases() as $status)
                            <option value="{{ $status->value }}"
                                {{ old('Status') === $status->value ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('Status')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Save Round</button>
                    <a href="{{ route('preqrounds.index') }}" class="btn btn-secondary">Back</a>
                </div>

            </form>
        </div>
    </div>

    {{-- Flatpickr Styles & Script --}}
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
