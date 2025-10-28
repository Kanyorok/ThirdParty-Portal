@extends('layouts.app')
@section('title', 'Committee Appointment Response')
@section('content')

{{-- SweetAlert CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if ($tenders && $tenders->count())
    <div class="container mt-4">
        <h4 class="mb-3">🧾 Tender Committee Appointment Response</h4>
        <form action="{{ route('memberresponse.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="tender_id" class="form-label">Select Tender</label>
                <select class="form-select" name="tender_id" id="tender_id" required>
                    <option value="" disabled selected>-- Select Tender --</option>
                    @foreach ($tenders as $entry)
                        @if($entry->tender)
                            <option value="{{ $entry->tender->Id }}">
                                {{ $entry->tender->TenderNo ?? 'No Tender No' }}
                                - {{ $entry->tender->Title ?? 'No Title' }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Do you accept this appointment?</label><br>
                <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="tender_response" id="accept_tender" value="1" required>
                    <label class="form-check-label" for="accept_tender">Accept</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="tender_response" id="decline_tender" value="2">
                    <label class="form-check-label" for="decline_tender">Decline</label>
                </div>
            </div>

            <div class="mb-3">
                <label for="comments_tender" class="form-label">Comments (optional)</label>
                <textarea class="form-control" name="tender_comments" id="comments_tender" rows="2"
                          placeholder="Enter reason if declining..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Tender Response</button>
        </form>
    </div>
@endif

@if ($rfq && $rfq->count())
    <div class="container mt-4">
        <h4 class="mb-3">📄 RFQ Committee Appointment Response</h4>
        <form action="{{ route('memberresponse.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="rfq_id" class="form-label">Select RFQ</label>
                <select class="form-select" name="rfq_id" id="rfq_id" required>
                    <option value="" disabled selected>-- Select RFQ --</option>
                    @foreach ($rfq as $entry)
                        @if($entry->rfq)
                            <option value="{{ $entry->rfq->Id }}">
                                {{ $entry->rfq->RFQNumber ?? 'No RFQ Number' }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Do you accept this appointment?</label><br>
                <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="rfq_response" id="accept_rfq" value="1" required>
                    <label class="form-check-label" for="accept_rfq">Accept</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="rfq_response" id="decline_rfq" value="2">
                    <label class="form-check-label" for="decline_rfq">Decline</label>
                </div>
            </div>

            <div class="mb-3">
                <label for="comments_rfq" class="form-label">Comments (optional)</label>
                <textarea class="form-control" name="rfq_comments" id="comments_rfq" rows="2"
                          placeholder="Enter reason if declining..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit RFQ Response</button>
        </form>
    </div>
@endif

{{-- Show popup if no appointments exist --}}
@if ((!$tenders || $tenders->count() === 0) && (!$rfq || $rfq->count() === 0))
    <div class="alert alert-info mt-5 text-center">
        You currently have no tender or RFQ appointments to respond to.
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'info',
                title: 'No Appointments Found',
                text: 'You currently have no tender or RFQ appointments to respond to.',
                confirmButtonText: 'OK'
            });
        });
    </script>
@endif

@endsection
