@extends('layouts.app')
@section('title', 'Budget Activities')
@section('content')
<div class="container mt-4">
  <div class="card p-4">
    <h5>Budget: {{ $budget->Name }}</h5>
    <p class="text-muted">Period: {{ $budget->From }} - {{ $budget->To }}</p>
    <a href="{{ route('budgetactivities.index') }}" class="btn btn-secondary mb-3">&larr; Back to List</a>
    <div class="table-responsive">
    <table class="table table-bordered table-hover table-striped align-middle text-center">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Description</th>
          <th>Budget Line</th>
          <th>Branch</th>
          <th>Allocation Type</th>
          <th>Full Allocation</th>
          <th>Monthly Allocations</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @php $total = 0; @endphp
        @foreach ($activities as $i => $activity)
          @php $total += $activity->FullAllocation; @endphp
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $activity->Description }}</td>
            <td>{{ $activity->budgetLine->LineName ?? '-' }}</td>
            <td>{{ $activity->branch->Name ?? '-' }}</td>
            <td>{{ ucfirst($activity->AllocationType) }}</td>
            <td>{{ number_format($activity->FullAllocation, 2) }}</td>
            <td>
              @if($activity->AllocationType === 'monthly' && $activity->allocations && count($activity->allocations))
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#monthlyAllocModal-{{ $activity->Id }}">View</button>
                <!-- Modal -->
                <div class="modal fade" id="monthlyAllocModal-{{ $activity->Id }}" tabindex="-1" aria-labelledby="monthlyAllocModalLabel-{{ $activity->Id }}" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="monthlyAllocModalLabel-{{ $activity->Id }}">Monthly Allocations</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <table class="table table-sm table-bordered">
                          <thead>
                            <tr>
                              <th>Month</th>
                              <th>Amount</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($activity->allocations as $alloc)
                              <tr>
                                <td>Month {{ $alloc->Month }}</td>
                                <td>{{ number_format($alloc->Amount, 2) }}</td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              @else
                -
              @endif
            </td>
            <td>
              <a href="{{ route('budgetactivities.edit', $activity->Id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
              <form action="{{ route('budgetactivities.destroy', $activity->Id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this activity?')">Delete</button>
              </form>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th colspan="5" class="text-end">Total Allocation</th>
          <th colspan="2">{{ number_format($total, 2) }}</th>
        </tr>
      </tfoot>
    </table>
    </div>
  </div>
</div>
@endsection
