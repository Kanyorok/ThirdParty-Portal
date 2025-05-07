@extends('layouts.app')
@section('title', 'Add Drivers')
@section('content')
<div class="container py-4">

    <!-- Driver List Header + Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Driver List</h4>
        <a href="{{ route('drivermanagement.create') }}" class="btn btn-primary">Add New Driver</a>
    </div>

    <!-- Driver Table (div-based) -->
    <div class="border rounded overflow-hidden">

        <!-- Header -->
        <div class="d-flex bg-dark text-white font-weight-bold p-2">
            <div class="flex-fill">DRIVER ID</div>
            <div class="flex-fill">NAME</div>
            <div class="flex-fill">LICENSE NUMBER</div>
            <div class="flex-fill">EXPIRY</div>
            <div class="flex-fill">STATUS</div>
            <div class="flex-fill">PHONE</div>
            <div class="flex-fill">ACTIONS</div>
        </div>

        <!-- Row 1 -->
        <div class="d-flex bg-light p-2 border-top">
            <div class="flex-fill">DRV001</div>
            <div class="flex-fill">James Njoroge</div>
            <div class="flex-fill">DLK123456</div>
            <div class="flex-fill">2026-12-31</div>
            <div class="flex-fill text-muted">Active</div>
            <div class="flex-fill">+254712345678</div>
            <div class="flex-fill d-flex gap-2">
                <button class="btn btn-sm btn-info text-white me-2">View</button>
                <button class="btn btn-sm btn-warning text-white">Edit</button>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="d-flex p-2 border-top">
            <div class="flex-fill">DRV002</div>
            <div class="flex-fill">Mary Wambui</div>
            <div class="flex-fill">DLK654321</div>
            <div class="flex-fill">2025-08-15</div>
            <div class="flex-fill text-muted">On Leave</div>
            <div class="flex-fill">+254700112233</div>
            <div class="flex-fill d-flex gap-2">
                <button class="btn btn-sm btn-info text-white me-2">View</button>
                <button class="btn btn-sm btn-warning text-white">Edit</button>
            </div>
        </div>

        <!-- Row 3 -->
        <div class="d-flex bg-light p-2 border-top">
            <div class="flex-fill">DRV003</div>
            <div class="flex-fill">Peter Otieno</div>
            <div class="flex-fill">DLK789456</div>
            <div class="flex-fill">2027-03-10</div>
            <div class="flex-fill text-muted">Active</div>
            <div class="flex-fill">+254799876543</div>
            <div class="flex-fill d-flex gap-2">
                <button class="btn btn-sm btn-info text-white me-2">View</button>
                <button class="btn btn-sm btn-warning text-white">Edit</button>
            </div>
        </div>
    </div>
</div>

    @endsection