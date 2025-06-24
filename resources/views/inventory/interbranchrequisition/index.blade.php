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
    <h4 class="mb-3">Inter-Branch Requisition List</h4>

    <div class="mb-3 d-flex justify-content-between align-items-end flex-wrap">
        <a href="{{ route('interbranchrequisition.create') }}" class="btn btn-sm btn-success mb-2">➕ Add Requisition</a>
        <form id="filterForm" method="GET" class="d-flex align-items-center mb-2">
            <label for="filterStatus" class="me-2 fw-bold">Filter by status:</label>
            <select id="filterStatus" name="status" class="form-select form-select-sm me-2" style="width: 170px;">
                <option value="">Show All</option>
                <option value="Ap" {{ request('status') == 'Ap' ? 'selected' : '' }}>Approved</option>
                <option value="su" {{ request('status') == 'su' ? 'selected' : '' }}>Pending Approval</option>
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
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($groupedRequisitions as $requisition)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $requisition->ReqNo ?? '-' }}</td>
                            <td>{{ $requisition->fromBranch->Name ?? '-' }}</td>
                            <td>{{ $requisition->toBranch->Name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($requisition->CreatedOn)->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $statusEnum = \App\Enums\Inventory\InterBranchRequisitionEnum::tryFrom($requisition->Status);
                                @endphp
                                @if($statusEnum)
                                    <span
                                        class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                                @else
                                    <span class="badge bg-warning">{{ $requisition->Status }}</span>
                                @endif
                            </td>
                            <td>{{ $requisition->items->count() }}</td>
                            <td>
                                <a href="{{ route('interbranchrequisition.show', $requisition->Id) }}"
                                   class="btn btn-secondary btn-sm">View</a>
                                <a href="{{ route('interbranchrequisition.edit', $requisition->Id) }}"
                                   class="btn btn-warning btn-sm"
                                   onclick="@if($requisition->Status !== 'su') return showCustomError('You cannot edit this requisition because a decision has already been made.'); @endif">
                                    Edit
                                </a>
                                <a href="#"
                                   class="btn btn-danger btn-sm"
                                   onclick="@if($requisition->Status !== 'su') return showCustomError('You cannot delete this requisition because a decision has already been made.'); @else confirmDelete('{{ $requisition->Id }}'); return false; @endif">
                                    Delete
                                </a>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#requisitionTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: false, // Hide "Show N entries"
                dom: 'rt<"bottom"ip><"clear">'
            });
        });

        function confirmDelete(Id) {
            if (confirm('⚠️ Are you sure you want to delete this requisition?')) {
                document.getElementById('delete-form-' + Id).submit();
            }
        }

        function showCustomError(message) {
            document.getElementById('customErrorMessage').childNodes[0].nodeValue = message;
            document.getElementById('customErrorContainer').style.display = 'block';
            window.scrollTo({top: 0, behavior: 'smooth'});
            return false;
        }

        function hideCustomError() {
            document.getElementById('customErrorContainer').style.display = 'none';
        }
    </script>
@endsection
