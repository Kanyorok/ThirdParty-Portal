@extends('layouts.app')
@section('title', 'Referral Status Tracker')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📍 Referral Status Tracker</h4>

        <div class="card mb-4">
            <div class="card-header">Client Details</div>
            <div class="card-body">
                <p><strong>Name:</strong> Jane Njeri</p>
                <p><strong>Phone:</strong> 0722000000</p>
                <p><strong>Email:</strong> jane@example.com</p>
                <p><strong>Client ID:</strong> 12345678</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Referral Info</div>
            <div class="card-body">
                <p><strong>Branch:</strong> Nairobi Branch</p>
                <p><strong>Referring Officer:</strong> John Mwangi</p>
                <p><strong>Bank Product:</strong> Loan</p>
                <p><strong>Suggested Cover:</strong> Credit Life</p>
                <p><strong>Referral Notes:</strong> Client taking a 3-year loan</p>
                <p><strong>Status:</strong>
                    <span class="badge bg-warning text-dark">Pending Review</span>
                </p>
                <p><strong>Created At:</strong> 2025-07-08</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Referral Progress</div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item">
                        <strong>Referral Submitted</strong> – 2025-07-08 by John Mwangi
                    </li>
                    <li class="list-group-item">
                        <strong>Reviewed by Officer</strong> – 2025-07-09 (Status: Under Review)
                    </li>
                    <li class="list-group-item">
                        <strong>Proposal Initiated</strong> – Pending
                    </li>
                </ul>
            </div>
        </div>

        @if(true)
            {{-- If proposal or policy is linked --}}
            <div class="card">
                <div class="card-header">Linked Policy Info</div>
                <div class="card-body">
                    <p><strong>Proposal No:</strong> PROP-202507001</p>
                    <p><strong>Status:</strong> Proposal Created</p>
                    <a href="#" class="btn btn-sm btn-primary">View Proposal</a>
                </div>
            </div>
        @endif
    </div>
@endsection
