@extends('layouts.app')
@section('title', 'Activities Overview')
@section('content')
    <div class="container mt-4">
        <div class="card p-4">
            <div class="card-header bg-dark text-white py-4 mb-0" style="font-size: 20px; font-weight: bold;">
                📊 Budget Activities Overview
            </div>

            <div class="card-body mb-0">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <p class="text-muted">
                    Below is a list of budget activities that have been added and linked to their respective budget
                    lines. Each activity
                    represents a planned action or initiative under the budget, including its description, cost, and the
                    period it is
                    intended to be implemented. This view helps track how funds are allocated across various budget
                    items.

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
                        {{-- <th>Branch</th> --}}
                        {{-- <th>Allocation Type</th> --}}
                        <th>Total Allocation</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $i = 1; @endphp
                    @foreach ($groupedActivities as $budgetId => $activities)
                        @php $budget = $activities->first()->budget; @endphp
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $budget->Name }}</td>
                            <td>{{ $budget->From }} - {{ $budget->To }}</td>
                            {{-- <td>{{ $activities->first()->budgetLine->LineName ?? '' }}</td> --}}
                            {{-- <td>
                              <div class="d-flex flex-column align-items-center">
                                @foreach (
                                  $activities as $activity)
                                  <span class="badge bg-primary mb-1">{{ $activity->Description }}</span>
                                @endforeach
                              </div>
                            </td> --}}
                            <td>
                                <a href="{{ route('budgetactivities.show', $budget->Id) }}"
                                   class="btn btn-outline-primary btn-sm">View Activities</a>
                            </td>
                            <td>
                                {{ $activities->sum('FullAllocation') }}
                            </td>
                            <td>
                                {{-- <form action="{{route('budgetactivities.destroy', $budgetId)}}" method="POST" class="d-inline" >
                                  @csrf
                                  @method('DELETE')
                                  <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this activity?')">🗑️</button>
                                </form> --}}
                                <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{ $budget->Name }}" {{-- Pass item name --}}
                                        data-route="{{route('budgetactivities.destroy', $budgetId)}}"> {{--Pass delete route--}}
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>
        </div>


    </div>
    </div>


    @foreach ($activities as $item)
        <!-- View Allocations Modal -->
        <div class="modal fade" id="viewAllocationsModal-{{ $item->Id }}" tabindex="-1"
             aria-labelledby="viewAllocationsLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content rounded-3 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewAllocationsLabel">Monthly Allocations
                            for {{ $item->ActivityName }}</h5>
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
                                    <td>{{ DateTime::createFromFormat('!m', $alloc->Month)->format('F') }}</td>
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
        </div>
    @endforeach

    @include('components.modals.delete-confirm')
@endsection
