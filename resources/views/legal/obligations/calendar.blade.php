@extends('layouts.app')
@section('title', 'Obligation Calendar')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4>📆 Obligation Calendar View</h4>
    <div id="calendar"></div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: @json($calendarEvents)
    });
    calendar.render();
});
</script>
@endsection
