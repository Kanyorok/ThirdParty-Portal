@extends('layouts.app')
@section('title', 'Insurance Referrals')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3 text-end">
        <a href="{{ route('bancassurance.referrals.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Referral
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm align-middle"  id="referralTable">
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
                        <td>{{ $loop->iteration ?? '-'}}</td>
                        <td>{{ $referral->ClientName ?? '-'}}</td>
                        <td>{{ $referral->insuranceProduct->Name ?? '-' }}</td>
                        <td>{{ $referral->preferredInsurer->Name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $referral->Status->badgeColor() }}">
                                {{ $referral->Status->label() ?? '-'}}
                            </span>
                        </td>
                        <td>{{ $referral->assignedToUser->Name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($referral->ReferralDate)->format('d/m/Y') ?? '-'}}</td>
                        <td>
                            <a href="{{ route('bancassurance.referrals.show', $referral->Id) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('bancassurance.referrals.edit', $referral->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('bancassurance.referrals.destroy', $referral->Id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete the referrence?');">
                                Delete
                            </button>
                        </form>
                        </td>
                    </tr>
                @empty
            @endforelse
            </tbody>
        </table>
    </div>
</div>

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
@endsection
