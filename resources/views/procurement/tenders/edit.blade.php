@extends('layouts.app')
@section('title','Edit Tender')
@section('content')
<div class="container">
    <h3>Edit Tender</h3>

    <form action="{{ route('tendering-process.update', $tender->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label>Tender Number</label>
            <input type="text" class="form-control" name="TenderNumber" value="{{ $tender->TenderNumber }}" disabled>
        </div>

        <div class="form-group mb-3">
            <label>Title</label>
            <input type="text" name="Title" class="form-control" value="{{ old('Title', $tender->Title) }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Description</label>
            <textarea name="Description" class="form-control" rows="4" required>{{ old('Description', $tender->Description) }}</textarea>
        </div>

        <div class="form-group mb-3">
            <label>Procurement Mode</label>
            <select name="ProcurementModeId" class="form-control">
                @foreach($procurementModes as $mode)
                    <option value="{{ $mode->id }}" {{ $tender->ProcurementModeId == $mode->Id ? 'selected' : '' }}>
                        {{ $mode->Name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Estimated Value</label>
            <input type="number" name="EstimatedValue" class="form-control" value="{{ old('EstimatedValue', $tender->EstimatedValue) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Currency <span class="text-danger">*</span></label>
            <select name="Currency" id="Currency" class="form-control" required>
                <option value="">-- Select Currency --</option>
                @foreach($currencies as $currency => $name)
                <option value="{{ $currency }}"
                            {{ old('Currency', $tender->Currency ?? '') === $currency ? 'selected' : '' }}>
                            {{ $currency }}
                        </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Start Date</label>
            <input type="date" name="StartDate" class="form-control" value="{{ old('StartDate', $tender->StartDate) }}" required min="{{ date('Y-m-d') }}">
        </div>

        <div class="form-group mb-3">
            <label>Status</label>
            <select name="Status" class="form-control" required>
                <option value="">-- Select Status --</option>
                <option value="open" {{ old('Status', $tender->Status) == 'open' ? 'selected' : '' }}>Open</option>
                <option value="closed" {{ old('Status', $tender->Status) == 'closed' ? 'selected' : '' }}>Closed</option>
                <option value="cancelled" {{ old('Status', $tender->Status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="awarded" {{ old('Status', $tender->Status) == 'awarded' ? 'selected' : '' }}>Awarded</option>
            </select>
        </div>

        <button class="btn btn-primary">Update Tender</button>
    </form>
</div>
@endsection
