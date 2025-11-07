@extends('layouts.app')
@section('title', 'Appoint Tender Committee')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📋 Appoint Tender Committee</h4>
    <form>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="tenderRef" class="form-label">Tender Reference</label>
                <select class="form-select" id="tenderRef">
                    <option selected disabled>-- Select Tender --</option>
                    <option>TND/PROC/2025/001</option>
                    <option>TND/PROC/2025/002</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="appointmentDate" class="form-label">Appointment Date</label>
                <input type="date" class="form-control" id="appointmentDate" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="committeeMembers" class="form-label">Select Committee Members</label>
            <select class="form-select" id="committeeMembers" multiple>
                <!-- Populate from system user list -->
                <option>Moses K. – Procurement Manager</option>
                <option>Grace A. – Finance Officer</option>
                <option>John O. – Technical Expert</option>
                <option>Linda M. – Legal Counsel</option>
            </select>
            <small class="form-text text-muted">Hold CTRL/CMD to select multiple users.</small>
        </div>

        <button type="submit" class="btn btn-primary">Appoint Committee</button>
    </form>
</div>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('appointmentDate');
            if (el) {
                // Compute today's date in local timezone to avoid server/browser TZ mismatches
                const pad = (n) => String(n).padStart(2, '0');
                const now = new Date();
                const todayStr = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}`;
                // Enforce min on the DOM element (fallback if datepicker fails)
                el.min = todayStr;
                if (!el.value || el.value < todayStr) {
                    el.value = todayStr;
                }

                // Match Raise Needs date picker behavior
                flatpickr(el, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd/m/Y',
                    allowInput: true,
                    minDate: 'today', // disables past dates and greys them out in the calendar
                    defaultDate: new Date(),
                    disableMobile: true
                });

                // Guard against manual edits: prevent setting a past date
                el.addEventListener('change', () => {
                    if (el.value && el.value < el.min) {
                        el.setCustomValidity('Date cannot be earlier than today.');
                        el.reportValidity();
                        el.value = el.min;
                        // Clear message after correction
                        el.setCustomValidity('');
                    }
                });
            }
        });
    </script>
@endpush

@endsection
