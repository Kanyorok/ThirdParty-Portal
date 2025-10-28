@extends('layouts.app')
@section('title', 'Compliance Calendar View')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <h4 class="mb-4">📆 Interactive Compliance Calendar</h4>

        <div id="compliance-calendar"></div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('compliance-calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                height: 650,
                events: @json($calendarEntries),
                eventClick: function (info) {
                    const url = `/legal/compliance/calendar/${info.event.id}/edit`;
                    window.location.href = url;
                },
                eventDidMount: function (info) {
                    // Tooltip
                    const tooltip = new bootstrap.Tooltip(info.el, {
                        title: info.event.extendedProps.description,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                }
            });

            calendar.render();
        });
    </script>
@endpush
