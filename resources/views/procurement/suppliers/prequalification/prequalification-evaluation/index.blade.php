@extends('layouts.app')

@section('title', 'Prequalification Evaluations')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-primary">My Prequalification Evaluations</h4>
            <form method="POST" id="bulkPrequalifyForm" class="d-flex align-items-center gap-2">
                @csrf
                <input type="number" min="1" class="form-control form-control-sm" placeholder="Round ID" name="round_id" style="width:120px" required>
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check-double me-1"></i> Bulk Prequalify (Passed)</button>
            </form>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills mb-3" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-passed" role="tab">Passed</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-under-review" role="tab">Under Review (Failed)</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-passed" role="tabpanel">
                    <table id="passedTable" class="table table-striped w-100">
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
                    <table id="failedTable" class="table table-striped w-100">
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
let passedDt = null, failedDt = null;

// Precompute base URLs safely; if a named route is missing, fall back to '#'
const SINGLE_PREQUALIFY_TEMPLATE = @json(\Illuminate\Support\Facades\Route::has('prequalification.prequalification-evaluation.prequalify.single')
    ? route('prequalification.prequalification-evaluation.prequalify.single', ['roundId'=>'__RID__','thirdPartyId'=>'__TPID__'])
    : '#');

function buildColumns() {
    return [
        {data:'application_no'},
        {data:'supplier'},
        {data:'status'},
        {data:'submitted_on'},
        {data:'total_score', render:(d)=> d? d+"%":""},
        {data:'decision', render:(d)=> d? `<span class="badge ${d==='Passed'?'bg-success':'bg-danger'}">${d}</span>`:''},
        {data:null, orderable:false, render:(row)=> actionButtons(row)}
    ];
}

function actionButtons(row) {
    const viewUrl = `{{ route('prequalification.prequalification-evaluation.results', '__ID__') }}`.replace('__ID__', row.application_id);
    const evalUrl = `{{ route('prequalification.prequalification-evaluation.show', '__ID__') }}`.replace('__ID__', row.application_id);
    const singleUrl = SINGLE_PREQUALIFY_TEMPLATE.replace('__RID__', row.round_id).replace('__TPID__', row.supplier_id);
    let buttons = '';
    if (row.decision) {
        buttons += `<a href="${viewUrl}" class="btn btn-sm btn-info text-white me-1"><i class='fas fa-eye'></i></a>`;
    } else {
        buttons += `<a href="${evalUrl}" class="btn btn-sm btn-warning text-dark me-1"><i class='fas fa-edit'></i></a>`;
    }
    if (row.prequalify_allowed) {
    const prequalifyTitle = (row.decision === 'Passed') ? 'Prequalify' : 'Force Prequalify';
    buttons += `<form method='POST' action='${singleUrl}' class='d-inline prequalify-single-form'>@csrf<button type='submit' class='btn btn-sm btn-success' title='${prequalifyTitle}'><i class="fas fa-check"></i></button></form>`;
    }
    return buttons;
}

function initDt() {
    passedDt = $('#passedTable').DataTable({
        ajax: {
            url: '{{ route('prequalification.prequalification-evaluation.datatable') }}',
            data: {status:'passed'}
        },
        responsive:true,
        columns: buildColumns()
    });
    failedDt = $('#failedTable').DataTable({
        ajax: {
            url: '{{ route('prequalification.prequalification-evaluation.datatable') }}',
            data: {status:'failed'}
        },
        responsive:true,
        columns: buildColumns()
    });
}

document.addEventListener('DOMContentLoaded', ()=> {
    initDt();
    document.getElementById('bulkPrequalifyForm').addEventListener('submit', function(e){
        e.preventDefault();
        const roundId = this.round_id.value;
        if(!roundId) return;
        const url = `{{ route('prequalification.prequalification-evaluation.prequalify.bulk', '__RID__') }}`.replace('__RID__', roundId);
        fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}})
            .then(r=>r.json().catch(()=>({})))
            .finally(()=> {passedDt.ajax.reload(); failedDt.ajax.reload();});
    });
});
</script>
@endsection