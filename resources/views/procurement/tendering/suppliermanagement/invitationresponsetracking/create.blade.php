@extends('layouts.app')
@section('title', 'Tender Invitation Response')
@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Tender Invitation Response</h4>
    <form>
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
                <input class="form-check-input" type="radio" name="response" id="accept" value="Accepted">
                <label class="form-check-label" for="accept">Accept</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="response" id="decline" value="Declined">
                <label class="form-check-label" for="decline">Decline</label>
            </div>
        </div>

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks (Optional)</label>
            <textarea class="form-control" id="remarks" rows="3" placeholder="You may state a reason for declining..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Response</button>
    </form>
</div>
@endsection