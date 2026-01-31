@extends('layouts.app')

@section('title', 'Prequalification Round Details')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-primary">Prequalification Round: {{ $prequalificationRound->Title }}</h4>
                <div>
                    @if($prequalificationRound->Status !== \App\Enums\Procurement\PrequalificationRoundEnum::Draft)
                    <a href="{{ route('prequalification.applications.create', ['round_id' => $prequalificationRound->RoundID]) }}" 
                       class="btn btn-primary me-2">
                        <i class="fas fa-plus-circle"></i> Apply for Prequalification
                    </a>
                    @endif
                    <a href="{{ route('prequalification.prequalification-rounds.edit', $prequalificationRound) }}"
                       class="btn btn-warning me-2">
                        <i class="fas fa-edit"></i> Edit Round
                    </a>
                    <a href="{{ route('prequalification.prequalification-rounds.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                @include('procurement.suppliers.prequalification.prequalification-rounds._form', [
                'prequalificationRound' => $prequalificationRound,
                'masterSections' => $masterSections,
                'readOnly' => true
                ])
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          xintegrity="sha512-SnH5WK+bZxgPHs44uW/j/jJ/1k1Fw/Gg6wzR5yV7qg6l2P7x3t4q8G+3j/6Cj9q/q8Cg2s8/j2VqQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
@endpush
