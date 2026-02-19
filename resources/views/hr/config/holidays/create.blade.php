@extends('layouts.app')

@section('title', 'New Holiday')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Holiday</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.holidays.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.config.holidays.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Holiday Date *</label>
                        <input type="date" name="HolidayDate" class="form-control" value="{{ old('HolidayDate') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-block">Region Scope</label>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="IsGlobal" value="1" id="IsGlobal" @checked(old('IsGlobal', true))>
                            <label for="IsGlobal" class="form-check-label">Global</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="IsRegional" value="1" id="IsRegional" @checked(old('IsRegional'))>
                            <label for="IsRegional" class="form-check-label">Regional</label>
                        </div>
                    </div>
                    <div class="col-md-3" id="CountrySelectWrap">
                        <label class="form-label">Country</label>
                        <select name="CountryId" class="form-select">
                            <option value="">Select</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->Id }}" @selected(old('CountryId') == $country->Id)>{{ $country->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Applies To Religion</label>
                        <select name="AppliesToReligion" class="form-select">
                            <option value="">All</option>
                            @foreach($religions as $religion)
                                <option value="{{ $religion->Name }}" @selected(old('AppliesToReligion') == $religion->Name)>{{ $religion->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsRecurring" value="1" id="IsRecurring" @checked(old('IsRecurring'))>
                            <label for="IsRecurring" class="form-check-label">Recurring</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isGlobal = document.getElementById('IsGlobal');
        const isRegional = document.getElementById('IsRegional');
        const countryWrap = document.getElementById('CountrySelectWrap');
        const countrySelect = countryWrap.querySelector('select');

        const sync = () => {
            if (isRegional.checked) {
                isGlobal.checked = false;
            } else if (!isGlobal.checked) {
                isGlobal.checked = true;
            }
            countryWrap.style.display = isRegional.checked ? '' : 'none';
            countrySelect.required = isRegional.checked;
        };

        isGlobal.addEventListener('change', sync);
        isRegional.addEventListener('change', sync);
        sync();
    });
</script>
@endpush
@endsection
