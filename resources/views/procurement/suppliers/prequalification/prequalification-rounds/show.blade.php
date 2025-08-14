@extends('layouts.app')

@section('title', 'Prequalification Round Details')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-primary">Prequalification Round: {{ $prequalificationRound->Title }}</h4>
            <div>
                <a href="{{ route('prequalification.prequalification-rounds.edit', $prequalificationRound) }}" class="btn btn-warning me-2">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('prequalification.prequalification-rounds.index') }}" class="btn btn-light">Back to List</a>
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