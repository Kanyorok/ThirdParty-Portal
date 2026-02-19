@extends('layouts.app')

@section('title', 'Leave Calendar')

@section('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.css' rel='stylesheet' />
<style>
    .fc-event {
        cursor: pointer;
    }
    .daily-count-badge {
        font-size: 0.8em;
        padding: 2px 5px;
        border-radius: 4px;
        cursor: pointer;
        background-color: #0d6efd;
        color: white;
        margin-top: 2px;
        display: inline-block;
    }
    .daily-count-badge:hover {
        background-color: #0b5ed7;
    }
    #calendar {
        min-height: 700px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Leave Calendar</h2>
    </div>

    <div class="row">
        <!-- Calendar Section -->
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div id='calendar'></div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" id="statsTitle">Leave Statistics (Current View)</h5>
                    <button class="btn btn-sm btn-outline-secondary" id="resetStatsBtn" style="display: none;">Reset</button>
                </div>
                <div class="card-body">
                    <canvas id="leaveTypeChart"></canvas>
                </div>
            </div>
            
            <div class="card shadow-sm">
                 <div class="card-header bg-white">
                    <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
                     <p class="text-muted" id="statsDesc">Displays the distribution of leave types for the currently visible period.</p>
                     <ul id="statsList" class="list-group list-group-flush">
                         <!-- Populated via JS -->
                     </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Day Details Modal -->
<div class="modal fade" id="dayDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dayDetailsTitle">Employees on Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="dayDetailsList" class="list-group list-group-flush">
                    <!-- Populated via JS -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.js'></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var dailyCounts = {}; // Store fetched counts
        var currentViewStats = {}; // Store month view stats
        var statsChart = null;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek' 
            },
            themeSystem: 'bootstrap5',
            navLinks: true, // can click day/week names to navigate views
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true, // allow "more" link when too many events
            events: function(info, successCallback, failureCallback) {
                // Fetch data from backend
                fetch(`{{ route('hr.leave.calendar.data') }}?start=${info.startStr}&end=${info.endStr}`)
                    .then(response => response.json())
                    .then(data => {
                        dailyCounts = data.daily_counts; // Store for interaction
                        currentViewStats = data.stats;
                        resetStats(); // Show monthly stats by default on load
                        
                        // Force redraw of badges since data arrived
                        // We can't easily re-render just cells, but re-rendering events might help? 
                        // Actually, since we need to wait for data to paint badges, let's call repaintBadges()
                        // Note: dayCellDidMount fires BEFORE this fetch returns usually. 
                        // So we MUST manually inject badges after data load.
                        setTimeout(repaintBadges, 100); 

                        successCallback(data.events.map(e => ({ ...e, allDay: true })));
                    })
                    .catch(error => {
                        console.error('Error fetching events:', error);
                        failureCallback(error);
                    });
            },
            dateClick: function(info) {
                let dateStr = info.dateStr.split('T')[0];
                handleDateSelection(dateStr);
            },
            eventClick: function(info) {
                // Also handle event click as selecting that day
                // Use explicit startDate from backend to avoid timezone shifts
                let dateStr = info.event.extendedProps.startDate || info.event.startStr.split('T')[0];
                handleDateSelection(dateStr);
            },
            loading: function(isLoading) {
                if (!isLoading) {
                    repaintBadges();
                }
            }
        });

        calendar.render();

        document.getElementById('resetStatsBtn').onclick = resetStats;

        function handleDateSelection(dateStr) {
             let data = dailyCounts[dateStr];
             if (data) {
                 updateDayStats(dateStr, data.employees);
             } else {
                 // Date has no leaves
                 updateDayStats(dateStr, []);
             }
        }

        function repaintBadges() {
            // Remove old badges to prevent duplicates
            document.querySelectorAll('.daily-count-badge').forEach(e => e.remove());

            for (const [dateStr, data] of Object.entries(dailyCounts)) {
                // Selector for day cell (Month View)
                let dayCell = document.querySelector(`.fc-daygrid-day[data-date="${dateStr}"] .fc-daygrid-day-top`);
                
                // Selector for week view header (TimeGrid Week)
                let weekHeader = document.querySelector(`.fc-col-header-cell[data-date="${dateStr}"] .fc-col-header-cell-cushion`);

                let target = dayCell || weekHeader;

                if (target) {
                    // Check if badge already exists (safety)
                    if (target.parentNode.querySelector('.daily-count-badge')) continue;

                    let badge = document.createElement('div');
                    badge.className = 'daily-count-badge';
                    badge.innerText = data.count + ' On Leave';
                    badge.onclick = (e) => {
                        e.stopPropagation(); 
                        handleDateSelection(dateStr);
                        showDayDetails(dateStr, data.employees);
                    };
                    
                    if (weekHeader) {
                        // For week view, append to the header cell container (parent of cushion)
                        // This usually positions it nicely under the date number/name
                        target.parentNode.appendChild(badge);
                    } else {
                         // For month view, prepend to top (next to number)
                         target.prepend(badge); 
                    }
                }
            }
        }

        function showDayDetails(date, employees) {
            document.getElementById('dayDetailsTitle').innerText = 'Leaves on ' + date;
            let listHtml = '';
            if (employees.length === 0) {
                 listHtml = '<div class="list-group-item">No leaves for this day.</div>';
            } else {
                employees.forEach(emp => {
                    listHtml += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${emp.name}</strong><br>
                                <small class="text-muted">${emp.type}</small>
                            </div>
                        </div>
                    `;
                });
            }
            document.getElementById('dayDetailsList').innerHTML = listHtml;
            let modal = new bootstrap.Modal(document.getElementById('dayDetailsModal'));
            modal.show();
        }

        function updateDayStats(date, employees) {
            // Aggregate types for this day
            let stats = {};
            if (employees.length === 0) {
                 // Clear stats
            } else {
                employees.forEach(emp => {
                    if (!stats[emp.type]) stats[emp.type] = 0;
                    stats[emp.type]++;
                });
            }
            
            document.getElementById('statsTitle').innerText = 'Stats: ' + date;
            document.getElementById('statsDesc').innerText = employees.length > 0 
                ? 'Distribution for selected day.' 
                : 'No leaves on this day.';
            document.getElementById('resetStatsBtn').style.display = 'block';
            
            updateCharts(stats);
        }

        function resetStats() {
            document.getElementById('statsTitle').innerText = 'Leave Statistics (Current View)';
            document.getElementById('statsDesc').innerText = 'Displays the distribution of leave types for the currently visible period.';
            document.getElementById('resetStatsBtn').style.display = 'none';
            updateCharts(currentViewStats);
        }

        function updateCharts(stats) {
            const ctx = document.getElementById('leaveTypeChart').getContext('2d');
            const labels = Object.keys(stats);
            const values = Object.values(stats);
            const colors = ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'];

            if (statsChart) {
                statsChart.destroy();
            }

            // Handle empty stats
            if (labels.length === 0) {
                if (window.activeChart) window.activeChart.destroy();
                // Clear canvas with a message
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.font = "14px sans-serif";
                ctx.fillStyle = "#6c757d";
                ctx.textAlign = "center";
                ctx.fillText("No data available", ctx.canvas.width/2, ctx.canvas.height/2);
                
                document.getElementById('statsList').innerHTML = '<li class="list-group-item text-center text-muted">No leaves found for this period.</li>';
                return;
            }

            statsChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors.slice(0, labels.length)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
            window.activeChart = statsChart;
            
            // List Update
            let listHtml = '';
             labels.forEach((label, index) => {
                 listHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    ${label}
                    <span class="badge rounded-pill" style="background-color: ${colors[index] || '#ccc'}">${values[index]} Days</span>
                </li>`;
             });
             document.getElementById('statsList').innerHTML = listHtml;
        }
    });
</script>
@endsection
