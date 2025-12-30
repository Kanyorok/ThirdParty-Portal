@extends('layouts.app')
@section('title', 'Load Opening Stock')
@section('content')
<div class="container mt-4">

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('failures'))
        <div class="alert alert-warning">
            <strong>⚠ Some rows contain invalid data:</strong>
            <ul class="mt-2">
                @foreach(session('failures') as $failure)
                    <li>
                        Row {{ $failure->row() }}:
                        @foreach($failure->errors() as $error)
                            <span class="text-danger">{{ $error }}</span>
                        @endforeach
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Bulk Upload Card -->
    <div class="card shadow">
        <small class="card-header bg-light">📤 Download the opening stock template and update it with the relevant data</small>
        <div class="card-body">
            <form method="POST" action="{{ route('openingstock.upload') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Upload Excel File (.xlsx)</label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx" required>
                    </div>

                    <div class="col-md-6 text-end">
                        <a href="{{ route('openingstock.sample') }}" class="btn btn-outline-primary">
                            ⬇️ Download Sample Template
                        </a>
                        <button type="submit" class="btn btn-primary ms-2">📤 Upload & Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
