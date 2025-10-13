@extends('layouts.app')

@section('title', 'License Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- License Status Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-certificate me-2"></i>
                        Current License Status
                    </h5>
                    <div>
                        <a href="{{ route('admin.licensing.create') }}" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-upload me-1"></i> Upload License
                        </a>
                        <form method="POST" action="{{ route('admin.licensing.refresh') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">
                                <i class="fas fa-refresh me-1"></i> Refresh
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    @if($status['valid'])
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="badge bg-success me-3 p-2">
                                        <i class="fas fa-check-circle fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-success">Licensed System</h6>
                                        <small class="text-muted">{{ $status['tenant_name'] }} - {{ $status['edition'] }}</small>
                                    </div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-light rounded">
                                            <h4 class="mb-1 text-primary">{{ count($status['modules']) }}</h4>
                                            <small class="text-muted">Licensed Modules</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-light rounded">
                                            <h4 class="mb-1 text-info">{{ $status['max_users'] }}</h4>
                                            <small class="text-muted">Max Users</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-light rounded">
                                            <h4 class="mb-1 {{ $status['days_until_expiry'] > 30 ? 'text-success' : ($status['days_until_expiry'] > 0 ? 'text-warning' : 'text-danger') }}">
                                                {{ $status['days_until_expiry'] ?? 'N/A' }}
                                            </h4>
                                            <small class="text-muted">Days Until Expiry</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-2 bg-light rounded">
                                            <h4 class="mb-1 text-secondary">{{ count($status['features']) }}</h4>
                                            <small class="text-muted">Features</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2">Licensed Modules</h6>
                                <div class="mb-3" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($status['modules'] as $module)
                                        <span class="badge bg-primary me-1 mb-1">{{ $module }}</span>
                                    @endforeach
                                </div>
                                
                                @if($status['features'])
                                <h6 class="mb-2">Premium Features</h6>
                                <div class="mb-3">
                                    @foreach($status['features'] as $feature => $enabled)
                                        @if($enabled)
                                        <span class="badge bg-success me-1 mb-1">{{ $feature }}</span>
                                        @endif
                                    @endforeach
                                </div>
                                @endif
                                
                                @if($status['limits'])
                                <h6 class="mb-2">Limits</h6>
                                <div class="small">
                                    @foreach($status['limits'] as $limit => $value)
                                    <div>{{ ucfirst(str_replace('_', ' ', $limit)) }}: <strong>{{ $value }}</strong></div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        @if($status['in_grace_period'])
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Grace Period:</strong> Your license has expired but you're still within the grace period. Please renew soon.
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <div class="badge bg-danger me-3 p-3">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                            <h5 class="text-danger mt-2">Invalid License</h5>
                            <p class="text-muted">{{ $status['error'] }}</p>
                            <a href="{{ route('admin.licensing.create') }}" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Upload Valid License
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Instance Information Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-server me-2"></i>
                        Instance Information
                    </h5>
                    <a href="{{ route('admin.licensing.instance.download') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-1"></i> Download Info
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Database GUID</h6>
                            <code class="small">{{ $instance->DbGuid }}</code>
                        </div>
                        <div class="col-md-6">
                            <h6>Host Fingerprint</h6>
                            <code class="small text-truncate d-block">{{ Str::limit($instance->HostFingerprint, 40) }}</code>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Provide this information when requesting a new license.
                    </small>
                </div>
            </div>

            <!-- Recent Licenses -->
            @if($allLicenses->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        License History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>License ID</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Last Validated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allLicenses as $license)
                                <tr>
                                    <td><code>{{ $license->LicenseId }}</code></td>
                                    <td>
                                        @if($license->Status == 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Revoked</span>
                                        @endif
                                    </td>
                                    <td>{{ $license->CreatedOn->format('M j, Y g:i A') }}</td>
                                    <td>
                                        @if($license->LastValidatedOn)
                                            {{ $license->LastValidatedOn->format('M j, Y g:i A') }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.licensing.show', $license) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        @if($license->Status == 1)
                                        <form method="POST" action="{{ route('admin.licensing.destroy', $license) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Are you sure you want to revoke this license?')">
                                                Revoke
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Audit Logs -->
            @if($recentAudits->count() > 0)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Recent Activity
                    </h5>
                    <a href="{{ route('admin.licensing.audit') }}" class="btn btn-outline-secondary btn-sm">
                        View All Logs
                    </a>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($recentAudits as $audit)
                        <div class="d-flex mb-2">
                            <div class="me-3">
                                @php
                                    $eventIcons = [
                                        'validated' => 'fas fa-check-circle text-success',
                                        'failed_signature' => 'fas fa-times-circle text-danger',
                                        'expired' => 'fas fa-clock text-warning',
                                        'module_denied' => 'fas fa-ban text-warning',
                                        'license_uploaded' => 'fas fa-upload text-info',
                                    ];
                                @endphp
                                <i class="{{ $eventIcons[$audit->Event] ?? 'fas fa-info-circle text-muted' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $audit->Event)) }}</strong>
                                    @if($audit->Detail)
                                        - {{ $audit->Detail }}
                                    @endif
                                </div>
                                <div class="text-muted small">
                                    {{ $audit->EventAt->diffForHumans() }}
                                    @if($audit->LicenseId)
                                        | {{ $audit->LicenseId }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}
</style>
@endpush
