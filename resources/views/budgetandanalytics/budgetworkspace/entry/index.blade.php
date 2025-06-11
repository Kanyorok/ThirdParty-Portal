@extends('layouts.app')
@section('title', 'Budget Entries by Product')

@section('content')
<div class="card p-3">
    <h5>📋 Budget Entries by Product</h5>

    <div class="mb-3 d-flex justify-content-between">
        <a href="{{ route('entrybyproduct.create') }}" class="btn btn-success">➕ Add Entries</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Scenario</th>
                    <th>Product</th>
                    <th>Period</th>
                    <th>Volume</th>
                    <th>Projected Value (KES)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($projections->count())
                    @foreach($projections as $index => $projection)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $projection->scenario->scenarioName ?? '—' }}</td>
                            <td>{{ $projection->product->Name ?? '—' }}</td>
                            <td>{{ $projection->period->fiscalYear ?? '—' }}</td>
                            <td>{{ number_format($projection->Volume) }}</td>
                            <td>{{ number_format($projection->Value, 2) }}</td>
                            <td><span class="badge bg-warning">Pending</span></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-info">👁 View</a>
                                <form action="#" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this entry?')">🗑 Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="text-center text-muted">No budget entries found.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
