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
            <div class="col-md-12">
                <label for="scenario" class="form-label">Budget</label>
                <input class="for-control" value="{{ $budget->budget->Name }}" readonly>
            </div>

            {{-- <div class="col-md-6">
                <label for="currency" class="form-label">Currency</label>
                <div class="input-group">
                    <input class="form-control" value="{{ $budget->Currency->Code }}" readonly>
            </div> --}}
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
                        <th>Rate %</th>
                        <th>Value</th>
                        <th>BudgetLine Value</th>
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
                            <td>5</td>
                            <td>{{ number_format($projection->Value, 2) }}</td>
                            <td>500</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-4">
            <h5>📊 Monthly Budget Allocations</h5>
            <p class="text-muted">Summary of budget allocations for each month</p>

            @php
                $months = [
                    'Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6',
                    'Month 7', 'Month 8', 'Month 9', 'Month 10', 'Month 11', 'Month 12'
                ];
                // Map allocations by month for easy lookup
                $allocMap = $monthlyAllocations->keyBy('Month');
            @endphp

            @if($monthlyAllocations && $monthlyAllocations->count())
                <div class="alert alert-info">
                    <strong>Note:</strong> Monthly allocations you set for the current period.
                </div>
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Month</th>
                        <th>Allocation Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($months as $i => $month)
                    <tr>
                        <td>{{ $month }}</td>
                        <td>{{ isset($allocMap[$i+1]) ? number_format($allocMap[$i+1]->Allocation ?? $allocMap[$i+1]->Amount, 2) : '0.00' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-warning">
                    No monthly allocations found. Please add allocations to proceed.
                </div>
            @endif

        </div>


    </div>
    </form>
    </div>
@endsection
