@extends('layouts.app')
@section('title', 'Credit Note - Accounts Payable')
@section('content')

<div class="container mt-5"><div class="container mt-5">
    <h2 id="formTitle">Credit Note Entry</h2>

    <form>
        <div class="mb-3">
            <label class="form-label">Note Type:</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="note_type" id="creditNote" value="Credit" checked onchange="updateFormTitle()">
                <label class="form-check-label" for="creditNote">Credit Note</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="note_type" id="debitNote" value="Debit" onchange="updateFormTitle()">
                <label class="form-check-label" for="debitNote">Debit Note</label>
            </div>
        </div>

        <div class="mb-3">
            <label for="noteNumber" class="form-label">Note Number</label>
            <input type="text" class="form-control" id="noteNumber" placeholder="e.g. CN001 or DN001">
        </div>

        <div class="mb-3">
            <label for="noteDate" class="form-label">Note Date</label>
            <input type="date" class="form-control" id="noteDate" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="mb-3">
            <label for="vendorName" class="form-label">Vendor</label>
            <input type="text" class="form-control" id="vendorName" placeholder="e.g. XYZ Suppliers Ltd">
        </div>

        <div class="mb-3">
            <label for="referenceInvoice" class="form-label">Reference Invoice</label>
            <input type="text" class="form-control" id="referenceInvoice" placeholder="e.g. INV123">
        </div>

        <div class="mb-3">
            <label for="reason" class="form-label">Reason / Description</label>
            <textarea class="form-control" id="reason" rows="3" placeholder="Reason for issuing the note..."></textarea>
        </div>

        <div class="mb-3 row">
            <div class="col-md-6">
                <label for="amount" class="form-label">Amount (Ksh)</label>
                <input type="number" class="form-control" id="amount" placeholder="e.g. 10,000">
            </div>
            <div class="col-md-6">
                <label for="currency" class="form-label">Currency</label>
                <select class="form-select" id="currency">
                    <option selected>KES</option>
                    <option>USD</option>
                    <option>EUR</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-success mt-3">Submit Note</button>
    </form>
</div>
@endsection
