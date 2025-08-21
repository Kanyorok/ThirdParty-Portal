@extends('layouts.app')
@section('title', 'Policy Endorsements')

@section('content')
<div class="container mt-4">
    <h4>📋 Policy Endorsements</h4>

    <table class="table table-bordered mt-3">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Policy Number</th>
                <th>Customer</th>
                <th>Type</th>
                <th>Request Date</th>
                <th>Effective Date</th>
                <th>Description</th>
                <th>Attachment</th>
                <th>Logged</th>
            </tr>
        </thead>
        <tbody>
            @forelse($endorsements as $endorsement)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $endorsement->PolicyNumber }}</td>
                <td>{{ $endorsement->CustomerName }}</td>
                <td>{{ $endorsement->EndorsementType }}</td>
                <td>{{ \Carbon\Carbon::parse($endorsement->RequestDate)->format('d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($endorsement->EffectiveDate)->format('d M Y') }}</td>
                <td>{{ $endorsement->Description }}</td>
                <td>
                    @if($endorsement->SupportingDocumentPath)
                        <a href="{{ asset('storage/' . $endorsement->SupportingDocumentPath) }}" target="_blank">📎 View</a>
                    @else
                        —
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($endorsement->CreatedAt)->format('d M Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted">No endorsements found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
