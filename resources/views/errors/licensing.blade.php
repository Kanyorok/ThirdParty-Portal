@extends('layouts.app')

@section('title', $title ?? 'Module Not Licensed')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <h4 class="mb-0">{{ $title ?? 'Module Not Licensed' }}</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-lock text-warning" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h5 class="text-muted mb-3">Access Denied</h5>
                    <p class="lead mb-4">{{ $message ?? 'The requested module is not available in your current license.' }}</p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Need access to this module?</strong><br>
                        Contact your system administrator to upgrade your license or verify your current licensing status.
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('home') }}" class="btn btn-primary me-2">
                            <i class="fas fa-home me-1"></i> Return to Dashboard
                        </a>
                        <a href="javascript:history.back()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Go Back
                        </a>
                    </div>
                </div>
                
                @auth
                @if(auth()->user()->hasRole('Super Admin'))
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="fas fa-cog me-1"></i>
                        <strong>Administrator:</strong> 
                        <a href="{{ route('settings.licensing') ?? '#' }}" class="text-decoration-none">
                            Manage licensing settings
                        </a>
                    </small>
                </div>
                @endif
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .text-warning {
        color: #f39c12 !important;
    }
</style>
@endpush
