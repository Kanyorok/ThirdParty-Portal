@extends('layouts.app')
@section('title', 'Tender Invitation Response')
@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Tender Invitation Response</h4>
    <form action="{{ route('tenderresponse.storeResponse') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="TenderID" value="1"> <!-- Replace with dynamic ID -->
        <input type="hidden" name="SupplierID" value="1"> <!-- Replace with dynamic ID -->

        <div class="mb-3">
            <label class="form-label fw-bold">Tender:</label>
            <input type="text" class="form-control" value="TND/PROC/2025/001 - Supply of Laptops" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Supplier:</label>
            <input type="text" class="form-control" value="Tech Supplies Ltd" readonly>
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
