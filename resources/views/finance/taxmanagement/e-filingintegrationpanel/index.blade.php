@extends('layouts.app')
@section('title', 'e-Filing Integration')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">🌐 e-Filing Integration</h4>

        <div class="alert alert-warning">
            <strong>Coming Soon:</strong> This panel will allow integration with your country’s tax authority (e.g.,
            <strong>KRA iTax</strong>) for direct submission of VAT, WHT, and other returns via secure APIs.
        </div>

        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between">
                <span>Integration with KRA iTax (Kenya)</span>
                <span class="badge bg-secondary">Planned</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>Return Status Sync</span>
                <span class="badge bg-secondary">Planned</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>API Token Configuration</span>
                <span class="badge bg-secondary">Planned</span>
            </li>
        </ul>
    </div>
@endsection
