@extends('layouts.app')

@section('title', 'Edit Interview Session')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Interview Session</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.interviews.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.recruitment.interviews.update', $session->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Round *</label>
                        <input type="number" name="RoundNo" class="form-control" min="1" value="{{ old('RoundNo', $session->RoundNo) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Round Label</label>
                        <input type="text" name="RoundLabel" class="form-control" value="{{ old('RoundLabel', $session->RoundLabel) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Interview Type</label>
                        <input type="text" name="InterviewType" class="form-control" value="{{ old('InterviewType', $session->InterviewType) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Interview Date *</label>
                        <input type="date" name="InterviewDate" class="form-control" value="{{ old('InterviewDate', $session->InterviewDate ? \Carbon\Carbon::parse($session->InterviewDate)->format('Y-m-d') : '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location</label>
                        <input type="text" name="Location" class="form-control" value="{{ old('Location', $session->Location) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            @foreach($statusList as $status)
                                <option value="{{ $status }}" @selected(old('Status', $session->Status) === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <textarea name="Notes" class="form-control" rows="3">{{ old('Notes', $session->Notes) }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update Session</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
