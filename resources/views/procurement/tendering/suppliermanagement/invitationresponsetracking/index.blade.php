@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
<a href="{{ route('tenderresponse.create') }}" class="btn btn-success">➕ Add Response</a>
    <h4>Invitation Response Tracker</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped mt-3">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Ref</th>
                    <th>Supplier</th>
                    <th>Sent On</th>
                    <th>Status</th>
                    <th>Responded On</th>
                    <th>Remarks</th>
                </tr>
            </thead>

            <tbody>
    @forelse ($invitations as $index => $invitation)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $invitation->TenderID }}</td>
            <td>{{ $invitation->SupplierID  }}</td>
            <td>{{ \Carbon\Carbon::parse($invitation->InvitationDate)->format('Y-m-d') }}</td>
            <td>
                @if($invitation->ResponseStatus === 'Accepted')
                    <span class="badge bg-success">Accepted</span>
                @elseif($invitation->ResponseStatus === 'Declined')
                    <span class="badge bg-danger">Declined</span>
                @else
                    <span class="badge bg-secondary">{{ $invitation->ResponseStatus }}</span>
                @endif
            </td>
            <td>{{ \Carbon\Carbon::parse($invitation->ResponseDate)->format('Y-m-d') }}</td>
            <td>{{ $invitation->DeclineReason ?? 'Ready to submit bid' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="text-center">No invitations found.</td>
        </tr>
    @endforelse
</tbody>

           

        </table>
    </div>
</div>
@endsection