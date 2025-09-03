@extends('layouts.app')

@section('title', 'Prequalification Rounds')

@section('content')
<div class="container-fluid py-4">

    {{-- Flash Success --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Header --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0 text-primary">
                <i class="bi bi-list-check me-2"></i> Prequalification Rounds
            </h4>
            <a href="{{ route('prequalification.prequalification-rounds.create') }}" class="btn btn-success d-flex align-items-center" data-ajax="1">
                <i class="fa fa-plus-circle me-2"></i> Create New Round
            </a>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table id="roundsTable" class="table table-striped table-hover mb-0 align-middle" data-datatable="auto" data-dt-opts='{"pageLength":10,"order":[[3,"desc"]],"responsive":true,"language":{"search":"_INPUT_","searchPlaceholder":"Search rounds..."}}'>
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">Round ID</th>
                        <th style="width: 25%;">Round Title</th>
                        <th style="width: 10%;">Needed Vendors</th>
                        <th style="width: 12%;">Start Date</th>
                        <th style="width: 12%;">End Date</th>
                        <th style="width: 10%;">Round Status</th>
                        <th style="width: 20%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prequalificationRounds as $Round)
                    <tr>
                        <td>{{ $Round->RoundID }}</td>
                        <td>{{ $Round->Title }}</td>
                        <td>{{ $Round->MaxVendors }}</td>
                        <td>{{ $Round->StartDate->format('Y-m-d') }}</td>
                        <td>{{ $Round->EndDate->format('Y-m-d') }}</td>
                        <td>
                            <span class="badge {{ $Round->Status->getBadgeClass() }}">
                                {{ $Round->Status->label() }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('prequalification.prequalification-rounds.show', $Round) }}"
                                class="btn btn-sm btn-info text-white me-1" data-ajax="1">
                                <i class="bi bi-eye"></i> View
                            </a>

                            <a href="{{ route('prequalification.prequalification-rounds.edit', $Round) }}"
                                class="btn btn-sm btn-warning me-1" data-ajax="1">
                                <i class="bi bi-pencil"></i> Edit
                            </a>

                            <form action="{{ route('prequalification.prequalification-rounds.destroy', $Round) }}"
                                method="POST" class="d-inline-flex"
                                onsubmit="return confirm('Are you sure you want to delete this round?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <div class="mb-3">
                                <i class="bi bi-folder-x display-4 text-secondary"></i>
                            </div>
                            <p class="lead mb-3">No prequalification rounds found.</p>
                            <a href="{{ route('prequalification.prequalification-rounds.create') }}" class="btn btn-primary" data-ajax="1">
                                <i class="fa fa-plus-circle me-1"></i> Create a New Round
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('partial:loaded', function(){
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush