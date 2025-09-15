@extends('layouts.app')
@section('title', 'Budgets Overview')
@section('content')

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some errors with your submission:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container my-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 text-primary">📋 Budget List</h5>
            <a href="{{ route('budgetperiod.create') }}" class="btn btn-info btn-sm px-3 py-2">
                <i class="fas fa-plus me-1"></i> New Budget
            </a>
        </div>

        <div class="card shadow-sm rounded-3 p-3">
            <p class="text-muted mb-3">
                Create and manage budgets linked to defined budget periods for effective financial planning and tracking.
            </p>

            @if ($budgets->count())
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 13px;">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Budget Name</th>
                            <th>Year</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>View GL</th>
                            <th>Approval</th>
{{--                            <th>Notes</th>--}}
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
                                        {{ ucfirst($budget->status) }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('budgetperiod.show', $budget->Id) }}"
                                       class="badge rounded-pill bg-info text-white text-decoration-none">
                                        View GL
                                    </a>
                                </td>

                                <td>
                                    @php
                                        $statusClass = match(strtolower($budget->Status)) {
                                            'draft' => 'bg-secondary',
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            default => 'bg-secondary', // fallback color
                                        };
                                    @endphp

                                    <span class="badge rounded-pill {{ $statusClass }} text-white text-decoration-none">
                                        {{ ucfirst($budget->Status ) }}
                                    </span>

                                </td>

{{--                                <td>{{ $budget->Notes ?? '-' }}</td>--}}
                                <td>
                                    <div class="d-flex gap-2">
                                        <!-- Edit -->
                                        @php
                                            $isDraft = $budget->Status !== 'draft';
                                            $isApproved = $budget->Status === 'approved';
                                        @endphp

                                        @if(!$isApproved)
                                            <!-- Edit -->
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editBudgetModal{{ $budget->Id }}"
                                                    @if($isDraft) disabled @endif>
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <!-- Delete -->
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger custom-delete-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#customDeleteConfirmModal"
                                                    data-name="{{ $budget->Name }}"
                                                    data-route="{{ route('budget.delete-budget', $budget->Id) }}"
                                                    @if($isDraft) disabled @endif>
                                                <i class="fas fa-trash-alt"></i>
                                            </button>

                                            <!-- Approve/Reject Decision -->
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#decisionModal{{ $budget->Id }}"
                                                    title="Approve or Reject"
                                                    @if($isDraft) disabled @endif>
                                                <i class="fas fa-check-circle"></i>
                                            </button>

                                        @endif
                                        @if($isApproved and !$budget->IsLimitSet)
                                            <a href="{{ route('budgetandanalytics.limits.runUpdate') }}"
                                               class="btn btn-sm btn-outline-warning"
                                               data-bs-toggle="modal"
                                               data-bs-target="#updateLimit{{ $budget->Id }}"
                                               style="text-align: right"
                                               title="Set spending limit for this approved budget">
                                                <i class="fas fa-sliders-h"></i>
                                            </a>
                                        @endif

                                        @if($isApproved and $budget->IsLimitSet)
                                            <span class="badge rounded-pill bg-success text-white text-decoration-none">
                                                Approved & Limit Set
                                            </span>
                                        @endif

                                    </div>
                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center p-4 border rounded-3 bg-light">
                    <p class="mb-2 text-muted">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        No saved periods yet.
                    </p>
                    <a href="{{ route('budgetperiod.create') }}" class="btn btn-info px-4 py-2">
                        <i class="fas fa-plus-circle me-2"></i> Add Budget
                    </a>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="{{ route('budgetperiod.update', $item->Id) }}">
                            @csrf
                            @method('PATCH')
                            <div class="card p-3 border-0">
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
                                        <label for="From{{ $item->Id }}" class="form-label">From</label>
                                        <input type="date" class="form-control" id="From{{ $item->Id }}" name="From"
                                               value="{{ $item->From }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="To{{ $item->Id }}" class="form-label">To</label>
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



    @foreach($budgets as $budget)
        <!-- Modal: Choose Approve or Reject -->
        <div class="modal fade" id="decisionModal{{ $budget->Id }}" tabindex="-1" aria-labelledby="decisionLabel{{ $budget->Id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-3 shadow-sm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="decisionLabel{{ $budget->Id }}">Approval Decision</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p>
                            What would you like to do with the budget <strong>"{{ $budget->Name }}"</strong>?
                        </p>
                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <button type="button" class="btn btn-success" data-bs-dismiss="modal"
                                    data-bs-toggle="modal" data-bs-target="#approveModal{{ $budget->Id }}">
                                Approve
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"
                                    data-bs-toggle="modal" data-bs-target="#rejectModal{{ $budget->Id }}">
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal: Approve with Reason -->
        <div class="modal fade" id="approveModal{{ $budget->Id }}" tabindex="-1" aria-labelledby="approveLabel{{ $budget->Id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-3 shadow-sm">
                    <form method="POST" action="{{route('budgetapproval.approve')}}">
                        @csrf
                        @method('POST')
                        <div class="modal-header">
                            <h5 class="modal-title" id="approveLabel{{ $budget->Id }}">Approve Budget</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="BudgetID" value="{{$budget->Id}}">
                            <p>Provide a reason for approving <strong>{{ $budget->Name }}</strong>:</p>
                            <textarea name="approval_reason" rows="4" class="form-control" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success"
                                    onclick="if(this.form.checkValidity()){
                                    this.disabled = true;
                                    this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Please Wait...';
                                    this.form.submit();
                                }">
                                Submit Approval
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Modal: Reject with Reason -->
        <div class="modal fade" id="rejectModal{{ $budget->Id }}" tabindex="-1" aria-labelledby="rejectLabel{{ $budget->Id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-3 shadow-sm">
                    <form method="POST" action="{{route('budgetapproval.reject')}}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectLabel{{ $budget->Id }}">Reject Budget</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="BudgetID" value="{{$budget->Id}}">
                            <p>Provide a reason for rejecting <strong>{{ $budget->Name }}</strong>:</p>
                            <textarea name="rejection_reason" rows="4" class="form-control" required></textarea>
                        </div>
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-danger"
                                    onclick="if(this.form.checkValidity()){
                                    this.disabled = true;
                                    this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Please Wait...';
                                    this.form.submit();
                                }">
                                Submit Rejection
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal: Run Limits Update-->
        <!-- Modal: Run Limits Update -->
        <div class="modal fade" id="updateLimit{{ $budget->Id }}" tabindex="-1" aria-labelledby="decisionLabel{{ $budget->Id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 shadow-lg border-0">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold" id="decisionLabel{{ $budget->Id }}">
                            ⚠️ Run Ledger Limit Update
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Danger Alert -->
                        <div class="alert alert-danger d-flex align-items-center p-3 rounded-3 shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                            <div>
                                <h5 class="fw-bold mb-1 text-uppercase">Irreversible Action</h5>
                                <p class="mb-0">
                                    You are about to <strong>set ledger limits</strong> for the budget
                                    <strong>"{{ $budget->Name }}"</strong>.
                                    <span class="fw-bold text-danger">This action cannot be undone!</span>
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('budgetandanalytics.limits.runUpdate') }}">
                            @csrf
                            @method('POST')
                            <input type="hidden" name="BudgetID" value="{{$budget->Id}}">

{{--                            <div class="mb-3">--}}
{{--                                <label for="BudgetID" class="form-label fw-semibold">Select Budget</label>--}}
{{--                                <select id="BudgetID" name="BudgetID" class="form-select shadow-sm" required>--}}
{{--                                    <option value="">-- Choose Active Budget --</option>--}}
{{--                                    @foreach($budgets as $budget)--}}
{{--                                        <option value="{{ $budget->Id }}">--}}
{{--                                            {{ $budget->Name }} (FY {{ $budget->FiscalYear }})--}}
{{--                                        </option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-danger px-4 fw-bold"                                onclick="if(this.form.checkValidity()){
                                    this.disabled = true;
                                    this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Updating...';
                                    this.form.submit();
                                }">
                                    <i class="bi bi-rocket-takeoff me-2"></i>Run Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    @endforeach




    {{-- Shared Delete Modal --}}
    @include('components.modals.delete-confirm')

@endsection

@section('styles')
    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        .table th, .table td {
            text-align: left;
            vertical-align: middle;
            padding: 0.5rem;
        }

        .btn-sm {
            padding: 0.25rem 0.6rem;
            font-size: 0.8rem;
        }

        .card {
            border: none;
            border-radius: 0.5rem;
        }
    </style>
@endsection
