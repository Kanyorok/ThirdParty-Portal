@extends('layouts.app')

@section('title', 'Exit Interview Questions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Exit Interview Questions</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.exit-interview-questions.create') }}">+ New Question</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Sequence</th>
                            <th>Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questions as $question)
                            <tr>
                                <td>{{ $question->Question }}</td>
                                <td>{{ $question->Sequence }}</td>
                                <td>{{ $question->IsActive ? 'Yes' : 'No' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.exit-interview-questions.edit', $question->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No questions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
