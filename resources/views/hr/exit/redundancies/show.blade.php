@extends('layouts.app')

@section('title', 'Redundancy Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Redundancy {{ $redundancy->RefNo }}</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.exit.redundancies.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Status:</strong> {{ $redundancy->Status }}</div>
                <div class="col-md-4"><strong>Reason:</strong> {{ $redundancy->Reason ?? '-' }}</div>
                <div class="col-md-4"><strong>Selection Method:</strong> {{ $redundancy->SelectionMethod ?? '-' }}</div>
                <div class="col-12"><strong>Criteria:</strong> {{ $redundancy->Criteria ?? '-' }}</div>
                <div class="col-md-4"><strong>Union Notified:</strong> {{ $redundancy->UnionNotified ? 'Yes' : 'No' }}</div>
                <div class="col-md-4"><strong>Union Notified On:</strong> {{ $redundancy->UnionNotifiedOn?->format('Y-m-d') ?? '-' }}</div>
                <div class="col-md-4"><strong>Labour Office Notified:</strong> {{ $redundancy->LabourOfficeNotified ? 'Yes' : 'No' }}</div>
                <div class="col-md-4"><strong>Labour Office Notified On:</strong> {{ $redundancy->LabourOfficeNotifiedOn?->format('Y-m-d') ?? '-' }}</div>
                <div class="col-12"><strong>Notes:</strong> {{ $redundancy->Notes ?? '-' }}</div>
            </div>

            @if($redundancy->Status !== 'Approved')
                <form action="{{ route('hr.exit.redundancies.approve', $redundancy->Id) }}" method="POST" class="mt-3">
                    @csrf
                    <button class="btn btn-outline-success" type="submit">Approve</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
