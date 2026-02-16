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

        @php
            $milestoneStructureLocked = in_array((string) $contract->ContractStatus, ['Approved', 'Ap', 'Executed', 'Terminated'], true);
            $contractValue = round(max(0, (float) ($contract->ContractValue ?? 0)), 2);
            $allocatedMilestoneValue = round((float) $milestones->sum(function ($milestone) use ($contractValue) {
                if (strtoupper((string) $milestone->ValueType) === 'PERCENT') {
                    return round(max(0, $contractValue * ((float) ($milestone->ValuePercent ?? 0) / 100)), 2);
                }

                return round(max(0, (float) ($milestone->ValueAmount ?? 0)), 2);
            }), 2);
            $remainingMilestoneValue = round($contractValue - $allocatedMilestoneValue, 2);
            $allocationBalanced = abs($remainingMilestoneValue) <= 0.01;
        @endphp

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
                <div class="alert {{ $allocationBalanced ? 'alert-success' : ($remainingMilestoneValue > 0 ? 'alert-warning' : 'alert-danger') }}">
                    <strong>Milestone Allocation:</strong>
                    {{ number_format($allocatedMilestoneValue, 2) }} / {{ number_format($contractValue, 2) }}
                    @if($allocationBalanced)
                        (Fully allocated)
                    @elseif($remainingMilestoneValue > 0)
                        (Remaining: {{ number_format($remainingMilestoneValue, 2) }})
                    @else
                        (Over by: {{ number_format(abs($remainingMilestoneValue), 2) }})
                    @endif
                </div>

                @if($milestoneStructureLocked)
                    <div class="alert alert-warning mb-0">
                        Milestone structure is locked because this contract is already approved/executed. You can still tick checklist fulfillment and update milestone status.
                    </div>
                @else
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
                            <select name="ValueType" class="form-select" id="milestoneValueType">
                                <option value="FIXED" @selected(old('ValueType', 'FIXED') === 'FIXED')>Fixed</option>
                                <option value="PERCENT" @selected(old('ValueType') === 'PERCENT')>Percent</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fixed Amount</label>
                            <input type="number" step="0.01" name="ValueAmount" class="form-control" id="milestoneValueAmount" value="{{ old('ValueAmount') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Percent Value (%)</label>
                            <input type="number" step="0.0001" min="0" max="100" name="ValuePercent" class="form-control" id="milestoneValuePercent" value="{{ old('ValuePercent') }}">
                        </div>
                        <div class="col-md-12">
                            <div class="small text-muted" id="milestoneValuePreview">
                                For Percent milestones, amount is computed as: Contract Value x Percent / 100.
                            </div>
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
                @endif
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
                            $milestoneAmount = strtoupper((string) $milestone->ValueType) === 'PERCENT'
                                ? round(max(0, $contractValue * ((float) ($milestone->ValuePercent ?? 0) / 100)), 2)
                                : round(max(0, (float) ($milestone->ValueAmount ?? 0)), 2);
                            $statusClass = match($milestone->Status) {
                                'Accepted' => 'success',
                                'Waived' => 'secondary',
                                'Rejected' => 'danger',
                                'Submitted' => 'info',
                                'In Progress' => 'warning',
                                default => 'light text-dark'
                            };
                            $currentStatus = $milestone->Status ?? 'Draft';
                            $canSubmit = in_array($currentStatus, ['Draft', 'In Progress', 'Rejected'], true);
                            $canAccept = $currentStatus === 'Submitted';
                            $canReject = $currentStatus === 'Submitted';
                            $canWaive = !in_array($currentStatus, ['Accepted', 'Waived'], true);
                            $nextStepHint = match ($currentStatus) {
                                'Submitted' => 'Next step: Accept or Reject this submitted milestone.',
                                'Accepted' => 'Milestone accepted. No further action required.',
                                'Waived' => 'Milestone waived. No further action required.',
                                default => 'First step: Submit the milestone for review, then Accept/Reject.',
                            };
                        @endphp
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">M{{ $milestone->MilestoneNo }} - {{ $milestone->Title }}</h6>
                                    <div class="small text-muted">
                                        Due: {{ $milestone->PlannedDueDate ? $milestone->PlannedDueDate->format('d-M-Y') : 'N/A' }} |
                                        Required Checklist: {{ $requiredDone }}/{{ $requiredTotal }} |
                                        Value: {{ $contract->tender->Currency->Code ?? 'KES' }} {{ number_format($milestoneAmount, 2) }}
                                        @if(strtoupper((string) $milestone->ValueType) === 'PERCENT')
                                            ({{ number_format((float) ($milestone->ValuePercent ?? 0), 4) }}%)
                                        @endif
                                    </div>
                                </div>
                                <span class="badge bg-{{ $statusClass }}">{{ $milestone->Status }}</span>
                            </div>

                            @if($milestone->Description)
                                <p class="small mb-2">{{ $milestone->Description }}</p>
                            @endif

                            <div class="small text-muted mb-2">{{ $nextStepHint }}</div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <form action="{{ route('contracts.lifecycle.milestones.status', [$contract->Id, $milestone->Id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="submit">
                                    <button class="btn btn-outline-info btn-sm" @disabled(!$canSubmit) title="{{ $canSubmit ? 'Submit milestone for review' : 'Already submitted or finalized' }}">Submit</button>
                                </form>
                                <form action="{{ route('contracts.lifecycle.milestones.status', [$contract->Id, $milestone->Id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="accept">
                                    <button class="btn btn-outline-success btn-sm" @disabled(!$canAccept) title="{{ $canAccept ? 'Accept submitted milestone' : 'Accept is available only after Submit' }}">Accept</button>
                                </form>
                                <form action="{{ route('contracts.lifecycle.milestones.status', [$contract->Id, $milestone->Id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button class="btn btn-outline-danger btn-sm" @disabled(!$canReject) title="{{ $canReject ? 'Reject submitted milestone' : 'Reject is available only after Submit' }}">Reject</button>
                                </form>
                                <form action="{{ route('contracts.lifecycle.milestones.status', [$contract->Id, $milestone->Id]) }}" method="POST" class="d-flex gap-2">
                                    @csrf
                                    <input type="hidden" name="action" value="waive">
                                    <input type="text" name="waive_reason" class="form-control form-control-sm" placeholder="Waive reason" @if($canWaive) required @endif @disabled(!$canWaive)>
                                    <button class="btn btn-outline-secondary btn-sm" @disabled(!$canWaive) title="{{ $canWaive ? 'Waive this milestone with a reason' : 'Accepted or waived milestones cannot be waived again' }}">Waive</button>
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

                            @if($milestoneStructureLocked)
                                <div class="small text-muted">Checklist structure is locked after approval.</div>
                            @else
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
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const valueType = document.getElementById('milestoneValueType');
        const valueAmount = document.getElementById('milestoneValueAmount');
        const valuePercent = document.getElementById('milestoneValuePercent');
        const preview = document.getElementById('milestoneValuePreview');

        if (!valueType || !valueAmount || !valuePercent || !preview) {
            return;
        }

        const contractValue = Number(@json($contractValue));
        const currencyCode = @json($contract->tender->Currency->Code ?? 'KES');

        function formatMoney(amount) {
            const parsed = Number.isFinite(amount) ? amount : 0;
            return `${currencyCode} ${parsed.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        function updatePreview() {
            const type = (valueType.value || 'FIXED').toUpperCase();
            if (type === 'PERCENT') {
                const percent = Number.parseFloat(valuePercent.value || '0') || 0;
                const amount = (contractValue * percent) / 100;
                preview.textContent = `Computed Milestone Amount: ${formatMoney(amount)} (${percent.toFixed(4)}% of ${formatMoney(contractValue)}).`;
                return;
            }

            const amount = Number.parseFloat(valueAmount.value || '0') || 0;
            preview.textContent = `Milestone Amount: ${formatMoney(amount)}.`;
        }

        function syncFieldState() {
            const type = (valueType.value || 'FIXED').toUpperCase();
            const isPercent = type === 'PERCENT';

            valuePercent.required = isPercent;
            valueAmount.required = !isPercent;
            valuePercent.disabled = !isPercent;
            valueAmount.disabled = isPercent;

            updatePreview();
        }

        valueType.addEventListener('change', syncFieldState);
        valueAmount.addEventListener('input', updatePreview);
        valuePercent.addEventListener('input', updatePreview);
        syncFieldState();
    });
</script>
@endpush
