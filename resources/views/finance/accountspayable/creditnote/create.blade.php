@extends('layouts.app')
@section('title', 'Credit Note')
@section('content')

<div class="container mt-2">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="card shadow rounded-4">
        <div class="card-header bg-light py-1 px-3">
            <h6 class="mb-0 text-muted"><i class="fab fa-wpforms text-info"></i></h6>
        </div>
        <div class="card-body">
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            <form action="{{route('creditnote.store')}}" method="POST">
                @csrf
                @method('POST')
                <div class="row mb-4">
                    {{-- <div class="col-md-6">--}}
                    {{-- <label for="notetype" class="form-label">Note Type</label>--}}
                    {{-- <select class="form-control" name="NoteType" required>--}}
                    {{-- <option disabled selected value="">--Select Note Type--</option>--}}
                    {{-- <option value="Credit">Credit Note</option>--}}
                    {{-- <option value="Debit">Debit Note</option>--}}
                    {{-- </select>--}}
                    {{-- </div>--}}
                    <input type="hidden" name="NoteType" value="Credit">
                    <div class="col-md-6">
                        <label for="referenceInvoice" class="form-label">Reference Invoice</label>
                        <select class="form-control" name="InvoiceRefNo" required>
                            <option disabled selected value="">--Select Invoice--</option>
                            @forelse($invoices as $invoice)
                            <option value="{{ $invoice->Id }}">{{ $invoice->InvoiceNumber }}</option>
                            @empty
                            <option disabled>No invoices found</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="noteDate" class="form-label">Note Date</label>
                        <input type="date" class="form-control" id="noteDate" name="NoteDate" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" min="0.00" class="form-control" id="amount" name="NoteAmount"
                            placeholder="e.g. 10,000" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason / Description</label>
                    <textarea class="form-control" name="Description" id="reason" rows="3"
                        placeholder="Reason for issuing the note..." required></textarea>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{route('creditnote.index')}}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success" id="postBtn"
                        onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='💾Saving...'; this.form.submit();}">
                        💾Save Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection