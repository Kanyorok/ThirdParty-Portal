@extends('layouts.app')

@section('title', 'My Evaluations')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-primary"><i class="fas fa-clipboard-list me-2"></i>My Evaluations</h4>
        </div>
        <div class="card-body">
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if ($evaluations->isEmpty())
            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i>No Evaluations Found</h4>
                <p>There are no evaluations assigned to you yet. You can start evaluating applications by navigating to the application list.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="text-nowrap">Application #</th>
                            <th scope="col" class="text-nowrap">Supplier</th>
                            <th scope="col" class="text-nowrap">Criteria</th>
                            <th scope="col" class="text-nowrap">Score</th>
                            <th scope="col" class="text-nowrap">Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($evaluations as $evaluation)
                        <tr>
                            <td>{{ $evaluation->ApplicationID }}</td>
                            <td>{{ optional($evaluation->application)->supplier->SupplierName ?? 'N/A' }}</td>
                            <td>
                                <strong>{{ optional($evaluation->criteria->masterCriteria)->CriteriaName ?? 'N/A' }}</strong>
                                <small class="d-block text-muted">{{ optional($evaluation->criteria->masterCriteria)->Description ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary me-1">{{ $evaluation->Score }}</span>
                                / {{ $evaluation->MaxScore }}
                            </td>
                            <td>{{ $evaluation->Remarks }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        <div class="card-footer bg-light d-flex justify-content-center">
            {{ $evaluations->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
@endsection