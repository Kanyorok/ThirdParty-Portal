@extends('layouts.app')

@section('title', 'Inter-Branch Requisition')

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="sessionErrorAlert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="sessionSuccessAlert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div id="customErrorContainer" style="display:none;">
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="customErrorMessage">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                onclick="hideCustomError()"></button>
    </div>
</div>

<div class="container mt-4">

    <div class="mb-3 d-flex justify-content-between align-items-end flex-wrap">
        <a href="{{ route('interbranchrequisition.create') }}" class="btn btn-sm btn-success mb-2">
            <i class="bi bi-plus-circle"></i> Add Requisition
        </a>
        <form id="filterForm" method="GET" class="d-flex align-items-center mb-2">
            <label for="filterStatus" class="me-2 fw-bold">Filter by status:</label>
            <select id="filterStatus" name="status" class="form-select form-select-sm me-2" style="width: 170px;">
                <option value="">Show All</option>
                <option value="Ap" {{ request('status') == 'Ap' ? 'selected' : '' }}>Approved</option>
                <option value="P" {{ request('status') == 'P' ? 'selected' : '' }}>Pending Approval</option>
                <option value="Re" {{ request('status') == 'Re' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if(request()->has('status') && request('status') !== null && request('status') !== "")
                <a href="{{ route('interbranchrequisition.index') }}" class="btn btn-link btn-sm ms-2">Reset</a>
            @endif
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="requisitionTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Requisition No</th>
                        <th>From Branch</th>
                        <th>To Branch</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($groupedRequisitions as $requisition)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $requisition->ReqNo ?? '-' }}</td>
                            <td>{{ $requisition->fromBranch->Name ?? '-' }}</td>
                            <td>{{ $requisition->toBranch->Name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($requisition->CreatedOn)->format('d M Y') }}</td>                           <td>
                                @php
                                    $statusEnum = \App\Enums\Inventory\InterBranchRequisitionEnum::tryFrom($requisition->Status);
                                @endphp
                                @if($statusEnum)
                                    <span class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                                @else
                                    <span class="badge bg-warning">{{ $requisition->Status }}</span>
                                @endif
                            </td>
                            <td>{{ $requisition->items->count() }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('interbranchrequisition.show', $requisition->Id) }}"
                                       class="btn btn-view btn-sm"
                                       data-bs-toggle="tooltip"
                                       title="View Requisition">
                                        <i class="bi bi-eye text-white"></i>
                                    </a>
                                    <a href="{{ route('interbranchrequisition.edit', $requisition->Id) }}"
                                       class="btn btn-edit btn-sm @if($requisition->Status !== 'P') disabled @endif"
                                       data-bs-toggle="tooltip"
                                       title="@if($requisition->Status !== 'P') Cannot edit - decision made @else Edit Requisition @endif"
                                       onclick="@if($requisition->Status !== 'P') return showCustomError('You cannot edit this requisition because a decision has already been made.'); @endif">
                                        <i class="bi bi-pencil text-white"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-delete btn-sm @if($requisition->Status !== 'P') disabled @endif"
                                            data-bs-toggle="tooltip"
                                            title="@if($requisition->Status !== 'P') Cannot delete - decision made @else Delete Requisition @endif"
                                            @if($requisition->Status === 'P')
                                            onclick="confirmDelete('{{ $requisition->Id }}', '{{ $requisition->ReqNo }}')"
                                            @else
                                            onclick="return showCustomError('You cannot delete this requisition because a decision has already been made.');"
                                            @endif>
                                        <i class="bi bi-trash text-white"></i>
                                    </button>
                                </div>

                                <form id="delete-form-{{ $requisition->Id }}"
                                      action="{{ route('interbranchrequisition.destroy', $requisition->Id) }}"
                                      method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#requisitionTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: false,
            dom: 'rt<"bottom"ip><"clear">',
            language: {
                emptyTable: "No requisitions found."
            },
            drawCallback: function() {
                // Initialize tooltips after each table draw
                initializeTooltips();
            }
        });

        // Initialize tooltips
        function initializeTooltips() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Initialize tooltips on page load
        initializeTooltips();
    });

    function confirmDelete(Id, reqNo) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete requisition: " + reqNo + ". This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + Id).submit();
            }
        });
    }

    function showCustomError(message) {
        Swal.fire({
            title: 'Action Not Allowed',
            text: message,
            icon: 'warning',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
        return false;
    }

    function hideCustomError() {
        document.getElementById('customErrorContainer').style.display = 'none';
    }
</script>

<style>
.btn-group .btn {
    border-radius: 0.375rem;
    margin-right: 0.25rem;
    padding: 0.25rem 0.5rem;
    border: none;
    transition: all 0.2s ease-in-out;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Ensure tooltips work properly */
.tooltip {
    pointer-events: none;
}

/* Solid background colors with white icons */
.btn-view {
    background-color: #5b6b79 !important;
    color: white !important;
}

.btn-edit {
    background-color: #e58a00 !important;
    color: white !important;
}

.btn-delete {
    background-color: #dc3545 !important;
    color: white !important;
}

/* Hover effects */
.btn-view:hover {
    background-color: #0b5ed7 !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-edit:hover:not(.disabled) {
    background-color: #e0a800 !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-delete:hover:not(.disabled) {
    background-color: #c82333 !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Disabled state for buttons when decision is made */
.btn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
}

.btn.disabled:hover {
    background-color: inherit !important;
    transform: none !important;
    box-shadow: none !important;
}

/* Ensure icons are properly sized and white */
.bi {
    font-size: 0.875rem;
    color: white;
}

/* Badge styling */
.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

/* Table responsive adjustments */
.table-responsive {
    border-radius: 0.375rem;
}

/* Add Requisition button styling */
.btn-success {
    background-color: #198754;
    border-color: #198754;
}

.btn-success:hover {
    background-color: #157347;
    border-color: #146c43;
}
</style>
@endsection
