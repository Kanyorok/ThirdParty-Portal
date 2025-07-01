@extends('layouts.app')
@section('title', 'Monthly Projection Allocations')
@section('content')
@if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
@if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card p-4">
    <h5>Monthly Projection Allocations</h5>
    <p class="text-muted">Overview of monthly allocations for the selected projection.</p>
    @if(isset($projectionID))
        <div class="mb-3">
            <label class="form-label">Projection ID</label>
            <input class="form-control" value="{{ $projectionID }}" readonly>
        </div>
    @endif
    @php
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
    @endphp
    @if($monthlyAllocations && $monthlyAllocations->count())
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Month</th>
                    <th>Allocation Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($monthlyAllocations as $alloc)
                    <tr>
                        <td>{{ $months[$alloc->Month - 1] ?? 'Month ' . $alloc->Month }}</td>
                        <td>{{ number_format($alloc->Allocation, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-warning">
            No monthly allocations found for this projection.
        </div>
    @endif
</div>
@endsection
