@extends('layouts.app')
@section('title', '📄 View Contract Details')

@section('content')
    <div class="container mt-4">
        <h4>📄 Contract Details – ID: {{ $id }}</h4>

        <div class="card">
            <div class="card-body">
                <p><strong>Ref No:</strong> CONTRACT/PROC/2025/00{{ $id }}</p>
                <p><strong>Vendor:</strong> OfficePro Ltd</p>
                <p><strong>Status:</strong> Active</p>
                <p><strong>Period:</strong> 2025-07-01 to 2025-12-31</p>
            </div>
        </div>
    </div>
@endsection
