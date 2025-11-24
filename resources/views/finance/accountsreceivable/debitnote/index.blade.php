@extends('layouts.app')
@section('title', 'Debit Notes')

@section('content')
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif


<div class="container mt-2">
    <div class="card shadow-sm rounded-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 px-3">
            <h6 class="mb-0 text-info" id="noteTypeTitle">
                <i class="fab fa-wpforms me-2"></i>Debit Notes - Accounts Receivable
            </h6>
            <a href="{{ route('debitnote.create') }}" class="btn btn-info btn-sm">
                <i class="fas fa-plus me-1"></i>Add New Note
            </a>
        </div>

        <div class="card-body pt-3">
            <form action="{{ route('debitnote.index') }}" method="GET" id="filter-form" class="row g-2 align-items-end mb-3">
                <div class="col-md-2">
                    <label class="form-label small text-muted">CD Number</label>
                    <input type="text" class="form-control form-control-sm" name="cd_number" value="{{ request('cd_number') }}" placeholder="Search CD#">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Invoice #</label>
                    <input type="text" class="form-control form-control-sm" name="invoice_number" value="{{ request('invoice_number') }}" placeholder="Invoice ref">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Date From</label>
                    <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Date To</label>
                    <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Min Amount</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="amount_min" value="{{ request('amount_min') }}" placeholder="0.00">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Status</label>
                    <select class="form-select form-select-sm" name="approval_status">
                        <option value="all" {{ request('approval_status')=='all' ? 'selected' : '' }}>All</option>
                        @isset($approvalStatuses)
                        @foreach($approvalStatuses as $status)
                        <option value="{{ $status }}" {{ request('approval_status')==$status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                        @endisset
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-sm btn-primary me-2"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('debitnote.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
            {{-- <div class="mb-3">--}}
            {{-- <div class="btn-group" role="group">--}}
            {{-- <button type="button" class="btn btn-outline-info btn-sm" onclick="filterNotes('All')">All</button>--}}
            {{-- <button type="button" class="btn btn-outline-success btn-sm" onclick="filterNotes('Credit')">Credit Notes</button>--}}
            {{-- <button type="button" class="btn btn-outline-danger btn-sm" onclick="filterNotes('Debit')">Debit Notes</button>--}}
            {{-- </div>--}}
            {{-- </div>--}}

            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0 text-center"
                    style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Note Type</th>
                            <th>Note Number</th>
                            <th>Date</th>
                            <th>Reference Invoice</th>
                            <th class="text-end">Amount (Ksh)</th>
                            <th scope="col">Approval</th>
                            {{--<th>Reason</th>--}}
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="noteTableBody">
                        @if($notes->count())
                        @foreach($notes as $note)
                        <tr class="note-row" data-note-type="{{ $note->NoteType }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ ucfirst($note->NoteType) ?? '-' }}</td>
                            <td>{{ strtolower($note->NoteType) === 'credit' ? 'CN-' : 'DN-' }}{{ $note->CDNumber ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($note->NoteDate)->format('d-m-Y') }}</td>
                            <td>{{ $note->invoiceDebit->InvoiceNumber ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($note->NoteAmount, 2) ?? '-' }}</td>
                            <td>
                                @php
                                $statusClass = match($note->ApprovalStatus) {
                                'posted' => 'bg-success',
                                'rejected' => 'bg-danger',
                                'draft' => 'bg-secondary',
                                default => 'bg-secondary'
                                };
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst($note->ApprovalStatus) ?? 'Pending' }}
                                </span>
                            </td>
                            {{--<td>{{ $note->Description ?? '-' }}</td>--}}
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('debitnote.show', $note->Id) }}"
                                        class="btn btn-sm btn-info me-1" title="View Note">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($note->ApprovalStatus === 'draft')
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#EditNotesModal-{{ $note->Id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @else
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary me-1 disabled"
                                        data-bs-toggle="modal"
                                        data-bs-target="#EditNotesModal-{{ $note->Id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif

                                    @if($note->ApprovalStatus === 'draft')
                                    <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{$note->CDNumber}}" {{-- Pass item name --}}
                                        data-route="{{ route('debitnote.destroy', $note->Id) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    @else
                                    <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn disabled"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{$note->CDNumber}}" {{-- Pass item name --}}
                                        data-route="{{ route('debitnote.destroy', $note->Id) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="8" class="p-0">
                                <div class="text-center p-3 border rounded-3 bg-light">
                                    <p class="mb-3 text-muted fs-5">
                                        <i class="fas fa-info-circle me-2 text-info"></i>
                                        <i> No Records found.</i>
                                    </p>
                                    <a href="{{ route('debitnote.create') }}" class="btn btn-info px-4 py-2">
                                        <i class="fas fa-plus-circle me-2"></i> New Note
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if(method_exists($notes,'links'))
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing {{ $notes->firstItem() ?? 0 }} to {{ $notes->lastItem() ?? 0 }} of {{ $notes->total() ?? $notes->count() }} entries
                </div>
                <div>
                    {{ $notes->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@foreach ($notes as $item)
<!-- Edit Notes Modal -->
<div class="modal fade" id="EditNotesModal-{{$item->Id}}" tabindex="-1" aria-labelledby="EditNotesModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-light text-info">
                <h5 class="modal-title text-info" id="EditNotesModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Credit/Debit Note
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('debitnote.update', $item->Id) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Note Type -->
                    <div class="row mb-3">
                        {{-- <div class="col-md-6 mb-3">--}}
                        {{-- <label class="form-label">Note Type</label>--}}
                        {{-- <select name="NoteType" class="form-select" value="{{old('NoteType', $item->NoteType)}}" required>--}}
                        {{-- <option value="credit">Credit</option>--}}
                        {{-- <option value="debit">Debit</option>--}}
                        {{-- </select>--}}
                        {{-- </div>--}}
                        <input type="hidden" name="NoteType" value="debit">

                        <!-- Reference Invoice -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Reference Invoice</label>
                            <select name="InvoiceRefNo" class="form-select" disabled required>
                                <option value="{{old('InvoiceRefNo', $item->InvoiceRefNo)}}" disabled selected>--Select Invoice--</option>
                                @foreach($invoices as $invoice)
                                <option value="{{ $invoice->Id }}" {{ $invoice->Id == $item->InvoiceRefNo ? 'selected' : '' }}>{{ $invoice->InvoiceTitle }} ({{ $invoice->InvoiceNumber }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Note Date -->
                        <div class="col-md-6">
                            <label class="form-label">Note Date</label>
                            <input type="date" name="NoteDate" class="form-control" value="{{old('NoteDate', \Carbon\Carbon::parse($item->NoteDate)->format('Y-m-d'))}}" required>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label">Amount (Ksh)</label>
                            <input type="number" step="0.01" name="NoteAmount" min="0.00" class="form-control"
                                value="{{old('NoteAmount', $item->NoteAmount)}}" required>
                        </div>

                        <!-- Description -->
                        <div class="mt-3">
                            <label class="form-label">Description</label>
                            <textarea name="Description" rows="3" class="form-control"
                                value="{{old('Description', $item->Description)}}">{{ $item->Description}}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel
                    </button>
                    <button type="button" class="btn btn-outline-info"
                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Editing...'; this.form.submit();}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    function filterNotes(type) {
        const rows = document.querySelectorAll('.note-row');
        const title = document.getElementById('noteTypeTitle');

        rows.forEach(row => {
            const noteType = row.getAttribute('data-note-type');
            const showRow = (type === 'All') || (noteType.toLowerCase() === type.toLowerCase());
            row.style.display = showRow ? '' : 'none';
        });
    }
</script>
@include('components.modals.delete-confirm')
@endsection