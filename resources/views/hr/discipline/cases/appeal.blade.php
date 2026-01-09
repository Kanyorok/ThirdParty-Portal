@extends('layouts.app')

@section('title', 'Appeal')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Appeal</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.discipline.cases.show', $case->Id) }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form action="{{ route('hr.discipline.cases.appeal.store', $case->Id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Appeal Date</label>
                        <input type="date" name="AppealDate" class="form-control" value="{{ old('AppealDate', optional($appeal->AppealDate)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Deadline Date</label>
                        <input type="date" name="DeadlineDate" class="form-control" value="{{ old('DeadlineDate', optional($appeal->DeadlineDate)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hearing Date</label>
                        <input type="date" name="HearingDate" class="form-control" value="{{ old('HearingDate', optional($appeal->HearingDate)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Appeal Grounds *</label>
                        <textarea name="Grounds" class="form-control" rows="4" required>{{ old('Grounds', $appeal->Grounds ?? '') }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <input type="text" name="Status" class="form-control" value="{{ old('Status', $appeal->Status ?? 'Submitted') }}">
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Appeal Documents</label>
                        <input type="file" name="AppealDocuments[]" class="form-control" multiple>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Appeal Panel</label>
                        <select name="PanelMembers[]" class="form-select" multiple>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->Id }}">{{ $employee->FirstName }} {{ $employee->LastName }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple.</small>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Appeal</button>
                </div>
            </form>
        </div>
    </div>

    @if($appeal)
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Appeal Decision</h5>
                <form action="{{ route('hr.discipline.cases.appeal.decide', $case->Id) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Outcome *</label>
                            <select name="Outcome" class="form-select" required>
                                <option value="">Select</option>
                                @foreach(['Upheld','Modified','Overturned'] as $outcome)
                                    <option value="{{ $outcome }}" @selected(old('Outcome', $appeal->Outcome ?? '') === $outcome)>{{ $outcome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Decision Summary</label>
                            <input type="text" name="DecisionSummary" class="form-control" value="{{ old('DecisionSummary', $appeal->DecisionSummary ?? '') }}">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-outline-primary">Save Decision</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
