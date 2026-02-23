@extends('layouts.app')

@section('title', 'Offer Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Offer Details</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.offers.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div><strong>Applicant:</strong> {{ $offer->application?->applicant?->FirstName }} {{ $offer->application?->applicant?->LastName }}</div>
                    <div><strong>Job Opening:</strong> {{ $offer->application?->opening?->Title ?? '-' }}</div>
                    <div><strong>Status:</strong> {{ $offer->Status }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Offer Date:</strong> {{ $offer->OfferDate ? \Carbon\Carbon::parse($offer->OfferDate)->format('Y-m-d') : '-' }}</div>
                    <div><strong>Salary Offered:</strong> {{ $offer->SalaryOffered ? number_format($offer->SalaryOffered, 2) : '-' }}</div>
                </div>
            </div>
            @if($offer->Benefits)
                <div class="mt-3"><strong>Benefits:</strong> {{ $offer->Benefits }}</div>
            @endif
            @if($offer->Notes)
                <div class="mt-2"><strong>Notes:</strong> {{ $offer->Notes }}</div>
            @endif
            <div class="mt-3 d-flex flex-wrap gap-2">
                @if($offer->Status === 'Draft' || $offer->Status === 'Submitted')
                    <form method="POST" action="{{ route('hr.recruitment.offers.approve', $offer->Id) }}">
                        @csrf
                        <button class="btn btn-outline-success" type="submit">Approve</button>
                    </form>
                @endif
                @if($offer->Status === 'Approved')
                    <form method="POST" action="{{ route('hr.recruitment.offers.send', $offer->Id) }}">
                        @csrf
                        <button class="btn btn-outline-primary" type="submit">Send Offer</button>
                    </form>
                @endif
                @if(in_array($offer->Status, ['Approved','Sent'], true))
                    <form method="POST" action="{{ route('hr.recruitment.offers.accept', $offer->Id) }}">
                        @csrf
                        <button class="btn btn-outline-success" type="submit">Mark Accepted</button>
                    </form>
                    <form method="POST" action="{{ route('hr.recruitment.offers.reject', $offer->Id) }}">
                        @csrf
                        <button class="btn btn-outline-danger" type="submit">Mark Rejected</button>
                    </form>
                @endif
                @if($offer->onboarding)
                    <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.onboarding.show', $offer->onboarding->Id) }}">View Onboarding</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
