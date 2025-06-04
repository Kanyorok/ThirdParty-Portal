@extends('layouts.app')
@section('title', 'Committee Appointment Response')
@section('content')

@if ($tender)
        <div class="container mt-4">
    <h4 class="mb-3">🧾 Committee Appointment Response</h4>
    <form action="{{route('memberresponse.store')}}" method="POST">
        @csrf
        @method('POST')
        <input type="hidden" name="tender_id" value="{{$tender->tender->Id}}">
        <div class="mb-3">
            <label class="form-label">Tender:</label>
            <input type="text" class="form-control" value="{{$tender->tender->TenderNo}} - {{$tender->tender->Title}}" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Appointee:</label>
            <input type="text" class="form-control" value="{{$tender->createdBy->Name}}" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Do you accept this appointment?</label><br>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="response" id="accept" value="1">
                <label class="form-check-label" for="accept">Accept</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="response" id="decline" value="2">
                <label class="form-check-label" for="decline">Decline</label>
            </div>
        </div>

        <div class="mb-3">
            <label for="comments" class="form-label">Comments (optional)</label>
            <textarea class="form-control" name="comments" id="comments" rows="2" placeholder="Enter reason if declining..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Response</button>
    </form>
</div>
@else
<h1>No Response to Act</h1>
@endif
@endsection