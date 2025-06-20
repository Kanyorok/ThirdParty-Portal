@extends('layouts.app')
@section('title', 'Budget Entry Listing')
@section('content')
    <div class="card mt-4">
        <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('entrybyglline.create') }}" class="btn btn-success">➕ Add Entry</a>

        </div>
        <div class="card-header bg-dark text-white">📑 Budget Entries by Line</div>
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

            <!-- Budget Table -->
            <div style="overflow-x: auto;">
                <table class="table table-bordered table-striped text-center" style="min-width: 800px;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Budget</th>
                            <th>Branch</th>
                            <th>Budget Line</th>
                            <th>Amount</th>
                            <th>Source</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Budget</td>
                            <td>Main Branch</td>
                            <td>Salaries – Staff Costs</td>
                            <td>500,000</td>
                            <td>Manual Entry</td>
                            <td>
                                <a href="{{ route('entrybyglline.show', 1) }}" class="btn btn-sm btn-info">View Allocations</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
</div>
@endsection
