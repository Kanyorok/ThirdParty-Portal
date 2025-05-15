@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Clarification Requests</h4>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender ID</th>
                    <th>Vendor ID</th>
                    <th>Question</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Responded</th>
                    <th>Published</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clarifications as $index => $clarification)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $clarification->TenderID }}</td>
                        <td>{{ $clarification->VendorID }}</td>
                        <td>{{ $clarification->Question }}</td>
                        <td>
                            <span class="badge {{ $clarification->Answer ? 'bg-success' : 'bg-warning' }}">
                                {{ $clarification->Answer ? 'Responded' : 'Pending' }}
                            </span>
                        </td>
                        <td>{{ $clarification->QuestionDate->format('Y-m-d') }}</td>
                        <td>{{ $clarification->AnswerDate ? $clarification->AnswerDate->format('Y-m-d') : '-' }}</td>
                        <td>
                            <span class="badge {{ $clarification->ISPUBLISHEDTOALL ? 'bg-success' : 'bg-secondary' }}">
                                {{ $clarification->ISPUBLISHEDTOALL ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>
                            @if ($clarification->Answer)
                                <a href="{{ route('tenderclarification.create', $clarification->ClarificationID) }}" class="btn btn-sm btn-outline-success">Edit</a>
                            @else
                                <a href="{{ route('tenderclarification.create', $clarification->ClarificationID) }}" class="btn btn-sm btn-outline-primary">Respond</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No clarifications found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
