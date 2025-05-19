@extends('layouts.app')
@section('title', 'Tender Invitation Response')
@section('content')
<div class="container mt-4">
    <form action="{{ route('tenderresponse.storeResponse') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!input type="hidden" name="TenderId" value="1"> <!-- Replace with dynamic ID -->
        <!input type="hidden" name="SupplierId" value="1"> <!-- Replace with dynamic ID -->

        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tenderSelect" class="form-label">Tender Reference</label>
                    <select class="form-select @error('TenderId') is-invalid @enderror" id="tenderSelect" name="TenderId" required>
                        <option selected disabled>-- Select Tender --</option>
                        @foreach ($tenders as $tender)
                            <option value="{{ $tender->Id }}">{{ $tender->TenderNo }}</option>
                        @endforeach
                    </select>
                    @error('TenderId')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="supplierSelect" class="form-label">Supplier Name</label>
                    <select class="form-select @error('SupplierId') is-invalid @enderror" id="supplierSelect" name="SupplierId" required>
                        <option selected disabled>-- Select Supplier --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->Id }}">{{ $supplier->SupplierName }}</option>
                        @endforeach
                    </select>
                    @error('supplier_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Your Response:</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="ResponseStatus" id="accept" value="Accepted" required>
                <label class="form-check-label" for="accept">Accept</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="ResponseStatus" id="decline" value="Declined">
                <label class="form-check-label" for="decline">Decline</label>
            </div>
        </div>

        <div class="mb-3">
            <label for="DeclineReason" class="form-label">Remarks (Optional)</label>
            <textarea class="form-control" name="DeclineReason" id="DeclineReason" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="ConfirmationAttachment" class="form-label">Upload Confirmation (Optional)</label>
            <input type="file" class="form-control" name="ConfirmationAttachment" id="ConfirmationAttachment">
        </div>

        <button type="submit" class="btn btn-primary">Submit Response</button>
    </form>
</div>
@endsection
