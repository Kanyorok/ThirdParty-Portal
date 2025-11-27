@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Tenant Clearance')

@section('content')
    <div class="container mt-5" style="max-width: 800px;">

        <form>
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Tenant && Lease</label>
                        <input type="text" class="form-control"
                               value="Name: {{ $clearancetenant->lease->tenant->thirdParty->TradingName  ?? '-' }} &nbsp;&nbsp; LeaseNo: {{ $clearancetenant->lease->LeaseNumber ?? '-'}}"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Exit Date</label>
                        <input type="text" class="form-control"
                               value="{{ $clearancetenant->ExitDate ? Carbon::parse($clearancetenant->ExitDate)->format('d M Y') : '-' }}"
                               readonly>
                    </div>

                    {{-- Three fields on one row --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Final Inspection Done</label>
                            <input type="text" class="form-control"
                                   value="{{ (int)$clearancetenant->FinalInspection === 1 ? 'Yes' : ((int)$clearancetenant->FinalInspection === 0 ? 'No' : '-') }}"
                                   readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dues Cleared</label>
                            <input type="text" class="form-control"
                                   value="{{ (int)$clearancetenant->AllDuesPaid === 1 ? 'Yes' : ((int)$clearancetenant->AllDuesPaid === 0 ? 'No' : '-') }}"
                                   readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Keys Returned</label>
                            <input type="text" class="form-control"
                                   value="{{ (int)$clearancetenant->KeysReturned === 1 ? 'Yes' : ((int)$clearancetenant->KeysReturned === 0 ? 'No' : '-') }}"
                                   readonly>
                        </div>
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
                    <a href="{{ route('tenantclearance.edit', $clearancetenant->Id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <a href="{{ route('tenantclearance.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
@endsection
