@php
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.app')

@section('title', 'Manual Bid Submissions')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    /* DataTables often needs help with Bootstrap 5 spacing */
    .dataTables_wrapper {
        padding: 20px 0;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Manual Bid Submissions</h4>
        <a href="{{ route('tendersubmission.create') }}" class="btn btn-sm btn-success">+ Record Manual Submission</a>
    </div>

    <div class="table-responsive">
        <table id="bidsubmissionTable" class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Ref</th>
                    <th>Supplier</th>
                    <th>Submission Mode</th>
                    <th>Received At</th>
                    <th>Recorded By</th>
                    <th>Remarks</th>
                    <th>Documents</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($submissions as $index => $submission)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div>{{ $submission->TenderRef }}</div>
                        @if($submission->tender)
                            <div class="text-muted small">{{ $submission->tender->Title }}</div>
                        @endif
                    </td>
                    <td>
                        @if($submission->supplier && $submission->supplier->supplierMaster && $submission->supplier->supplierMaster->party)
                        {{ $submission->supplier->supplierMaster->party->TradingName ?? $submission->supplier->supplierMaster->party->ThirdPartyName }}
                        @else
                        {{ $submission->SupplierName }}
                        @endif
                    </td>
                    <td>{{ $submission->submissionMode->Description ?? 'N/A' }}</td>
                    <td>
                        {{-- Cleaned up Carbon logic --}}
                        @php
                        $receivedDate = ($submission->ReceivedAt instanceof \Carbon\Carbon || $submission->ReceivedAt instanceof \Illuminate\Support\Carbon)
                        ? $submission->ReceivedAt
                        : \Carbon\Carbon::parse($submission->ReceivedAt);
                        @endphp
                        {{ $receivedDate->format('d/m/Y') }}
                    </td>
                    <td>{{ $submission->createdByUser->Name ?? 'N/A' }}</td>
                    <td>{{ Str::limit($submission->Remarks ?? 'N/A', 30) }}</td>
                    <td>
                        @if ($submission->Documents)
                        <a href="{{ Storage::url($submission->Documents) }}" class="btn btn-sm btn-outline-secondary" download>
                            <i class="fa fa-download"></i> Download
                        </a>
                        @else
                        <span class="text-muted">No Doc</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewModal-{{ $submission->Id }}">
                                View
                            </button>
                            {{-- Removed 'hidden' so you can see it, or add logic if it's permission based --}}
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal-{{ $submission->Id }}">
                                Edit
                            </button>
                        </div>
                    </td>
                </tr>

                {{-- Modals remain inside forelse to keep scope, but ensure they are after the <tr> --}}
                @include('partials.bid_modals', ['submission' => $submission])
                {{-- Recommendation: Move modal HTML to a partial or bottom to keep table clean --}}

                @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">No submissions found.</td>
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
    $(document).ready(function() {
        // FIXED TYPO: removed spaces in arrow operator
        @if(count($submissions) > 0)
        $('#bidsubmissionTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            responsive: true
        });
        @endif
    });
</script>
@endsection