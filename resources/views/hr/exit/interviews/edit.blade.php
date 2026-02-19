@extends('layouts.app')

@section('title', 'Exit Interview')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Exit Interview</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.exit.requests.show', $exit->Id) }}">Back</a>
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
            <div class="row g-3">
                <div class="col-md-4"><strong>Exit No:</strong> {{ $exit->ExitNo }}</div>
                <div class="col-md-4"><strong>Employee:</strong> {{ $exit->employee?->FirstName }} {{ $exit->employee?->LastName }}</div>
                <div class="col-md-4"><strong>Status:</strong> {{ $exit->Status }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('hr.exit.interviews.store', $exit->Id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Interviewer</label>
                        <select name="InterviewerID" class="form-select">
                            <option value="">Select</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->Id }}" @selected(old('InterviewerID', $interview->InterviewerID ?? null) == $employee->Id)>
                                    {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeNo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Interview Date</label>
                        <input type="date" name="InterviewDate" class="form-control" value="{{ old('InterviewDate', $interview?->InterviewDate?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mode</label>
                        <input type="text" name="Mode" class="form-control" value="{{ old('Mode', $interview->Mode ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Attrition Reason</label>
                        <input type="text" name="AttritionReason" class="form-control" value="{{ old('AttritionReason', $interview->AttritionReason ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="Notes" class="form-control" rows="4">{{ old('Notes', $interview->Notes ?? '') }}</textarea>
                    </div>
                </div>

                @if($questions->isNotEmpty())
                    <hr class="my-4">
                    <h5 class="mb-3">Exit Interview Questions</h5>
                    @foreach($questions as $question)
                        <div class="mb-3">
                            <label class="form-label">{{ $loop->iteration }}. {{ $question->Question }}</label>
                            <textarea name="Answers[{{ $question->Id }}]" class="form-control" rows="3">{{ old('Answers.' . $question->Id, $responses[$question->Id]->Answer ?? '') }}</textarea>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info mt-4 mb-0">
                        No exit interview questions configured yet. Add them in
                        <a href="{{ route('hr.config.exit-interview-questions.index') }}" class="alert-link">Exit Interview Questions</a>.
                    </div>
                @endif

                <div class="mt-4 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Interview</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
