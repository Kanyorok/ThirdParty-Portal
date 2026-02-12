@extends('layouts.app')
@section('title', 'Contract Execution Monitor')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Contract Execution - {{ $contract->ContractRef ?? ('CONTRACT-' . $contract->Id) }}</h4>
            <a href="{{ route('contracts.lifecycle.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="mb-1">{{ $contract->tender->Title ?? 'Contract' }}</h5>
                        <div class="text-muted">
                            Vendor:
                            <strong>
                                {{ $contract->winningSupplier?->thirdParty?->TradingName
                                    ?? $contract->winningSupplier?->thirdParty?->ThirdPartyName
                                    ?? $contract->winningSupplier?->supplierMaster?->party?->TradingName
                                    ?? $contract->winningSupplier?->supplierMaster?->party?->ThirdPartyName
                                    ?? 'N/A' }}
                            </strong>
                        </div>
                        <div class="text-muted">
                            Period:
                            {{ $contract->ContractStartDate ? $contract->ContractStartDate->format('d-M-Y') : 'N/A' }}
                            to
                            {{ $contract->ContractEndDate ? $contract->ContractEndDate->format('d-M-Y') : 'N/A' }}
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="mb-2">
                            Contract Value:
                            <strong>
                                {{ $contract->tender->Currency->Code ?? 'KES' }}
                                {{ number_format((float) $contract->ContractValue, 2) }}
                            </strong>
                        </div>
                        @if($contract->ContractStatus === 'Approved')
                            <form action="{{ route('contracts.lifecycle.execution.submit', $contract->Id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-check-circle me-1"></i> Activate Contract
                                </button>
                            </form>
                        @else
                            <span class="badge bg-success">Active / Executed</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Penalty Rule (Contract Level)</div>
            <div class="card-body">
                @if($penaltyRule)
                    <div class="row">
                        <div class="col-md-3"><strong>Type:</strong> {{ $penaltyRule->PenaltyType }}</div>
                        <div class="col-md-2"><strong>Rate:</strong> {{ $penaltyRule->Rate ?? 'N/A' }}</div>
                        <div class="col-md-2"><strong>Grace Days:</strong> {{ $penaltyRule->GraceDays }}</div>
                        <div class="col-md-3"><strong>Apply Method:</strong> {{ $penaltyRule->ApplyMethod }}</div>
                        <div class="col-md-2"><strong>Cap:</strong> {{ $penaltyRule->CapAmount ?? $penaltyRule->CapPercent ?? 'N/A' }}</div>
                    </div>
                @else
                    <span class="text-muted">No penalty rule configured on this contract.</span>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Add Milestone</div>
            <div class="card-body">
                <form action="{{ route('contracts.lifecycle.milestones.store', $contract->Id) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-2">
                        <label class="form-label">No</label>
                        <input type="number" name="MilestoneNo" class="form-control" min="1" value="{{ old('MilestoneNo', ($milestones->count() + 1)) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Title</label>
                        <input type="text" name="Title" class="form-control" value="{{ old('Title') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="PlannedDueDate" class="form-control" value="{{ old('PlannedDueDate') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Value Type</label>
                        <select name="ValueType" class="form-select">
                            <option value="FIXED">Fixed</option>
                            <option value="PERCENT">Percent</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fixed Amount</label>
                        <input type="number" step="0.01" name="ValueAmount" class="form-control" value="{{ old('ValueAmount') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Percent Value (%)</label>
                        <input type="number" step="0.0001" min="0" max="100" name="ValuePercent" class="form-control" value="{{ old('ValuePercent') }}">
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="2">{{ old('Description') }}</textarea>
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Add Milestone
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Contract Milestones and Checklists</div>
            <div class="card-body">
                @if($milestones->isEmpty())
                    <div class="text-muted">No milestones defined yet.</div>
                @else
                    @foreach($milestones as $milestone)
                        @php
                            $requiredTotal = $milestone->checklistItems->where('Required', true)->count();
                            $requiredDone = $milestone->checklistItems->where('Required', true)->where('IsFulfilled', true)->count();
                            $statusClass = match($milestone->Status) {
                                'Accepted' => 'success',
                                'Waived' => 'secondary',
                                'Rejected' => 'danger',
                                'Submitted' => 'info',
                                'In Progress' => 'warning',
                                default => 'light text-dark'
                            };
                        @endphp
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">M{{ $milestone->MilestoneNo }} - {{ $milestone->Title }}</h6>
                                    <div class="small text-muted">
                                        Due: {{ $milestone->PlannedDueDate ? $milestone->PlannedDueDate->format('d-M-Y') : 'N/A' }} |
                                        Required Checklist: {{ $requiredDone }}/{{ $requiredTotal }}
                                    </div>
                                </div>
                                <span class="badge bg-{{ $statusClass }}">{{ $milestone->Status }}</span>
                            </div>

                            @if($milestone->Description)
                                <p class="small mb-2">{{ $milestone->Description }}</p>
                            @endif

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <form action="{{ route('contracts.lifecycle.milestones.status', [$contract->Id, $milestone->Id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="submit">
                                    <button class="btn btn-outline-info btn-sm">Submit</button>
                                </form>
                                <form action="{{ route('contracts.lifecycle.milestones.status', [$contract->Id, $milestone->Id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="accept">
                                    <button class="btn btn-outline-success btn-sm">Accept</button>
                                </form>
                                <form action="{{ route('contracts.lifecycle.milestones.status', [$contract->Id, $milestone->Id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button class="btn btn-outline-danger btn-sm">Reject</button>
                                </form>
                                <form action="{{ route('contracts.lifecycle.milestones.status', [$contract->Id, $milestone->Id]) }}" method="POST" class="d-flex gap-2">
                                    @csrf
                                    <input type="hidden" name="action" value="waive">
                                    <input type="text" name="waive_reason" class="form-control form-control-sm" placeholder="Waive reason" required>
                                    <button class="btn btn-outline-secondary btn-sm">Waive</button>
                                </form>
                            </div>

                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th width="90">Required</th>
                                        <th width="130">Fulfilled</th>
                                        <th>Notes</th>
                                        <th width="110">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($milestone->checklistItems as $item)
                                        <tr>
                                            <td>{{ $item->ItemDescription }}</td>
                                            <td>{{ $item->Required ? 'Yes' : 'No' }}</td>
                                            <td>
                                                <span class="badge {{ $item->IsFulfilled ? 'bg-success' : 'bg-warning text-dark' }}">
                                                    {{ $item->IsFulfilled ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            <td>{{ $item->Notes ?? '—' }}</td>
                                            <td>
                                                <form action="{{ route('contracts.lifecycle.milestones.checklist.toggle', [$contract->Id, $milestone->Id, $item->Id]) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="IsFulfilled" value="{{ $item->IsFulfilled ? 0 : 1 }}">
                                                    <button class="btn btn-sm {{ $item->IsFulfilled ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                        {{ $item->IsFulfilled ? 'Undo' : 'Tick' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-muted">No checklist items yet.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <form action="{{ route('contracts.lifecycle.milestones.checklist.store', [$contract->Id, $milestone->Id]) }}" method="POST" class="row g-2">
                                @csrf
                                <div class="col-md-5">
                                    <input type="text" name="ItemDescription" class="form-control form-control-sm" placeholder="Checklist item description" required>
                                </div>
                                <div class="col-md-2">
                                    <select name="Required" class="form-select form-select-sm">
                                        <option value="1">Required</option>
                                        <option value="0">Optional</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="Notes" class="form-control form-control-sm" placeholder="Notes (optional)">
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection
