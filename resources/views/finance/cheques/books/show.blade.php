@extends('layouts.app')

@section('title', 'Cheque Book Details')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-1">Cheque Book #{{ $row->ChequeBookID }}</h4>
                    <p class="text-muted mb-0">
                        {{ $row->BookName ?? 'Unnamed Book' }}
                        <span class="mx-2">•</span>
                        {{ optional($row->bankAccount->bank)->BankName }} ({{ $row->bankAccount?->AccountNumber }})
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('finance.chequebooks.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <a href="{{ route('finance.chequebooks.edit', $row->ChequeBookID) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success shadow-sm border-0 border-start border-success border-4">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger shadow-sm border-0 border-start border-danger border-4">{{ session('error') }}</div>
            @endif

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="text-muted small text-uppercase mb-2">Range</h6>
                            <div class="fs-5 fw-bold text-dark">{{ $row->StartNumber }} <i class="fas fa-arrow-right text-muted mx-1 small"></i> {{ $row->EndNumber }}</div>
                            <small class="text-muted">Prefix: {{ $row->Prefix ?: '-' }} / Suffix: {{ $row->Suffix ?: '-' }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="text-muted small text-uppercase mb-2">Next Leaf</h6>
                            <div class="fs-5 fw-bold text-primary">{{ $row->NextLeafNumber }}</div>
                            <small class="text-muted">Ready to issue</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="text-muted small text-uppercase mb-2">Usage</h6>
                            <div class="d-flex align-items-center mb-1">
                                <div class="fs-5 fw-bold text-dark me-2">{{ $row->LeavesIssued }} / {{ $row->LeavesTotal }}</div>
                                <span class="badge bg-light text-dark border">{{ $row->LeavesTotal > 0 ? round(($row->LeavesIssued/$row->LeavesTotal)*100) : 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $row->LeavesTotal > 0 ? ($row->LeavesIssued/$row->LeavesTotal)*100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="text-muted small text-uppercase mb-2">Status</h6>
                            <div>
                                @if($row->IsActive)
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i> Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Leaves Table --}}
            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-muted">
                        <i class="fas fa-list text-info me-2"></i> Cheque Leaves
                    </h6>
                </div>
                
                {{-- Filters --}}
                <div class="card-body border-bottom pb-3 bg-white">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Status</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="fas fa-filter"></i></span>
                                <select class="form-select" name="status" onchange="this.form.submit()">
                                    @php $f = request('status'); @endphp
                                    <option value="">All Statuses</option>
                                    @foreach(['Unused','Reserved','Issued','Cleared','Bounced','Cancelled','Spoiled'] as $s)
                                        <option value="{{ $s }}" @selected($f===$s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Search</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input name="q" class="form-control" value="{{ request('q') }}" placeholder="Search Cheque No or Leaf #...">
                                <button class="btn btn-primary" type="submit">Search</button>
                            </div>
                        </div>
                        @if(request('status') || request('q'))
                            <div class="col-md-auto">
                                <a href="{{ route('finance.chequebooks.show', $row->ChequeBookID) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Clear
                                </a>
                            </div>
                        @endif
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th class="ps-3">Leaf #</th>
                            <th>Cheque No.</th>
                            <th>Status</th>
                            <th>Cheque ID</th>
                            <th>Used On</th>
                            <th>Cleared On</th>
                            <th>Cancelled On</th>
                            <th>Notes</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $leavesQuery = \App\Models\Finance\ChequeLeaf::where('ChequeBookID',$row->ChequeBookID)->orderBy('LeafNumber');
                            if(request('status')) $leavesQuery->where('Status', request('status'));
                            if(request('q')) {
                              $q = request('q');
                              $leavesQuery->where(function($qq) use ($q){
                                $qq->where('ChequeNumber','like',"%$q%")->orWhere('LeafNumber',$q);
                              });
                            }
                            $leaves = $leavesQuery->paginate(50);
                        @endphp

                        @forelse($leaves as $leaf)
                            <tr>
                                <td class="ps-3 fw-bold text-secondary">{{ $leaf->LeafNumber }}</td>
                                <td class="fw-bold text-primary" style="font-family: monospace; font-size: 1.1em;">{{ $leaf->ChequeNumber }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($leaf->Status) {
                                            'Unused' => 'bg-secondary bg-opacity-25 text-secondary',
                                            'Issued' => 'bg-info bg-opacity-25 text-info-emphasis',
                                            'Cleared' => 'bg-success bg-opacity-25 text-success-emphasis',
                                            'Bounced' => 'bg-danger bg-opacity-25 text-danger-emphasis',
                                            'Cancelled' => 'bg-dark text-white',
                                            'Spoiled' => 'bg-warning bg-opacity-25 text-warning-emphasis',
                                            default => 'bg-light text-dark border'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} border border-0">{{ $leaf->Status }}</span>
                                </td>
                                <td>
                                    @if($leaf->ChequeID)
                                        <a href="{{ route('finance.cheques.show', $leaf->ChequeID) }}" class="text-decoration-none">#{{ $leaf->ChequeID }}</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $leaf->UsedOn ? \Carbon\Carbon::parse($leaf->UsedOn)->format('d M Y') : '-' }}</td>
                                <td class="small text-muted">{{ $leaf->ClearedOn ? \Carbon\Carbon::parse($leaf->ClearedOn)->format('d M Y') : '-' }}</td>
                                <td class="small text-muted">{{ $leaf->CancelledOn ? \Carbon\Carbon::parse($leaf->CancelledOn)->format('d M Y') : '-' }}</td>
                                <td class="small text-muted text-truncate" style="max-width: 150px;" title="{{ $leaf->Notes }}">{{ $leaf->Notes ?? '-' }}</td>
                                <td class="text-end pe-3">
                                    @if($leaf->Status==='Unused')
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger border-0 custom-delete-btn" 
                                                title="Mark as Spoiled"
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="Leaf #{{ $leaf->LeafNumber }} ({{ $leaf->ChequeNumber }})"
                                                data-route="{{ route('finance.chequebooks.leaves.spoil',[$row->ChequeBookID,$leaf->LeafID]) }}"
                                                data-action-text="Spoil"
                                                data-action-class="btn-danger">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-file-invoice fa-2x mb-3 text-secondary opacity-25"></i>
                                    <p class="mb-0">No leaves found matching criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top-0">
                    {{ $leaves->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Reusing the delete-confirm modal component but adapting it via JS if needed, or using it as is --}}
    @include('components.modals.delete-confirm')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: If the delete-confirm modal needs text adjustment for "Spoil" action
            const deleteModal = document.getElementById('customDeleteConfirmModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const actionText = button.getAttribute('data-action-text');
                    const actionClass = button.getAttribute('data-action-class');
                    
                    if (actionText) {
                        const submitBtn = deleteModal.querySelector('form button[type="submit"]');
                        submitBtn.textContent = actionText;
                        // Adjust text to say "Spoil" instead of "Delete" if needed, 
                        // but the component might be hardcoded. 
                        // Assuming the component is flexible or we just use "Delete" logic for "Spoil" as a destructive action.
                        // If strict text change is needed, we'd modify the modal body/title here.
                        const title = deleteModal.querySelector('.modal-title');
                        if(title) title.textContent = 'Confirm Action';
                        const body = deleteModal.querySelector('.modal-body p');
                        if(body) body.textContent = `Are you sure you want to spoil ${button.getAttribute('data-name')}?`;
                    }
                });
            }
        });
    </script>
@endsection
