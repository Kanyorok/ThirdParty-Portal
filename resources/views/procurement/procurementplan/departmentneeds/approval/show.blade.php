@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'View Procurement Need')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <h3 class="mb-4 text-primary">Needs Details</h3>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Item Name</label>
                <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                    {{ $need->item->ItemName ?? 'N/A' }}
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Category</label>
                <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                    {{ $need->item->category->Name ?? 'N/A' }}
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Quantity Needed</label>
                <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                    {{ $need->RequestedQty }}
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Unit of Measure</label>
                <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                    {{ $need->item->uom->Name ?? 'N/A' }}
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Est. Unit Cost</label>
                <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                    {{ number_format($need->EstimatedUnitCost, 2, '.', ',') }}
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Required By</label>
                <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                    {{ Carbon::parse($need->RequestedDate)->format('d/m/Y') }}
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Submitted On</label>
                <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                    {{ Carbon::parse($need->CreatedOn)->format('d/m/Y') }}
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Justification</label>
                <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                    {{ $need->Justification ?? 'N/A' }}
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Submitted By</label>
                <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                    {{ $need->creator->Name ?? 'N/A' }}
                    {{-- <small class="text-muted">
                          ({{ $need->creator->Department ?? 'Unknown Department' }})
                    </small> --}}
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Attached Document</label>
                <div class="form-control-plaintext border rounded bg-light px-3 py-2">
                    @if($need->Attachment)
                        <a href="{{ asset('uploads/' . $need->Attachment) }}" target="_blank"
                           class="text-decoration-underline">
                            {{ $need->Attachment }}
                        </a>
                    @else
                        <span class="text-muted">No document attached.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="rejectForm" method="POST" action="{{ route('department-need-approval.destroy', $need->Id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Reason</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rejectReason">Reason for Rejection</label>
                            <textarea name="Department_needs_reject_reason" id="rejectReason" class="form-control"
                                      required minlength="15" maxlength="2000"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <form action="{{ route('department-need-approval.update', $need->Id) }}" method="POST" class="d-inline">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-success">Approve</button>
        </form>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject
        </button>
        <a href="{{ route('department-need-approval.index') }}" class="btn btn-secondary">
            Back to List
        </a>
    </div>
    </div>
@endsection
