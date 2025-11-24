@extends('layouts.app')
@section('title', 'View Clause')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm border rounded-4 overflow-hidden">

            {{-- Header --}}
            <div class="card-header bg-light  d-flex justify-content-between align-items-center py-3">
                <h5 class=" text-info mb-0">
                    <i class="fas fa-file-alt"></i> Clause Details
                </h5>
                <a href="{{ route('legal.clauses.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            {{-- Body --}}
            <div class="card-body bg-white">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <strong>Title:</strong>
                            <div>{{ $clause->Title }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <strong>Type:</strong>
                            <div>{{ $clause->ClauseType }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <strong>Version:</strong>
                            <div>{{ $clause->Version ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <strong>Standard:</strong>
                            <div>
                            <span class="badge {{ $clause->IsStandard ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                                {{ $clause->IsStandard ? 'Yes' : 'No' }}
                            </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Clause Content --}}
                <div class=" border rounded p-3 bg-light mt-4">
                    <h6 class="text-info fw-bold mb-1"><i class="fas fa-scroll"></i> Clause Content:</h6>
                    <p class=" mb-0 fw-semibold">{{ $clause->Content }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
