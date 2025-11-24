@extends('layouts.app')
@section('title', 'Case Outcome Details')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm border rounded-4 overflow-hidden">

            {{-- Header --}}
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                <h5 class="text-info mb-0">
                    <i class="fas fa-balance-scale"></i> Outcome Details
                </h5>
                <a href="{{ route('legal.cases.show', $case->Id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Outcomes
                </a>
            </div>

            {{-- Body --}}
            <div class="card-body bg-white">
                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <strong class="text-info">Outcome:</strong>
                            <div class="mb-0 fw-semibold">{{ $outcome->Outcome }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <strong class="text-info">Judgment Date:</strong>
                            <div
                                class="mb-0 fw-semibold">{{ \Carbon\Carbon::parse($outcome->JudgmentDate)->format('d-m-Y') }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <strong class="text-info">Judge:</strong>
                            <div class="mb-0 fw-semibold">{{ $outcome->JudgeName }}</div>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <strong class="text-info">Penalty:</strong>
                            <div class="mb-0 fw-semibold">{{ number_format($outcome->PenaltyAmount, 2) }}</div>
                        </div>
                    </div>
                </div>

                <div class="p-3 border rounded bg-light mt-4">
                    <strong class="text-info">Decision:</strong>
                    <div class="mb-0 fw-semibold">{{ $outcome->CourtDecision }}</div>
                </div>

                {{-- Remarks --}}
                <div class="border rounded p-3 bg-light mt-4">
                    <h6 class="text-info fw-bold mb-1">
                        <i class="fas fa-sticky-note"></i> Remarks:
                    </h6>
                    <p class="mb-0 fw-semibold">{{ $outcome->Remarks ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
