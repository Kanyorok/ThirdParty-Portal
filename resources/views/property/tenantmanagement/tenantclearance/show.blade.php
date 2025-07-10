@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Tenant Details')

@section('content')
    <div class="container mt-5" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Tenant Clearance Details</h3>
            <a href="#" class="btn btn-outline-secondary btn-sm">← Back to List</a>
        </div>

        <form>
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Tenant</label>
                        <input type="text" class="form-control"
                               value="{{ $clearancetenant->tenant->TenantName ?? '-' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Exit Date</label>
                        <input type="text" class="form-control"
                               value="{{ $clearancetenant->ExitDate ? Carbon::parse($clearancetenant->ExitDate)->format('d/m/Y') : '-' }}"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Final Inspection Done</label>
                        <input type="text" class="form-control"
                               value="{{ (int)$clearancetenant->FinalInspection === 1 ? 'Yes' : ((int)$clearancetenant->FinalInspection === 0 ? 'No' : '-') }}"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dues Cleared</label>
                        <input type="text" class="form-control"
                               value="{{ (int)$clearancetenant->AllDuesPaid === 1 ? 'Yes' : ((int)$clearancetenant->AllDuesPaid === 0 ? 'No' : '-') }}"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keys Returned</label>
                        <input type="text" class="form-control"
                               value="{{ (int)$clearancetenant->KeysReturned === 1 ? 'Yes' : ((int)$clearancetenant->KeysReturned === 0 ? 'No' : '-') }}"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" rows="3"
                                  readonly>{{ $clearancetenant->AdditionalNotes ?? '—' }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div>
                        <span class="badge bg-{{ $clearancetenant->Status->badgeColor() }}">
                            {{ $clearancetenant->Status->label() }}
                        </span>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ route('tenantclearance.edit', $clearancetenant->Id) }}" class="btn btn-outline-primary"><i
                            class="bi bi-pencil-square"></i> Edit</a>
                    <a href="{{ route('tenantclearance.index') }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
@endsection
