@extends('layouts.app')

@section('title', 'Prequalification Evaluations')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-primary">My Prequalification Evaluations</h4>
            <form method="POST" id="bulkPrequalifyForm" class="d-flex align-items-center gap-2">
                @csrf
                <select class="form-select form-select-sm" name="round_id" id="round_id" style="width:200px" required>
                    <option value="">Select Active Round</option>
                    @php
                    $activeRounds = \App\Models\Procurement\Prequalification\PrequalificationRound::where('Status', \App\Enums\Procurement\PrequalificationRoundEnum::Open)
                        ->where('StartDate', '<=', now())
                        ->where('EndDate', '>=', now())
                        ->get();
                    @endphp
                    @foreach($activeRounds as $round)
                        <option value="{{ $round->RoundID }}">{{ $round->Title }} (ID: {{ $round->RoundID }})</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-check-double me-1"></i> Bulk Prequalify
                </button>
            </form>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills mb-3" id="evaluationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#tab-pending" type="button" role="tab">
                        Pending
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="passed-tab" data-bs-toggle="tab" data-bs-target="#tab-passed" type="button" role="tab">
                        Passed
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="failed-tab" data-bs-toggle="tab" data-bs-target="#tab-under-review" type="button" role="tab">
                        Under Review (Failed)
                    </button>
                </li>
            </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-pending" role="tabpanel">
                    <table id="pendingTable" class="table table-striped table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Application</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Score</th>
                                <th>Decision</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                
                <div class="tab-pane fade" id="tab-passed" role="tabpanel">
                    <table id="passedTable" class="table table-striped table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Application</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Score</th>
                                <th>Decision</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                
                <div class="tab-pane fade" id="tab-under-review" role="tabpanel">
                    <table id="failedTable" class="table table-striped table-hover w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Application</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Score</th>
                                <th>Decision</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Use document ready instead of setTimeout for reliability
$(document).ready(function() {
    // Check if required libraries are loaded
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables library not loaded');
        alert('DataTables library failed to load. Please refresh the page.');
        return;
    }
    
    // Configuration
    const DT_URL = '{{ route('prequalification.prequalification-evaluation.datatable') }}';
    const BULK_URL = '{{ route('prequalification.prequalification-evaluation.prequalify.bulk', ':roundId') }}';
    const TOKEN = '{{ csrf_token() }}';
    const EVAL_SHOW_URL = '{{ route('prequalification.prequalification-evaluation.show', ':appId') }}';
    const PREQUALIFY_SINGLE_URL = '{{ route('prequalification.prequalification-evaluation.prequalify.single', ['roundId' => ':roundId', 'thirdPartyId' => ':thirdPartyId', 'categoryId' => ':categoryId']) }}';
    const REJECT_SINGLE_URL = '{{ route('prequalification.prequalification-evaluation.reject.single', ['roundId' => ':roundId', 'thirdPartyId' => ':thirdPartyId', 'categoryId' => ':categoryId']) }}';
    
    // Common DataTable configuration
    const commonConfig = {
        pageLength: 25,
        processing: true,
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
            emptyTable: 'No data available',
            zeroRecords: 'No matching records found',
            loadingRecords: 'Loading...',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            infoFiltered: '(filtered from _MAX_ total entries)'
        },
        order: [[3, 'desc']]
    };
    
    // Column definitions (shared across all tables)
    const columns = [
        { data: 'application_no', name: 'application_no' },
        { data: 'supplier', name: 'supplier' },
        { data: 'status', name: 'status' },
        { 
            data: 'submitted_on', 
            name: 'submitted_on',
            render: function(data) {
                return data ? data : 'N/A';
            }
        },
        { 
            data: 'total_score', 
            name: 'total_score',
            render: function(data) {
                return data ? data + '%' : '';
            }
        },
        { 
            data: 'decision', 
            name: 'decision',
            render: function(data) {
                if (!data) return '';
                const badgeClass = (data === 'Passed') ? 'bg-success' : 'bg-danger';
                return `<span class="badge ${badgeClass}">${data}</span>`;
            }
        },
        { 
            data: null, 
            name: 'actions',
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
                const evalUrl = EVAL_SHOW_URL.replace(':appId', row.application_id);
                const prequalifyUrl = PREQUALIFY_SINGLE_URL
                    .replace(':roundId', row.round_id)
                    .replace(':thirdPartyId', row.supplier_id)
                    .replace(':categoryId', row.category_id);
                const rejectUrl = REJECT_SINGLE_URL
                    .replace(':roundId', row.round_id)
                    .replace(':thirdPartyId', row.supplier_id)
                    .replace(':categoryId', row.category_id);
                
                let html = '';
                
                // Evaluate button logic:
                // Only enable if evaluation is allowed (i.e. Supplier is Prequalified)
                // If decision exists, showing edit button (yellow) is fine, but maybe stick to same logic
                if (row.evaluation_allowed) {
                    html += `<a href="${evalUrl}" class="btn btn-sm btn-warning text-dark me-1" title="Evaluate">
                        <i class="fas fa-edit"></i>
                    </a>`;
                } else {
                    html += `<button class="btn btn-sm btn-warning text-dark me-1" title="Supplier must be prequalified first" disabled>
                        <i class="fas fa-edit"></i>
                    </button>`;
                }
                
                // Prequalify button logic (Reverse of Evaluate)
                if (row.prequalify_allowed) {
                    html += `<form method="POST" action="${prequalifyUrl}" class="d-inline preq-form">
                        <input type="hidden" name="_token" value="${TOKEN}">
                        <button type="submit" class="btn btn-sm btn-success" title="Prequalify Supplier">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>`;
                } else {
                    html += `<span class="btn btn-sm btn-secondary disabled" title="Already Prequalified">
                        <i class="fas fa-check-circle"></i>
                    </span>`;
                }

                // Reject option for failed applications in under-review queue.
                if (row.decision === 'Failed' && row.status !== 'Rejected') {
                    html += ` <form method="POST" action="${rejectUrl}" class="d-inline reject-form ms-1">
                        <input type="hidden" name="_token" value="${TOKEN}">
                        <button type="submit" class="btn btn-sm btn-danger" title="Reject Supplier for Category">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>`;
                }
                
                return html;
            }
        }
    ];
    
    // Initialize Pending Table
    const pendingDt = $('#pendingTable').DataTable({
        ...commonConfig,
        ajax: {
            url: DT_URL,
            type: 'GET',
            data: function(d) {
                d.status = 'pending';
                d.round_id = $('#round_id').val();
            },
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                console.error('Pending Table Error:', {xhr, error, thrown});
                alert('Failed to load pending applications. Please try again.');
            }
        },
        columns: columns
    });
    
    // Initialize Passed Table
    const passedDt = $('#passedTable').DataTable({
        ...commonConfig,
        ajax: {
            url: DT_URL,
            type: 'GET',
            data: function(d) {
                d.status = 'passed';
                d.round_id = $('#round_id').val();
            },
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                console.error('Passed Table Error:', {xhr, error, thrown});
                alert('Failed to load passed applications. Please try again.');
            }
        },
        columns: columns
    });
    
    // Initialize Failed Table
    const failedDt = $('#failedTable').DataTable({
        ...commonConfig,
        ajax: {
            url: DT_URL,
            type: 'GET',
            data: function(d) {
                d.status = 'failed';
                d.round_id = $('#round_id').val();
            },
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                console.error('Failed Table Error:', {xhr, error, thrown});
                alert('Failed to load failed applications. Please try again.');
            }
        },
        columns: columns
    });
    
    // Reload tables when round selection changes
    $('#round_id').on('change', function() {
        pendingDt.ajax.reload();
        passedDt.ajax.reload();
        failedDt.ajax.reload();
    });
    
    // Bulk Prequalify Form Handler
    $('#bulkPrequalifyForm').on('submit', function(e) {
        e.preventDefault();
        
        const roundId = $('#round_id').val();
        if (!roundId) {
            alert('Please select a round first');
            return;
        }
        
        if (!confirm('Are you sure you want to bulk prequalify all passed suppliers for this round?')) {
            return;
        }
        
        const btn = $(this).find('button[type="submit"]');
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: BULK_URL.replace(':roundId', roundId),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': TOKEN },
            success: function(response) {
                alert(response.message || 'Suppliers prequalified successfully');
                // Reload all tables
                pendingDt.ajax.reload();
                passedDt.ajax.reload();
                failedDt.ajax.reload();
            },
            error: function(xhr) {
                console.error('Bulk prequalify error:', xhr);
                const message = xhr.responseJSON?.message || 'Failed to prequalify suppliers';
                alert(message);
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
    // Individual Prequalify Form Handler (delegated event)
    $(document).on('submit', '.preq-form', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to prequalify this supplier for this category?')) {
            return;
        }
        
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const originalHtml = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                alert(response.message || 'Supplier prequalified successfully');
                // Reload all tables
                pendingDt.ajax.reload();
                passedDt.ajax.reload();
                failedDt.ajax.reload();
            },
            error: function(xhr) {
                console.error('Single prequalify error:', xhr);
                const message = xhr.responseJSON?.message || 'Failed to prequalify supplier';
                alert(message);
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Individual Reject Form Handler (delegated event)
    $(document).on('submit', '.reject-form', function(e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to reject this supplier for this category?')) {
            return;
        }

        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const originalHtml = btn.html();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                alert(response.message || 'Supplier rejected successfully');
                pendingDt.ajax.reload();
                passedDt.ajax.reload();
                failedDt.ajax.reload();
            },
            error: function(xhr) {
                console.error('Single reject error:', xhr);
                const message = xhr.responseJSON?.message || 'Failed to reject supplier';
                alert(message);
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
});
</script>
@endsection
