@extends('layouts.app')

@section('title', 'Create Prequalification Round')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-primary">Create Prequalification Round</h4>
            <a href="{{ route('prequalification.prequalification-rounds.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-circle-left"></i> Back To Rounds
            </a>
        </div>
        <div class="card-body">
            @include('procurement.suppliers.prequalification.prequalification-rounds._form', [
            'masterSections' => $masterSections
            ])
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uW/j/jJ/1k1Fw/Gg6wzR5yV7qg6l2P7x3t4q8G+3j/6Cj9q/q8Cg2s8/j2VqQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush