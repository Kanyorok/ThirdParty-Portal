@extends('layouts.app')
@section('title', 'Committee Appointment Response')
@section('content')

<div class="container mt-4">
    <h4 class="mb-3">🧾 Committee Appointment Response</h4>
    <form>
        <div class="mb-3">
            <label class="form-label">Tender:</label>
            <input type="text" class="form-control" value="TND/PROC/2025/001 - ICT Equipment" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Appointee:</label>
            <input type="text" class="form-control" value="Moses K. – Procurement Manager" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Do you accept this appointment?</label><br>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="response" id="accept" value="Accepted">
                <label class="form-check-label" for="accept">Accept</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="response" id="decline" value="Declined">
                <label class="form-check-label" for="decline">Decline</label>
            </div>
        </div>

        <div class="mb-3">
            <label for="comments" class="form-label">Comments (optional)</label>
            <textarea class="form-control" id="comments" rows="2" placeholder="Enter reason if declining..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Response</button>
    </form>
</div>

@endsection