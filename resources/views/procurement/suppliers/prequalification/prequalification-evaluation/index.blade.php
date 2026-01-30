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
                    <i class="fas fa-check-double me-1"></i> Bulk Prequalify (Passed)
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

@php
try {
    $singlePrequalifyTemplate = route('prequalification.prequalification-evaluation.prequalify.single', [
        'roundId' => '__RID__',
        'thirdPartyId' => '__TPID__',
        'categoryId' => '__CID__',
    ]);
} catch (\Throwable $e) {
    $singlePrequalifyTemplate = '#ROUTE_ERROR';
}
@endphp

@section('scripts')
<script>
setTimeout(function() {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
        console.error('Required libraries not loaded');
        return;
    }
    
    var $ = jQuery;
    var SINGLE_URL = @json($singlePrequalifyTemplate);
    var DT_URL = '{{ route('prequalification.prequalification-evaluation.datatable') }}';
    var BULK_URL = '{{ route('prequalification.prequalification-evaluation.prequalify.bulk', '__RID__') }}';
    var TOKEN = '{{ csrf_token() }}';
    
    var pendingDt = $('#pendingTable').DataTable({
        ajax: {
            url: DT_URL,
            type: 'GET',
            data: function(d) {
                d.status = 'pending';
                d.round_id = $('#round_id').val();
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'application_no', name: 'application_no' },
            { data: 'supplier', name: 'supplier' },
            { data: 'status', name: 'status' },
            { 
                data: 'submitted_on', 
                name: 'submitted_on',
                render: function(data, type, row, meta) {
                    return data ? data : 'N/A';
                }
            },
            { 
                data: 'total_score', 
                name: 'total_score',
                render: function(data, type, row, meta) {
                    return data ? data + '%' : '';
                }
            },
            { 
                data: 'decision', 
                name: 'decision',
                render: function(data, type, row, meta) {
                    if (!data) return '';
                    var cls = (data === 'Passed') ? 'bg-success' : 'bg-danger';
                    return '<span class="badge ' + cls + '">' + data + '</span>';
                }
            },
            { 
                data: null, 
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    var evalUrl = '{{ route('prequalification.prequalification-evaluation.show', '__ID__') }}'.replace('__ID__', row.application_id);
                    var singleUrl = SINGLE_URL.replace('__RID__', row.round_id).replace('__TPID__', row.supplier_id).replace('__CID__', row.category_id);
                    var html = '';
                    if (!row.decision) {
                        html += '<a href="' + evalUrl + '" class="btn btn-sm btn-warning text-dark me-1"><i class="fas fa-edit"></i></a>';
                    }
                    if (row.prequalify_allowed) {
                        html += '<form method="POST" action="' + singleUrl + '" class="d-inline preq-form">@csrf<button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button></form>';
                    } else {
                        html += '<span class="btn btn-sm btn-secondary disabled"><i class="fas fa-check-circle"></i></span>';
                    }
                    return html;
                }
            }
        ],
        pageLength: 25,
        order: [[3, 'desc']],
        processing: true
    });
    
    var passedDt = $('#passedTable').DataTable({
        ajax: {
            url: DT_URL,
            type: 'GET',
            data: function(d) {
                d.status = 'passed';
                d.round_id = $('#round_id').val();
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'application_no' },
            { data: 'supplier' },
            { data: 'status' },
            { data: 'submitted_on', render: function(d) { return d || 'N/A'; } },
            { data: 'total_score', render: function(d) { return d ? d + '%' : ''; } },
            { data: 'decision', render: function(d) { return d ? '<span class="badge ' + (d === 'Passed' ? 'bg-success' : 'bg-danger') + '">' + d + '</span>' : ''; } },
            { 
                data: null, 
                orderable: false,
                render: function(d, t, row) {
                    var evalUrl = '{{ route('prequalification.prequalification-evaluation.show', '__ID__') }}'.replace('__ID__', row.application_id);
                    var singleUrl = SINGLE_URL.replace('__RID__', row.round_id).replace('__TPID__', row.supplier_id).replace('__CID__', row.category_id);
                    var h = '';
                    if (!row.decision) h += '<a href="' + evalUrl + '" class="btn btn-sm btn-warning text-dark me-1"><i class="fas fa-edit"></i></a>';
                    if (row.prequalify_allowed) {
                        h += '<form method="POST" action="' + singleUrl + '" class="d-inline preq-form">@csrf<button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button></form>';
                    } else {
                        h += '<span class="btn btn-sm btn-secondary disabled"><i class="fas fa-check-circle"></i></span>';
                    }
                    return h;
                }
            }
        ],
        pageLength: 25,
        order: [[3, 'desc']],
        processing: true
    });
    
    var failedDt = $('#failedTable').DataTable({
        ajax: {
            url: DT_URL,
            type: 'GET',
            data: function(d) {
                d.status = 'failed';
                d.round_id = $('#round_id').val();
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'application_no' },
            { data: 'supplier' },
            { data: 'status' },
            { data: 'submitted_on', render: function(d) { return d || 'N/A'; } },
            { data: 'total_score', render: function(d) { return d ? d + '%' : ''; } },
            { data: 'decision', render: function(d) { return d ? '<span class="badge ' + (d === 'Passed' ? 'bg-success' : 'bg-danger') + '">' + d + '</span>' : ''; } },
            { 
                data: null, 
                orderable: false,
                render: function(d, t, row) {
                    var evalUrl = '{{ route('prequalification.prequalification-evaluation.show', '__ID__') }}'.replace('__ID__', row.application_id);
                    var singleUrl = SINGLE_URL.replace('__RID__', row.round_id).replace('__TPID__', row.supplier_id).replace('__CID__', row.category_id);
                    var h = '';
                    if (!row.decision) h += '<a href="' + evalUrl + '" class="btn btn-sm btn-warning text-dark me-1"><i class="fas fa-edit"></i></a>';
                    if (row.prequalify_allowed) {
                        h += '<form method="POST" action="' + singleUrl + '" class="d-inline preq-form">@csrf<button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button></form>';
                    } else {
                        h += '<span class="btn btn-sm btn-secondary disabled"><i class="fas fa-check-circle"></i></span>';
                    }
                    return h;
                }
            }
        ],
        pageLength: 25,
        order: [[3, 'desc']],
        processing: true
    });
    
    // Reload tables when round selection changes
    $('#round_id').on('change', function() {
        pendingDt.ajax.reload();
        passedDt.ajax.reload();
        failedDt.ajax.reload();
    });
    
    $('#bulkPrequalifyForm').on('submit', function(e) {
        e.preventDefault();
        var rid = $('#round_id').val();
        if (!rid) { alert('Select round'); return; }
        var btn = $(this).find('button[type="submit"]');
        var orig = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        $.ajax({
            url: BULK_URL.replace('__RID__', rid),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': TOKEN },
            success: function(r) {
                alert(r.message || 'Success');
                pendingDt.ajax.reload();
                passedDt.ajax.reload();
                failedDt.ajax.reload();
            },
            error: function() { alert('Failed'); },
            complete: function() { btn.prop('disabled', false).html(orig); }
        });
    });
    
    $(document).on('submit', '.preq-form', function(e) {
        e.preventDefault();
        if (!confirm('Prequalify?')) return;
        var f = $(this);
        var b = f.find('button');
        var o = b.html();
        b.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.ajax({
            url: f.attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': TOKEN },
            success: function(r) {
                alert(r.message || 'Success');
                pendingDt.ajax.reload();
                passedDt.ajax.reload();
                failedDt.ajax.reload();
            },
            error: function() { alert('Failed'); b.prop('disabled', false).html(o); }
        });
    });
    
}, 1500);
</script>
@endsection