@extends('layouts.app')
@section('title', 'Policy Register')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-journal-text me-2"></i> Issued Policies Register
            </h5>
        </div>

        <div class="card-body">
            <p class="text-muted small mb-4">
                <i class="bi bi-info-circle me-2 text-primary"></i>
                Below is the list of all policies that have been issued and are currently active.
            </p>

            <table id="register" class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 5%">#</th>
                        <th>Policy Number</th>
                        <th>Customer</th>
                        <th>Insurer</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th class="text-center" style="width: 10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($policies as $policy)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <span class="fw-semibold text-primary">{{ $policy->PolicyNumber ?? '-' }}</span>
                            </td>
                            <td>{{ $policy->customer->thirdParty->ThirdPartyName ?? '-' }}</td>
                            <td>{{ $policy->insurer->Name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $policy->Status->badgeColor() ?? 'success' }}">
                                    {{ $policy->Status->Label() }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($policy->PolicyStartDate)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <a href="{{ route('bancassurance.policies.show', $policy->Id) }}" 
                                   class="btn btn-sm btn-outline-info px-3">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#register').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search policies..."
                }
            });
        });
    </script>
@endsection
@endsection
