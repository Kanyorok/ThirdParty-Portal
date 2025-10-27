@extends('layouts.app')
@section('title', 'Budget Entry Listing')

@section('content')
    <div class="container mt-0">
        <div class="card shadow-sm rounded-3">
            <!-- Header -->

            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                <h6 class="mb-0 text-primary">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Entries by Line
                </h6>
                <a href="{{ route('entrybyglline.create') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Entry
                </a>
            </div>


            <!-- Body -->
            <div class="card-body p-3">
                <p class="text-muted mb-3" style="font-size: 14px;">
                    Listing of manual budget entries grouped by budget line. You can view details, check allocations, or
                    delete entries.
                </p>

                <div class="table-responsive">
                    @if($groupedEntries->count())
                        <table
                            class="table table-bordered table-hover table-striped align-middle text-center entry-table"
                            style="min-width: 850px;">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Budget</th>
                                <th>Budget Line</th>
                                <th>Total Allocation</th>
                                {{--                                <th>Source</th>--}}
                                <th class="text-center">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($groupedEntries as $budgetId => $entries)
                                @php
                                    $item = $entries->first();
                                    $totalAllocation = $entries->flatMap(function($entry) {
                                        return $entry->allocations ?? collect();
                                    })->sum('Allocation');
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->budget->Name ?? $item->BudgetID }}</td>
                                    <td>
                                        <a href="{{ route('entrybyglline.glview', $budgetId) }}"
                                           class="btn btn-sm btn-outline-info" title="View Budget Lines">
                                            <i class="fas fa-stream"></i>
                                        </a>
                                    </td>
                                    <td>{{ number_format($totalAllocation, 2) }}</td>
                                    {{--                                    <td><span class="badge bg-secondary">Manual Entry</span></td>--}}
                                    <td class="text-center" style="white-space: nowrap;">
                                        <a href="{{ route('entrybyglline.show', $item->BudgetID) }}"
                                           class="btn btn-sm btn-outline-primary me-1" title="View Entry">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger custom-delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="{{ $item->budget->Name }}"
                                                data-route="{{ route('entrybyglline.destroy', $item->Id) }}"
                                                @if($item->budget->Status === 'approved') disabled @endif
                                        >
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center p-4 border rounded-3 bg-light">
                            <p class="mb-2 text-muted">
                                <i class="fas fa-info-circle me-2 text-info"></i>
                                No budget entries found.
                            </p>
                            <a href="{{ route('entrybyglline.create') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-plus-circle me-1"></i> Add Entry
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('components.modals.delete-confirm')
@endsection

@section('styles')
    <style>
        .entry-table {
            font-size: 13px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .entry-table th,
        .entry-table td {
            vertical-align: middle;
            text-align: center;
        }
    </style>
@endsection
