@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'RFQs List')

@section('content')
    <div class="container">
        <!-- Filters -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="created_by" class="form-select">
                    <option value="">All Creators</option>
                    @foreach($allUsers as $u)
                        <option value="{{ $u->Id }}" {{ request('created_by') == $u->Id ? 'selected' : '' }}>{{ $u->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary">Filter</button>
                <a href="{{ route('rfqs.index') }}" class="btn btn-link">Reset</a>
            </div>
        </form>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Button to trigger modal -->
        @can('create', \App\Models\Procurement\RFQ::class)
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createRFQModal">
                + New RFQ
            </button>
        @endcan

        @if($rfqs->count())
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>
                        @php $dir = request('sort_by') === 'RFQNumber' && request('sort_dir') === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="?{{ http_build_query(array_merge(request()->except('page'), ['sort_by' => 'RFQNumber', 'sort_dir' => $dir])) }}">Quotation Number
                            @if(request('sort_by') === 'RFQNumber')
                                {!! request('sort_dir') === 'asc' ? '&uarr;' : '&darr;' !!}
                            @endif
                        </a>
                    </th>
                    <th>Requisition No</th>
                    <th>
                        @php $dir = request('sort_by') === 'Status' && request('sort_dir') === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="?{{ http_build_query(array_merge(request()->except('page'), ['sort_by' => 'Status', 'sort_dir' => $dir])) }}">Quotation Status
                            @if(request('sort_by') === 'Status')
                                {!! request('sort_dir') === 'asc' ? '&uarr;' : '&darr;' !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        @php $dir = request('sort_by') === 'SubmissionDeadline' && request('sort_dir') === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="?{{ http_build_query(array_merge(request()->except('page'), ['sort_by' => 'SubmissionDeadline', 'sort_dir' => $dir])) }}">Submission Deadline
                            @if(request('sort_by') === 'SubmissionDeadline')
                                {!! request('sort_dir') === 'asc' ? '&uarr;' : '&darr;' !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        @php $dir = request('sort_by') === 'CreatedBy' && request('sort_dir') === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="?{{ http_build_query(array_merge(request()->except('page'), ['sort_by' => 'CreatedBy', 'sort_dir' => $dir])) }}">Created By
                            @if(request('sort_by') === 'CreatedBy')
                                {!! request('sort_dir') === 'asc' ? '&uarr;' : '&darr;' !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        @php $dir = request('sort_by') === 'CreatedOn' && request('sort_dir') === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="?{{ http_build_query(array_merge(request()->except('page'), ['sort_by' => 'CreatedOn', 'sort_dir' => $dir])) }}">Created On
                            @if(request('sort_by') === 'CreatedOn')
                                {!! request('sort_dir') === 'asc' ? '&uarr;' : '&darr;' !!}
                            @endif
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rfqs as $rfq)
                    <tr>
                        {{-- pagination-aware index: first item on current page + loop index --}}
                        <td>{{ ($rfqs->currentPage() - 1) * $rfqs->perPage() + $loop->iteration }}</td>
                        <td>{{ $rfq->RFQNumber ?? '-' }}</td>
                        <td>{{ $rfq->requisition->RequisitionNo ?? '-' }}</td>
                        <td>{{ $rfq->Status ?? '-' }}</td>
                        <td>{{ $rfq->SubmissionDeadline ? Carbon::parse($rfq->SubmissionDeadline)->format('d M Y') : '-' }}</td>
                        <td>{{ $createdByMap[$rfq->CreatedBy] ?? '-' }}</td>
                        <td>{{ $rfq->CreatedOn ? Carbon::parse($rfq->CreatedOn)->format('d M Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('rfqs.show', $rfq->Id) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $rfqs->links() }}
            </div>
        @else
            <p>No RFQs created yet.</p>
        @endif
    </div>

    <!-- Modal -->
    @can('create', \App\Models\Procurement\RFQ::class)
    <div class="modal fade" id="createRFQModal" tabindex="-1" aria-labelledby="createRFQModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('rfqs.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createRFQModalLabel">Create RFQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="RequisitionId" class="form-label">Select Requisition <span
                                class="text-danger">*</span></label>
                        <select name="RequisitionId" id="RequisitionId" class="form-select" required>
                            <option value="">-- Choose Requisition --</option>
                            @foreach($requisitions as $requisition)
                                <option
                                    value="{{ $requisition->Id }}">{{ $requisition->RequisitionNo }} @if(!empty($requisition->PlanTitle))
                                        - {{ $requisition->PlanTitle }}
                                    @endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="Comments" class="form-label">Comments <span class="text-danger">*</span></label>
                        <textarea name="Comments" id="Comments" rows="3" class="form-control" required></textarea>
                    </div>

                    <!-- Submission Deadline -->
                    <div class="mb-3">
                        <label for="SubmissionDeadline" class="form-label">Submission Deadline <span
                                class="text-danger">*</span></label>
                        <input type="date" name="SubmissionDeadline" id="SubmissionDeadline" class="form-control"
                               required
                               min="{{ Carbon::now()->toDateString() }}">

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save RFQ</button>
                </div>
            </form>
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
    @endcan
    <script>
        @if($errors->any())
        var createRFQModal = new bootstrap.Modal(document.getElementById('createRFQModal'));
        createRFQModal.show();
        @endif
    </script>

@endsection
