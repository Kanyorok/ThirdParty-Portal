@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Agreements')

@section('styles')
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">

    <!-- Header with Add Lease Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('addlease.create') }}" class="btn btn-primary">
            <i class="bi bi-file-earmark-plus me-1"></i> New Lease
        </a>
    </div>

    <p class="text-muted">
        <small>The list below consists of all lease agreements, both active and inactive.</small>
    </p>

    @if($newleases->count())
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="leaseagreement" class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th>Lease Number</th>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Frequency</th>
                            <th>Due Date</th>
                            <th style="width: 25%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($newleases as $newlease)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $newlease->LeaseNumber ?? '-' }}</td>
                                <td>{{ $newlease->tenant->thirdParty->TradingName ?? '-' }}</td>
                                <td>
                                    {{ $newlease->property->PropertyName ?? '-' }}
                                    @if($newlease->property && $newlease->IsActive === 'Inactive')
                                        <span class="badge bg-danger ms-2">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $newlease->StartDate ? Carbon::parse($newlease->StartDate)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $newlease->EndDate ? Carbon::parse($newlease->EndDate)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $newlease->code->Description ?? '-' }}</td>
                                <td>{{ $newlease->DueDay ?? '-' }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if ($newlease->property && $newlease->IsActive == false)
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="bi bi-x-circle me-1"></i> Inactive
                                            </button>
                                        @else
                                            <a href="{{ route('addlease.show', $newlease->Id) }}"
                                               class="btn btn-sm btn-success">
                                                <i class="bi bi-eye me-1"></i> View
                                            </a>
                                        @endif

                                        <a href="{{ route('addlease.edit', $newlease->Id) }}"
                                           class="btn btn-sm btn-warning text-white">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </a>

                                        @if($newlease->invoices()->exists())
                                            <button class="btn btn-sm btn-secondary" disabled>In Use</button>
                                        @else
                                            <form action="{{ route('addlease.destroy', $newlease->Id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to delete this lease?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i> No lease agreements registered yet.
        </div>
    @endif

</div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#leaseagreement').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
