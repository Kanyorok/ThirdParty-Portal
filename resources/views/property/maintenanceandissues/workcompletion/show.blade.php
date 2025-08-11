@extends('layouts.app')

@section('title', 'Work Completion Details')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Work Completion Details</h2>

    <div class="card">
        <div class="card-header bg-primary text-white">
            Work Completion #{{ $workCompletion->request->request->RequestNumber }}
        </div>

        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Request Number</th>
                        <td>{{ $workCompletion->request->request->RequestNumber ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Assignment Type</th>
                        <td>{{ $workCompletion->request->assignmentType->Description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Completion Date</th>
                        <td>{{ \Carbon\Carbon::parse($workCompletion->CompletionDate)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Work Done Summary</th>
                        <td>{{ $workCompletion->WorkDoneSummary }}</td>
                    </tr>
                    <tr>
                        <th>Parts Used</th>
                        <td>{{ $workCompletion->PartsUsed }}</td>
                    </tr>
                    <tr>
                        <th>Cost</th>
                        <td>KES {{ number_format($workCompletion->Cost, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Final Status</th>
                        <td>{{ $workCompletion->finalstatus->Description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Created On</th>
                        <td>{{ \Carbon\Carbon::parse($workCompletion->CreatedOn)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Last Modified</th>
                        <td>{{ \Carbon\Carbon::parse($workCompletion->ModifiedOn)->format('d/m/Y') }}</td>
                    </tr>
                </tbody>
            </table>

            <a href="{{ route('workcompletion.index') }}" class="btn btn-secondary mt-3">Back to List</a>
        </div>
    </div>
</div>
@endsection
