@extends('layouts.app')
@section('title', 'Activities Overview')
@section('content')
<div class="container mt-4">
  <div class="card p-4">
    <h5>📋 Budget Activities Overview</h5>
    <p class="text-muted">
      Below is a list of budget activities that have been added and linked to their respective budget lines. Each activity
       represents a planned action or initiative under the budget, including its description, cost, and the period it is 
       intended to be implemented. This view helps track how funds are allocated across various budget items.
    
    </p>
  
<div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('budgetactivities.create') }}" class="btn btn-success">➕ New Activity</a>    
  </div>
    <table class="table table-bordered table-hover table-striped align-middle text-center">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Budget</th>
          <th>Frequency</th>
          {{-- <th>Budget Line</th> --}}
          <th>Activities</th>
          <th>Branch</th>
          {{-- <th>Allocation Type</th> --}}
          <th>Total Allocation</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($activities as $item)            
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>Budget Name</td>
            <td>11-02-2025 - 11-11-2025</td>
            {{-- <td>{{ $item->budgetLine->LineName }}</td> --}}
            <td>3</td>
            {{-- <td>{{ $item->ActivityName }}</td> --}}
            <td>{{ $item->branch->Name }}</td>
            {{-- @if ($item->AllocationType=='monthly')
              <td>Monthly
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewAllocationsModal-{{ $item->Id }}">👁️ View</button>
              </td>
            @else
              <td>Full Allocation</td>
            @endif --}}
            <td>{{ $item->FullAllocation }}</td>
            <td>
              <div class="d-flex gap-2 justify-content-center">
                  <a href="{{ route('budgetperiod.edit', 1) }}" class="btn btn-sm btn-info">✏️</a>

                  <form action="{{ route('budgetperiod.destroy', 1) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Period?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                  </form>
              </div>
            </td>
          </tr>
        @endforeach
        <!-- Add more rows as needed -->
      </tbody>
    </table>
  </div>
</div>



@foreach ($activities as $item)
  <!-- View Allocations Modal -->
  <div class="modal fade" id="viewAllocationsModal-{{ $item->Id }}" tabindex="-1" aria-labelledby="viewAllocationsLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
          <div class="modal-content rounded-3 shadow">
              <div class="modal-header">
                  <h5 class="modal-title" id="viewAllocationsLabel">Monthly Allocations for {{ $item->ActivityName }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <table class="table table-striped">
                      <thead>
                          <tr>
                              <th>Month</th>
                              <th>Amount</th>
                          </tr>
                      </thead>
                      <tbody>
                        @foreach ($item->allocations as $alloc)
                          <tr>
                              <td>{{ \DateTime::createFromFormat('!m', $alloc->Month)->format('F') }}</td>
                              <td>{{ number_format($alloc->Amount) }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                          <tr>
                              <th>Total</th>
                              <th>{{ number_format($item->FullAllocation) }}</th>
                          </tr>
                      </tfoot>
                  </table>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
              </div>
          </div>
      </div>
  </div>
@endforeach

@include('components.modals.delete-confirm')
@endsection