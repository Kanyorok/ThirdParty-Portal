@extends('layouts.app')

@section('title', 'Insurance Referrals')

@section('styles')

<style>
    #referralTable thead th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .table td, .table th {
        vertical-align: middle !important;
    }
    .badge {
        font-size: 0.8rem;
    }
    .btn i {
        vertical-align: middle;
    }

    /* 🧩 Add spacing between buttons */
    .btn-group .btn {
        margin-right: 4px;
    }
    .btn-group .btn:last-child {
        margin-right: 0; /* prevent extra space at end */
    }
</style>

@endsection

@section('content')
<div class="container mt-4">

    {{-- ✅ Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-pill py-2 px-3 mb-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ✅ Header --}}
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('bancassurance.referrals.create') }}" 
        class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> New Referral
        </a>
    </div>

    <p class="text-muted small mb-3">
        <i class="bi bi-people-fill me-2 text-primary"></i>The list below displays all current insurance referrals and their respective statuses.
    </p>

    {{-- ✅ Data Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="referralTable" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
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
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $referral->customerreferral->thirdParty->ThirdPartyName ?? '-' }}</td>
                                <td>{{ $referral->insuranceProduct->Name ?? '-' }}</td>
                                <td>{{ $referral->preferredInsurer->Name ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $referral->Status->badgeColor() ?? 'secondary' }}">
                                        {{ $referral->Status->label() ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $referral->assignedToUser->Name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($referral->ReferralDate)->format('d M Y') ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- View --}}
                                        <a href="{{ route('bancassurance.referrals.show', $referral->Id) }}" 
                                           class="btn btn-outline-info btn-sm rounded-pill px-2" 
                                           title="View Referral">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        {{-- Edit --}}
                                        <a href="{{ route('bancassurance.referrals.edit', $referral->Id) }}" 
                                           class="btn btn-outline-warning btn-sm rounded-pill px-2" 
                                           title="Edit Referral">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        {{-- Delete or Locked --}}
                                        @if($referral->customerreferral()->exists())
                                            <button class="btn btn-outline-secondary btn-sm rounded-pill px-2" 
                                                    title="Referral in use">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('bancassurance.referrals.destroy', $referral->Id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to delete this referral?');"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm rounded-pill px-2" 
                                                        title="Delete Referral">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search referrals..."
            },
            columnDefs: [
                { orderable: false, targets: [7] } // Disable ordering on Actions
            ]
        });
    });
</script>
@endsection
