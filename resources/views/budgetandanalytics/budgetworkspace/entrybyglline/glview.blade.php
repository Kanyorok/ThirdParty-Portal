@extends('layouts.app')
@section('title', 'Budget Lines, Branches & Amounts')
@section('content')
<div class="card mt-4">
    <div class="card-header bg-dark text-white">📑 Budget Lines, Branches & Amounts
        <a href="{{ route('entrybyglline.index') }}" class="btn btn-secondary btn-sm float-end">← Back to Entries</a>
    </div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <p class="text-muted">
                This page displays budget entries grouped by budget lines, branches, and amounts. You can  edit, or delete entries as needed.
            </p>

            @if($entries->count())
                @php    
                    $totalAllocation = $entries->flatMap(function($entry) { return $entry->allocations ?? collect(); })->sum('Allocation');
                @endphp
                <div class="mb-3">
                   
                    <strong>Budget:</strong> {{ $entries->first()->budget->Name ?? '' }}<br>
                    <strong>Total Allocations:</strong>{{ number_format($totalAllocation, 2) }}<br>
                    <strong>Source:</strong>Manual Entry<br>
                </div>
            <table class="table table-responsive table-hover table-sm align-middle
             table-bordered table-striped text-center" style="min-width: 700px;">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Branch</th>
                        <th>Budget Line</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->branch->Name }}</td>
                        <td>{{ $item->budgetline->LineName }}</td>
                        <td>{{ $item->Amount }}</td>
                        <td style="width: 200px; white-space: nowrap;">
                            <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#allocModal{{ $item->Id }}">View Allocations</a>
                            <a href="{{ route('entrybyglline.edit', $item->Id) }}" class="btn btn-sm btn-primary">🖉</a>
                            <form action="{{ route('entrybyglline.destroy', $item->Id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?')">🗑️</button>
                            </form>
                            <!-- Modal -->
                            <div class="modal fade" id="allocModal{{ $item->Id }}" tabindex="-1" aria-labelledby="allocModalLabel{{ $item->Id }}" aria-hidden="true">
                              <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="allocModalLabel{{ $item->Id }}">Monthly Allocations for {{ $item->budgetline->LineName }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    @if($item->allocations && $item->allocations->count())
                                      <table class="table table-bordered table-sm text-center">
                                        <thead>
                                          <tr>
                                            <th>Month</th>
                                            <th>Allocation</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                          @foreach($item->allocations as $alloc)
                                            <tr>
                                              <td>{{ $alloc->Month }}</td>
                                              <td>{{ $alloc->Allocation }}</td>
                                            </tr>
                                          @endforeach
                                        </tbody>
                                      </table>
                                    @else
                                      <div class="alert alert-info">No allocations found for this entry.</div>
                                    @endif
                                  <div>
                                    <p class="text-muted">
                                        Total Allocated Amount: <strong>{{ number_format($item->Amount, 2) }}</strong>
                                  </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="alert alert-info text-center">
                <strong>No budget lines found.</strong>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
