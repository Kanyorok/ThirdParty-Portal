@extends('layouts.app')
@section('title', 'Budget Entry Listing')
@section('content')
    <div class="card mt-4">

        {{--        <div class="card-header bg-dark text-white">📑 Budget Entries by Line (Manual Entry)</div>--}}
        <div class="card-body">
            <!-- Filters -->
            <form class="row g-3 mb-3">
                {{-- <div class="col-md-4">
                    <label class="form-label">Budget Period</label>
                    <select class="form-select">
                        <option selected>FY2025-Q1</option>
                        <option>FY2025-Q2</option>
                    </select>
                </div> --}}
                {{-- <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Filter</button>
                </div> --}}
            </form>
            <div>
                <p class="text-muted">This page lists all budget entries by budget line. You can view, edit, or delete
                    entries as needed.</p>
            </div>

            <!-- Budget Table -->
            <div class="mb-2 d-flex justify-content-between">
                <a href="{{ route('entrybyglline.create') }}" class="btn btn-success">➕ Add Entry</a>
            </div>
            <div style="overflow-x: auto;">
                @if($groupedEntries->count())
                    <table class="table table-bordered table-striped text-center" style="min-width: 800px;">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Budget</th>
                            <th>Budget Line</th>
                            <th>Total Allocation</th>
                            <th>Source</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($groupedEntries as $budgetId => $entries)
                            @php
                                $item = $entries->first();
                                $totalAllocation = $entries->flatMap(function($entry) { return $entry->allocations ?? collect(); })->sum('Allocation');
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->budget->Name ?? $item->BudgetID }}</td>
                                <td>
                                    <a href="{{ route('entrybyglline.glview', $budgetId) }}"
                                       class="btn btn-sm btn-info">View Budget Lines</a>
                                </td>
                                <td>{{ number_format($totalAllocation, 2) }}</td>
                                <td>Manual Entry</td>
                                <td style="width: 200px; white-space: nowrap;">
                                    <a href="{{ route('entrybyglline.show', $item->BudgetID) }}"
                                       class="btn btn-sm btn-primary">👁️</a>
                                    {{-- <form action="{{ route('entrybyglline.destroy', $item->Id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?')">🗑️</button>
                                    </form> --}}
                                    <button type="button"
                                            class="btn btn-sm btn-danger custom-delete-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{ $item->budget->Name  }}" {{-- Pass item name --}}
                                            data-route="{{route('entrybyglline.destroy', $item->Id)}}"> {{--Pass delete route--}}
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info text-center">
                        <strong>No budget entries found.</strong> Please add a new entry to get started.
                    </div>
                @endif
            </div>

        </div>
</div>
    @include('components.modals.delete-confirm')

@endsection
