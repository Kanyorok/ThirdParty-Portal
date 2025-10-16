@extends('layouts.app')

@section('title', 'Prequalification Round Not Configured')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0 text-danger">Prequalification Round Not Configured</h4>
        </div>
        <div class="card-body">
            <p>The selected application <strong>#{{ optional($application)->applicationNo ?? 'N/A' }}</strong> does not have a prequalification round configured.</p>
            <p>Please contact the procurement administrator to assign this application to an active prequalification round before viewing results or evaluating.</p>

            <div class="mt-3">
                <a href="{{ url()->previous() }}" class="btn btn-secondary me-2">Back</a>
                @can('manage prequalification')
                <a href="{{ route('prequalification.rounds.index') }}" class="btn btn-primary">Manage Rounds</a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
