@extends('layouts.app')

@section('title', 'Offers')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Offers</h2>
        <a class="btn btn-primary" href="{{ route('hr.recruitment.offers.create') }}">+ New Offer</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Job</th>
                            <th>Offer Date</th>
                            <th>Salary</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offers as $offer)
                            <tr>
                                <td>{{ $offer->application?->applicant?->FirstName }} {{ $offer->application?->applicant?->LastName }}</td>
                                <td>{{ $offer->application?->opening?->Title ?? '-' }}</td>
                                <td>{{ $offer->OfferDate ? \Carbon\Carbon::parse($offer->OfferDate)->format('Y-m-d') : '-' }}</td>
                                <td>{{ $offer->SalaryOffered ? number_format($offer->SalaryOffered, 2) : '-' }}</td>
                                <td>{{ $offer->Status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.offers.show', $offer->Id) }}">View</a>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.recruitment.offers.edit', $offer->Id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No offers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3">
        {{ $offers->links() }}
    </div>
</div>
@endsection
