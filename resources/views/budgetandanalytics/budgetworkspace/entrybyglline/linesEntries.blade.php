@extends('layouts.app')
@section('title', 'Budget Entry Listing')
@section('content')
    <div class="card mt-4">
      
        <div class="card-header bg-dark text-white">📑 Budget Entries by Line (Manual Entry)</div>
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
                <p class="text-muted">This page lists all budget entries by budget line. You can view, edit, or delete entries as needed.</p>
            </div>

            <!-- Budget Table -->
              <div class="mb-2 d-flex justify-content-between">
                    <a href="{{ route('entrybyglline.create') }}" class="btn btn-success">➕ Add Entry</a>
              </div>
            <div style="overflow-x: auto;">
                @if($entries->count())
                <table class="table table-bordered table-striped text-center" style="min-width: 800px;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Budget</th>
                            <th>Branch</th>
                            <th>Budget Line</th>
                            <th>Amount</th>
                            {{-- <th>Source</th> --}}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $item)
                        <tr>
                            <td>{{ $loop->iteration}}</td>
                            <td>{{$item->budget->Name}}</td>
                            <td>{{$item->branch->Name}}</td>
                            <td>{{$item->budgetLine->LineName}}</td>
                            <td>{{ $item->Amount}}</td>
                            {{-- <td>Manual Entry</td> --}}
                            <td>
                                <a href="{{ route('entrybyglline.show', $item->Id) }}" class="btn btn-sm btn-info">View Allocations</a>
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
@endsection
