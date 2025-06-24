@extends('layouts.app')
@section('title', '')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
<h4 class="mb-4">📑 Bid Responsiveness Overview</h4>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-3">
            <select name="tender_filter" class="form-select">
                <option value="">All Tenders</option>
                @foreach($tenders as $tender)
                    <option value="{{ $tender }}" {{ ($filters['tender_filter'] ?? '') === $tender ? 'selected' : '' }}>
                        {{ $tender }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <select name="responsiveness_filter" class="form-select">
                <option value="">All Responsiveness</option>
                <option value="1" {{ ($filters['responsiveness_filter'] ?? '') === '1' ? 'selected' : '' }}>Responsive</option>
                <option value="0" {{ ($filters['responsiveness_filter'] ?? '') === '0' ? 'selected' : '' }}>Non-Responsive</option>
            </select>
        </div>

        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Search Bidder Name"
                   value="{{ $filters['search'] ?? '' }}">
        </div>

        <div class="col-md-3">
            <button class="btn btn-outline-primary w-100" type="submit">Apply Filters</button>
        </div>
    </form>
    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="responsivenessTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Bidder Name</th>
                            <th>Tender Ref</th>
                            <th>Submission Date</th>
                            <th>Mandatory Docs</th>
                            <th>Eligibility</th>
                            <th>Timely?</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($bidResponses as $index => $bid)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $bid->supplier->SupplierName ?? '-' }}</td>
                            <td>{{ $bid->tender->TenderNo ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($bid->CreatedOn)->format('Y-m-d') }}</td>
                            <td>
                                @if($bid->bidResponsiveness)
                                    {{ $bid->bidResponsiveness->SubmittedTimely ? '✅' : '❌' }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($bid->bidResponsiveness)
                                    {{ $bid->bidResponsiveness->HasMandatoryDocuments ? '✅' : '❌' }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($bid->bidResponsiveness)
                                    {{ $bid->bidResponsiveness->IsEligible ? '✅' : '❌' }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($bid->bidResponsiveness)
                                    {!! $bid->bidResponsiveness->IsResponsive ? '<span class="badge bg-success">Responsive</span>' : '<span class="badge bg-danger">Non-Responsive</span>' !!}
                                @else
                                    <span class="badge bg-secondary">Not Reviewed</span>
                                @endif
                            </td>
                            <td>
                                {{ $bid->bidResponsiveness->Remarks ?? '—' }}
                            </td>
                            <td>
                                <a href="{{ route('bidresponsiveness.create', $bid->id) }}" class="btn btn-sm btn-outline-secondary">Review</a>

                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No bid responses available.</td>
                        </tr>
                    @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        @if(!$bidResponses->isEmpty())
        $('#responsivenessTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                emptyTable: ""
            }
        });
        @endif
    });
</script>
@endsection