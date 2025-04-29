@extends('layouts.app')

@section('title', 'RFQs List')

@section('content')
<div class="container">
    <h3>All RFQs</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('rfqs.create') }}" class="btn btn-primary mb-3">+ New RFQ</a>

    @if($rfqs->count())
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Quotation Number</th>
                    <th>Quotation Status</th>
                    <th>RFQ Category</th>
                    <th>Submission Deadline</th>
                    <th>Created On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rfqs as $rfq)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $rfq->RFQNumber ?? '-' }}</td>
                        <td>{{ $rfq->Status ?? '-' }}</td>
                        <td>{{ $rfq->category->Name ?? '-' }}</td>
                        <td>
                            {{ $rfq->SubmissionDeadline ? \Carbon\Carbon::parse($rfq->SubmissionDeadline)->format('d M Y') : '-' }}
                        </td>
                        <td>{{ $rfq->CreatedOn ? \Carbon\Carbon::parse($rfq->CreatedAt)->format('d M Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('rfqs.show', $rfq->Id) }}" class="btn btn-sm btn-info">View</a>
                            {{-- Add edit/delete buttons if needed --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No RFQs created yet.</p>
    @endif
</div>
@endsection
