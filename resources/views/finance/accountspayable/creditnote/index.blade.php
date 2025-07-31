@extends('layouts.app')
@section('title', 'Credit/Debit Notes - Accounts Payable')
@section('content')
<div class="container mt-">

    <div class="card shadow rounded-4">
        <div class="card-header bg-light py-1 px-3">
            <h6 class="mb-0 text-muted"id="noteTypeTitle"><i class="fab fa-wpforms text-info"></i> Credit/Debit Notes</h6>
        </div>
        <div class="card-body">
            <p class="text-muted">Below is the list of all saved credit and debit notes with their details.</p>
            <a href="{{ route('creditnote.create') }}" class="btn btn-primary mb-3"> <i class="fas fa-plus"></i>  Add New Note</a>
            <div class="mb-3">  
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="filterNotes('All')">All</button>
                    <button type="button" class="btn btn-outline-success" onclick="filterNotes('Credit')">Credit Notes</button>
                    <button type="button" class="btn btn-outline-danger" onclick="filterNotes('Debit')">Debit Notes</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Note Type</th>
                            <th>Note Number</th>
                            <th>Date</th>
                            <th>Reference Invoice</th>
                            <th>Amount (Ksh)</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="noteTableBody">
                        @if($notes->count())
                        @foreach($notes as $note)
                            <tr class="note-row" data-note-type="{{ $note->NoteType }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $note->NoteType ?? '-' }}</td>
                                <td>{{ $note->CDNumber ?? '-'}}</td>
                                <td>{{ \Carbon\Carbon::parse($note->NoteDate)->format('d M Y') }}</td>
                                <td>{{ $note->invoice ? $note->invoice->InvoiceNumber : 'N/A' }}</td>
                                <td class="text-end">{{ number_format($note->NoteAmount, 2) ?? '-'}}</td>
                                <td>{{ $note->Description ?? '-'}}</td>
                                <td style="white-space: nowrap;">
                                    <button type="button" id="EditModalButton" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#EditNotesModal">Edit</button>
                                    <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        {{-- data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{$item->taxType->TaxTypeName}}"    {{-- Pass item name --}}
                                        {{-- data-route="{{ route('taxruleconfig.destroy', $item->Id) }}"> Pass delete route --}} >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach 
                        @else
                            <tr>
                                <td colspan="8" class="text-center">No credit/debit notes found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
                <div class="modal fade" id="EditNotesModal" tabindex="-1" aria-hidden="true" aria-labelledby="EditNotesModalLabel">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="EditNotesModalLabel">Edit Note</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="notetype" class="form-label">Note Type</label>
                                        <select class="form-control" name="NoteType" required>
                                        <option disabled selected value="">--Select Note Type--</option>
                                            <option value="Credit">Credit Note</option>
                                            <option value="Debit">Debit Note</option>
                                        </select>
                                    </div>           
                                    <div class="col-md-6">
                                        <label for="referenceInvoice" class="form-label">Reference Invoice</label>
                                        <select class="form-control" name="InvoiceRefNo" required>
                                            <option disabled selected value="">--Select Invoice--</option>
                                                @foreach($invoices as $invoice)
                                                    <option value="{{ $invoice->Id}}">{{$invoice->InvoiceNumber}}</option>
                                                @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <div class="col-md-6">
                                        <label for="noteDate" class="form-label">Note Date</label>
                                        <input type="date" class="form-control" id="noteDate" name="NoteDate" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="amount" class="form-label">Amount (Ksh)</label>
                                        <input type="number" min="0.00" class="form-control" id="amount" name="NoteAmount" placeholder="e.g. 10,000" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason / Description</label>
                                    <textarea class="form-control" name="Description" id="reason" rows="3" placeholder="Reason for issuing the note..."required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-success">Edit Note</button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<script>
    function filterNotes(type) {
        const rows = document.querySelectorAll('.note-row');
        const title = document.getElementById('noteTypeTitle');

        rows.forEach(row => {
            const noteType = row.getAttribute('data-note-type');
            const showRow = (type === 'All') || (noteType.toLowerCase() === type.toLowerCase());
            row.style.display = showRow ? '' : 'none';
        });

        // Update heading   
        // if (type === 'All') {
        //     title.textContent = 'All Notes';
        // } else if (type === 'Credit') {
        //     title.textContent = 'Credit Notes';
        // } else if (type === 'Debit') {
        //     title.textContent = 'Debit Notes';
        // }
    }
</script>

@endsection
