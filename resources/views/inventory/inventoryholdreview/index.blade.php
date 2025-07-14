@php use App\Enums\Inventory\Transfers; @endphp
@extends('layouts.app')

@section('title', 'Inventory Review List')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        table.dataTable,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 0.875rem;
        }
    </style>
@endsection

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">Inventory Review List</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="mb-3 d-flex justify-content-between align-items-end flex-wrap">
            <a href="{{ route('inventoryholdreview.create') }}" class="btn btn-sm btn-success mb-2">➕ Review New
                Item</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="reviewTable" class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>InventoryHold ID</th>
                            <th>Item</th>
                            <th>UOM</th>
                            <th>From Branch</th>
                            <th>Quantity</th>
                            <th>Defect</th>
                            <th>Status</th>
                            <th>Condition</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($holds as $hold)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $hold->inventoryHold->InventoryHoldID ?? $hold->InventoryHoldID }}</td>
                                <td>{{ $hold->item->ItemName ?? '-' }}</td>
                                <td>{{ $hold->item->uom->Code ?? '-' }}</td>
                                <td>{{ $hold->fromBranch->Name ?? '-' }}</td>
                                <td>{{ number_format($hold->Quantity, 2) }}</td>
                                <td>{{ $hold->defectDetail->Description ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $statusEnum = Transfers::tryFrom($hold->Status);
                                    @endphp
                                    @if($statusEnum)
                                        <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                            {{ $statusEnum->label() }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning">{{ $hold->Status ?? 'Unknown' }}</span>
                                    @endif
                                </td>
                                <td>{{ $hold->Condition ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('inventoryholdreview.show', $hold->Id) }}"
                                       class="btn btn-warning btn-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No inventory reviews found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    @section('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                @if(!$holds->isEmpty())
                $('#reviewTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    lengthChange: true,
                    language: {
                        emptyTable: ""
                    }
                });
            @endif
        </script>
    @endsection

@endsection
