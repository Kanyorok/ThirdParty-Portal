@extends('layouts.app')

@section('title', 'Onboarding Queue')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Onboarding Queue</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach($statusList as $status)
                            <option value="{{ $status }}" @selected(request('status') == $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Job</th>
                            <th>Offer</th>
                            <th>Status</th>
                            <th>Start</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($queues as $queue)
                            <tr>
                                <td>{{ $queue->CandidateName ?? $queue->application?->applicant?->FirstName }} {{ $queue->application?->applicant?->LastName }}</td>
                                <td>{{ $queue->application?->opening?->Title ?? '-' }}</td>
                                <td>{{ $queue->offer?->OfferDate ? \Carbon\Carbon::parse($queue->offer->OfferDate)->format('Y-m-d') : '-' }}</td>
                                <td>{{ $queue->Status }}</td>
                                <td>{{ $queue->StartDate ? \Carbon\Carbon::parse($queue->StartDate)->format('Y-m-d') : '-' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.onboarding.show', $queue->Id) }}">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No onboarding entries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3">
        {{ $queues->links() }}
    </div>
</div>
@endsection
