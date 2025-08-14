@extends('layouts.app')

@section('title', 'Create Prequalification Round')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-primary">Create Prequalification Round</h4>
            <a href="{{ route('prequalification.prequalification-rounds.index') }}" class="btn btn-light">Back</a>
        </div>
        <div class="card-body">
            @include('procurement.suppliers.prequalification.prequalification-rounds._form', [
            'masterSections' => $masterSections
            ])
        </div>
    </div>
</div>
@endsection