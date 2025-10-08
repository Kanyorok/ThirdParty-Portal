@extends('layouts.app')

@section('title', 'Insurance Referrals')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Add New Referral Button --}}
    <div class="mb-3 text-end">
        <a href="{{ route('bancassurance.referrals.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Referral
        </a>
    </div>

    {{-- Page Description --}}
    <div class="mb-3">
        <p><small>The list below consists of insurance referrals:</small></p>
    </div>

    {{-- Referrals Table --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm align-middle" id="referralTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Client Name</th>
                    <th>Product</th>
                    <th>Insurer</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Referral Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($referrals as $referral)
                    <tr>
                        <td>{{ $loop->iteration ?? '-' }}</td>
                        <td>{{ $referral->ClientName ?? '-' }}</td>
                        <td>{{ $referral->insuranceProduct->Name ?? '-' }}</td>
                        <td>{{ $referral->preferredInsurer->Name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $referral->Status->badgeColor() }}">
                                {{ $referral->Status->label() ?? '-' }}
                            </span>
                        </td>
                        <td>{{ $referral->assignedToUser->Name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($referral->ReferralDate)->format('d/m/Y') ?? '-' }}</td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('bancassurance.referrals.show', $referral->Id) }}" 
                               class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="{{ route('bancassurance.referrals.edit', $referral->Id) }}" 
                               class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            <form action="{{ route('bancassurance.referrals.destroy', $referral->Id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this referral?')" 
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No referrals found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#referralTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
