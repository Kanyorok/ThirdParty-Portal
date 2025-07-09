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
          <th>Activity Name</th>
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
            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $activity->activity->ActivityName }}">
              {{ $activity->activity->ActivityName }}
            </td>
            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $activity->Description}}">{{ $activity->Description }}</td>
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
              <a href="{{ route('budgetactivities.edit', $activity->Id) }}" class="btn btn-sm btn-primary">🖉</a>
              {{-- <form action="{{ route('budgetactivities.destroy', $activity->Id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this activity?')">🗑️</button>
              </form> --}}
              <button type="button"
                      class="btn btn-sm btn-danger custom-delete-btn"
                      data-bs-toggle="modal"
                      data-bs-target="#customDeleteConfirmModal"
                      data-name="{{ $activity->activity->ActivityName  }}"    {{-- Pass item name --}}
                      data-route="{{route('budgetactivities.destroy', $activity->Id)}}"> {{--Pass delete route--}}
                      🗑️
                </button>
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
@include('components.modals.delete-confirm')
@endsection
