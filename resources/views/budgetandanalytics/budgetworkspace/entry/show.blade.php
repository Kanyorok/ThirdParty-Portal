@extends('layouts.app')
@section('title', 'Budget Entry')
 
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
    <h5>Projections Overview</h5>
    <p class="text-muted">The overview of the generated Branch Projections.</p>
 
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="scenario" class="form-label">Budget</label>
                    <input class="form-control" value="{{ $budget->budget->Name }}" readonly>
            </div>
 
            <div class="col-md-6">
                <label for="currency" class="form-label">Currency</label>
                <div class="input-group">
                    <input class="form-control" value="{{ $budget->Currency->Code }}" readonly>
            </div>
        </div>
{{--
         <div class="mt-3">
                <label for="period" class="form-label">Period</label>
                <input type="text" name="Period" id="period" class="form-control" value="   2025" readonly>
            </div> --}}
 
        <div class="mt-4">
        <h5>Detailed Projections</h5>
        <p class="text-muted">Breakdown of projections by product.</p>
 
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Volume</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budget->projections as $projection)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $projection->productType->Name }}
                            </td>
                            <td>{{ $projection->Volume }}</td>
                            <td>{{ number_format($projection->Value, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
 
    <div class="card p-4">
    <h5>📊 Monthly Budget Allocations</h5>
    <p class="text-muted">Summary of budget allocations for each month</p>
 
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
 
    @php
        $months = [
            'Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6',
            'Month 7', 'Month 8', 'Month 9', 'Month 10', 'Month 11', 'Month 12'
        ];
        // Group allocations by BudgetProjectionID
        $grouped = $monthlyAllocations->groupBy('BudgetProjectionID');
    @endphp
 
    @if($grouped->count())
        <div class="alert alert-info">
            <strong>Note:</strong> Monthly allocations are set for the current period.
            You can update them as needed.
        </div>
        <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    @foreach ($months as $month)
                        <th>{{ $month }}</th>
                    @endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grouped as $projectionId => $allocations)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        @php $total = 0; @endphp
                        @foreach ($months as $month)
                            @php
                                $alloc = $allocations->firstWhere('Month', $month);
                                $amount = $alloc ? $alloc->Allocation : 0;
                                $total += $amount;
                            @endphp
                            <td>{{ number_format($amount, 2) }}</td>
                        @endforeach
                        <td><strong>{{ number_format($total, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @else
        <div class="alert alert-warning">
            No monthly allocations found. Please add allocations to proceed.
        </div>
    @endif
 
    <div class="mt-4 text-end">
        <a href="{{ route('budgetprojections.create') }}" class="btn btn-success">
            ➕ Add New Allocation
        </a>
    </div>
</div>
 
 
    </div>
   </form>
</div>
@endsection