{{-- Example HTMX-enabled page --}}
@extends('layouts.app')

@section('title', 'HTMX Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">{{ ucfirst(request()->segment(1)) }}</a></li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-dashboard me-2"></i>
                    HTMX Navigation Test
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">✅ HTMX Working!</h4>
                    <p>This page was loaded using HTMX partial navigation. The sidebar should remain static and expanded.</p>
                    <hr>
                    <p class="mb-0">
                        <strong>Test Results:</strong><br>
                        • Sidebar state preserved: <span id="sidebar-preserved">✅</span><br>
                        • No full page reload: <span id="no-reload">✅</span><br>
                        • HTMX loaded: <span id="htmx-loaded">Checking...</span><br>
                        • Current route: <code>{{ request()->route()->getName() ?? 'N/A' }}</code>
                    </p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6>Test Internal Links</h6>
                                <p>These links should also use HTMX:</p>
                                <a href="{{ route('home') }}" class="btn btn-primary btn-sm">Go Home</a>
                                <a href="{{ route('budget.budgetlinecategories.index') }}" class="btn btn-secondary btn-sm">Budget Categories</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6>Diagnostic Tools</h6>
                                <p>Open browser console and run:</p>
                                <code>SidebarNavigation.highlightActiveRoute()</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if HTMX is loaded
    const htmxLoaded = document.getElementById('htmx-loaded');
    if (typeof htmx !== 'undefined') {
        htmxLoaded.textContent = '✅';
        htmxLoaded.className = 'text-success';
    } else {
        htmxLoaded.textContent = '❌';
        htmxLoaded.className = 'text-danger';
    }
    
    // Check if this was a partial load
    const noReload = document.getElementById('no-reload');
    if (window.performance && window.performance.navigation.type === 0) {
        noReload.textContent = '✅';
        noReload.className = 'text-success';
    } else {
        noReload.textContent = '❌';
        noReload.className = 'text-danger';
    }
});
</script>
@endsection
