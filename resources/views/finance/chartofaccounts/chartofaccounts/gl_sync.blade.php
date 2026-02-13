@extends('layouts.app')

@section('title', 'GL Sync')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                <h5 class="mb-0 text-info">🔄 Sync General Ledgers</h5>
                <a href="{{ route('chartofaccounts.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card-body">
                <p class="text-muted">Use this page to initiate synchronization of GL accounts with third-party systems.</p>

                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                        This is a placeholder view for upcoming GL sync workflows. Hook your sync actions here.
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="button" disabled>
                        <i class="fas fa-sync-alt me-1"></i> Start Sync (coming soon)
                    </button>
                    <button class="btn btn-outline-secondary" type="button" disabled>
                        <i class="fas fa-history me-1"></i> View Logs (coming soon)
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
