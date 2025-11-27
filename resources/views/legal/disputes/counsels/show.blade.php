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
                <a href="{{ route('legal.disputes.counsels.index', $case->Id) }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            {{-- Body --}}
            <div class="card-body bg-white">
                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light text-info">
                            <strong>Name:</strong>
                            <div class="text-dark fw-semibold">{{ $counsel->CounselName }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light text-info">
                            <strong>Firm:</strong>
                            <div class="text-dark fw-semibold">{{ $counsel->FirmName ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light text-info">
                            <strong>Email:</strong>
                            <div class="text-dark fw-semibold">{{ $counsel->Email ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light text-info">
                            <strong>Phone:</strong>
                            <div class="text-dark fw-semibold">{{ $counsel->Phone ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="p-3 border rounded bg-light text-info">
                            <strong>Role:</strong>
                            <div class="text-dark fw-semibold">{{ $counsel->Role ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
