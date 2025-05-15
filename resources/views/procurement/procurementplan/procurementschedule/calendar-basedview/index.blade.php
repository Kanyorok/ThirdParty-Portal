@extends('layouts.app')
@section('title', 'Procurement Calendar View')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📆 Procurement Calendar View (Monthly)</h4>

    <!-- Filters & Month Selector -->
    <form class="row g-3 mb-4">
        <div class="col-md-3">
            <select class="form-select">
                <option selected>All Branches</option>
                <option>Nairobi HQ</option>
                <option>Mombasa Branch</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select">
                <option selected>All Departments</option>
                <option>ICT</option>
                <option>Finance</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select">
                <option selected>Planned Start</option>
                <option>Planned End</option>
                <option>Actual Delivery</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="month" class="form-control" value="2025-05" />
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100">Apply</button>
        </div>
    </form>

    <!-- Calendar Grid -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title text-center mb-4">🗓️ May 2025</h5>

            <!-- Weekday Headers -->
            <div class="row row-cols-7 text-center fw-bold text-secondary border-bottom pb-2 mb-2">
                <div class="col">Sun</div>
                <div class="col">Mon</div>
                <div class="col">Tue</div>
                <div class="col">Wed</div>
                <div class="col">Thu</div>
                <div class="col">Fri</div>
                <div class="col">Sat</div>
            </div>

            <!-- Full Month Grid (6 weeks) -->
            <div class="row row-cols-7 text-start border">
                <!-- Calendar cells for each day -->
                <!-- Example Day -->
                <div class="col border p-2" style="min-height: 120px;">
                    <strong class="d-block">1</strong>
                    <span class="badge bg-success d-block mt-1 small">RFQ – Toner</span>
                </div>
                <div class="col border p-2">
                    <strong class="d-block">2</strong>
                </div>
                <div class="col border p-2">
                    <strong class="d-block">3</strong>
                </div>
                <!-- ... Continue for entire month (up to 42 cells for 6 weeks) -->
                <div class="col border p-2">
                    <strong class="d-block">5</strong>
                    <span class="badge bg-warning d-block mt-1 small">Delivery – Laptops</span>
                </div>
                <div class="col border p-2">
                    <strong class="d-block">7</strong>
                    <span class="badge bg-danger d-block mt-1 small">Overdue – Desks</span>
                </div>
                <!-- Fill in remaining cells to complete the 6x7 grid -->
            </div>
        </div>
    </div>
</div>


@endsection