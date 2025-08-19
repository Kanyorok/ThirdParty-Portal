@extends('layouts.app')
@section('title', 'View Counsel')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border rounded-4 overflow-hidden">
        
        {{-- Header --}}
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
            <h5 class="text-info mb-0">
                <i class="fas fa-user-tie"></i> Counsel Details
            </h5>
            <a href="{{ route('legal.disputes.counsels.index', $case->Id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        {{-- Body --}}
        <div class="card-body bg-white">
            <div class="row g-3">

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Name:</strong>
                        <div>{{ $counsel->CounselName }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Firm:</strong>
                        <div>{{ $counsel->FirmName ?? '—' }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Email:</strong>
                        <div>{{ $counsel->Email ?? '—' }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Phone:</strong>
                        <div>{{ $counsel->Phone ?? '—' }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Role:</strong>
                        <div>{{ $counsel->Role ?? '—' }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <strong>Assigned On:</strong>
                        <div>
                            {{ $counsel->AssignedOn ? \Carbon\Carbon::parse($counsel->AssignedOn)->format('d M Y') : '—' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Remarks --}}
            <div class="border rounded p-3 bg-light mt-4">
                <h6 class="text-info fw-bold mb-1">
                    <i class="fas fa-sticky-note"></i> Remarks:
                </h6>
                <p class="mb-0 fw-semibold">{{ $counsel->Remarks ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
