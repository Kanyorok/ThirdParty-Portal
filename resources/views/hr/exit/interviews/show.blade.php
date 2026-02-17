@extends('layouts.app')

@section('title', 'Exit Interview')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Exit Interview</h2>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('hr.exit.interviews.index') }}">Back</a>
            @if($exit->Id)
                <a class="btn btn-outline-primary" href="{{ route('hr.exit.interviews.edit', $exit->Id) }}">Edit</a>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Exit No:</strong> {{ $exit->ExitNo }}</div>
                <div class="col-md-4"><strong>Employee:</strong> {{ $exit->employee?->FirstName }} {{ $exit->employee?->LastName }}</div>
                <div class="col-md-4"><strong>Status:</strong> {{ $exit->Status }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            @if(!$interview)
                <div class="alert alert-info mb-0">No interview details captured yet.</div>
            @else
                <div class="row g-3">
                    <div class="col-md-6"><strong>Interviewer:</strong> {{ $interview->interviewer?->FirstName }} {{ $interview->interviewer?->LastName }}</div>
                    <div class="col-md-6"><strong>Interview Date:</strong> {{ $interview->InterviewDate?->format('Y-m-d') ?? '-' }}</div>
                    <div class="col-md-6"><strong>Mode:</strong> {{ $interview->Mode ?? '-' }}</div>
                    <div class="col-md-6"><strong>Attrition Reason:</strong> {{ $interview->AttritionReason ?? '-' }}</div>
                    <div class="col-12"><strong>Notes:</strong> {{ $interview->Notes ?? '-' }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Exit Interview Questions</h5>
            @if($questions->isEmpty())
                <div class="alert alert-info mb-0">No exit interview questions configured.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Question</th>
                                <th>Answer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($rowCount = 0)
                            @foreach($questions as $question)
                                @php($answer = trim((string)($responses[$question->Id]->Answer ?? '')))
                                @continue($answer === '')
                                @php($rowCount++)
                                <tr>
                                    <td>{{ $rowCount }}</td>
                                    <td>{{ $question->Question }}</td>
                                    <td>{{ $answer }}</td>
                                </tr>
                            @endforeach
                            @if($rowCount === 0)
                                <tr>
                                    <td colspan="3" class="text-center">No answers recorded yet.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
