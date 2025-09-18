@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', '')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📑 Bid Responsiveness Overview</h4>

        <!-- Filters & Bulk Actions -->
        <div class="row mb-3">
            <div class="col-md-8">
                <form method="GET" class="row g-2">
                    <div class="col-md-4">
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
                            <option value="">All Status</option>
                            <option value="1" {{ ($filters['responsiveness_filter'] ?? '') === '1' ? 'selected' : '' }}>Responsive</option>
                            <option value="0" {{ ($filters['responsiveness_filter'] ?? '') === '0' ? 'selected' : '' }}>Non-Responsive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search Bidder" value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary w-100" type="submit">Filter</button>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" onclick="showBulkModal('approve_all')">
                        <i class="fas fa-check-double"></i> Bulk Approve
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="showBulkModal('reject_all')">
                        <i class="fas fa-times-circle"></i> Bulk Reject
                    </button>
                </div>
            </div>
        </div>
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
                            <th>Timely?</th>
                            <th>Mandatory Docs</th>
                            <th>Eligibility</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($bidResponses as $index => $bid)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $bid->supplier->thirdParty->ThirdPartyName ?? '-' }}</td>
                                <td>{{ $bid->tender->TenderNo ?? '-' }}</td>
                                <td>{{ Carbon::parse($bid->CreatedOn)->format('Y-m-d') }}</td>

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
                                    <div class="btn-group" role="group">
                                        @if($bid->bidResponsiveness)
                                            <a href="{{ route('bidresponsiveness.show', $bid->bidResponsiveness->Id) }}" 
                                               class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('bidresponsiveness.edit', $bid->bidResponsiveness->Id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('bidresponsiveness.create', $bid->id) }}"
                                               class="btn btn-sm btn-outline-secondary" title="Review">
                                                <i class="fas fa-plus"></i> Review
                                            </a>
                                        @endif
                                    </div>
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

        // Bulk operations
        function showBulkModal(action) {
            const selectedTender = document.querySelector('[name="tender_filter"]').value;
            if (!selectedTender) {
                alert('Please select a tender first using the filter dropdown.');
                return;
            }
            
            const actionText = action === 'approve_all' ? 'approve all bids as responsive' : 'reject all bids as non-responsive';
            const confirmMessage = `Are you sure you want to ${actionText} for tender ${selectedTender}?\n\nThis action cannot be undone.`;
            
            if (confirm(confirmMessage)) {
                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("bidresponsiveness.bulk") }}';
                
                form.innerHTML = `
                    @csrf
                    <input type="hidden" name="tender_id" value="${getTenderIdFromNo(selectedTender)}">
                    <input type="hidden" name="action" value="${action}">
                `;
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Helper function to get tender ID from tender number
        function getTenderIdFromNo(tenderNo) {
            // This would need to be populated from the backend
            // For now, return the tender number itself
            return tenderNo;
        }
    </script>
@endsection

