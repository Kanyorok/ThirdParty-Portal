@extends('layouts.app')
@section('title', 'Budgets Overview')
@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some errors with your submission:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('budgetperiod.create') }}" class="btn btn-success btn-sm">
                + New Budget
            </a>
        </div>

        <div class="card p-3">
            <h5>📋 Budget List</h5>
            <p class="text-muted">
                Create and manage budgets linked to defined budget periods for effective financial planning and
                tracking.
            </p>

            @if ($budgets->count())
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Budget Name</th>
                    <th>Fiscal Year</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Status</th>
                    <th>View GL</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($budgets as $budget)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $budget->Name ?? '-' }}</td>
                        <td>{{ $budget->FiscalYear ?? '-' }}</td>
                        <td>{{ $budget->From ?? '-' }}</td>
                        <td>{{ $budget->To ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $budget->badgeClass }}">
                                {{ $budget->status }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('budgetperiod.show', $budget->Id) }}">
                                <span class="badge rounded-pill bg-info text-white">View GL</span>
                            </a>
                        </td>
                        <td>{{ $budget->Notes ?? '-' }}</td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                   data-bs-target="#editBudgetModal{{ $budget->Id }}">✏️</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
                </div>
        @else
            <div class="alert alert-info">
                No Saved Periods
            </div>
        @endif
    </div>
    </div>

    {{-- Edit Modals --}}
    @foreach ($budgets as $item)
        <div class="modal fade" id="editBudgetModal{{ $item->Id }}" tabindex="-1"
             aria-labelledby="editLabel{{ $item->Id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content rounded-3 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editLabel{{ $item->Id }}">📈 Edit Budget Setup</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form method="post" action="{{ route('budgetperiod.update', $item->Id) }}">
                            @csrf
                            @method('PATCH')
                            <div class="card p-4 border-0">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="Name{{ $item->Id }}" class="form-label">Budget Name</label>
                                        <input type="text" class="form-control" id="Name{{ $item->Id }}" name="Name"
                                               value="{{ $item->Name }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="FiscalYear{{ $item->Id }}" class="form-label">Fiscal Year</label>
                                        <input type="number" class="form-control" id="FiscalYear{{ $item->Id }}"
                                               min="2020" name="FiscalYear" value="{{ $item->FiscalYear }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="From{{ $item->Id }}" class="form-label">From (Date)</label>
                                        <input type="date" class="form-control" id="From{{ $item->Id }}" name="From"
                                               value="{{ $item->From }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="To{{ $item->Id }}" class="form-label">To (Date)</label>
                                        <input type="date" class="form-control" id="To{{ $item->Id }}" name="To"
                                               value="{{ $item->To }}" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="Notes{{ $item->Id }}" class="form-label">Notes</label>
                                    <textarea class="form-control" id="Notes{{ $item->Id }}" name="Notes" rows="3"
                                              required>{{ $item->Notes }}</textarea>
                                </div>

                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary"
                                            onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
                                        💾 Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    @endforeach

@endsection
