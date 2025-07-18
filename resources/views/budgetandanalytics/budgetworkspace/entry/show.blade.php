@extends('layouts.app')
@section('title', 'Budget Projection Products')

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
{{--        <h5>Projections Overview</h5>--}}
        <p class="text-muted">This form gives a complete overview of the products with their rates and allocations.</p>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="scenario" class="form-label">Budget Name: </label>
                <b>{{$budget->budget->Name}}</b>
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
{{--            <h5>Detailed Projections</h5>--}}
{{--            <p class="text-muted">Breakdown of projections by product.</p>--}}

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
                        <th>Allocations</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $projection)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $projection['Name'] }}
                            </td>
                            <td>{{ $projection['Volume'] }}</td>
                            <td>{{ $projection['Rate'] }}</td>
                            <td>{{ number_format($projection['Value'], 2) }}</td>
                            <td>{{ number_format(($projection['Value'] * $projection['Rate']) / 100, 2) }}</td>
                            <td>
                                @if($projection['AllocationType'] === 'monthly')
                                    <!-- Button trigger modal -->
                                    <button type="button" class="btn btn-sm btn-outline-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#monthlyModal{{ $loop->index }}">
                                        View Monthly
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="monthlyModal{{ $loop->index }}" tabindex="-1"
                                         aria-labelledby="monthlyModalLabel{{ $loop->index }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Monthly Allocations for {{ $projection['Name'] }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-striped table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <th>Month</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($projection['allocations'] as $monthData)
                                                            <tr>
                                                                <td>Month {{ $monthData->Month }}</td>
                                                                <td>{{ number_format($monthData->Amount, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{ number_format($projection['Value'], 2) }}
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('budgetprojections.edit', $projection['Id']) }}" class="btn btn-sm btn-info">
                                    ✏️ Edit
                                </a>

                                <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{ $projection['Name'] }}"    {{-- Pass item name --}}
                                        data-route="{{ route('budgetprojections.deleteProjection', $projection['Id']) }}"> {{-- Pass delete route --}}
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

{{--        <div class="card p-4">--}}
{{--            <h5>📊 Monthly Projection Allocations</h5>--}}
{{--            <p class="text-muted">Summary of budget allocations for each month</p>--}}

{{--            @php--}}
{{--                $months = [--}}
{{--                    'Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6',--}}
{{--                    'Month 7', 'Month 8', 'Month 9', 'Month 10', 'Month 11', 'Month 12'--}}
{{--                ];--}}
{{--                // Map allocations by month for easy lookup--}}
{{--                $allocMap = $monthlyAllocations->keyBy('Month');--}}
{{--            @endphp--}}

{{--            @if($monthlyAllocations && $monthlyAllocations->count())--}}
{{--                <div class="alert alert-info">--}}
{{--                    <strong>Note:</strong> Monthly allocations you set for the current period.--}}
{{--                </div>--}}
{{--                <table class="table table-bordered table-hover align-middle">--}}
{{--                    <thead class="table-light">--}}
{{--                    <tr>--}}
{{--                        <th>Month</th>--}}
{{--                        <th>Allocation Amount</th>--}}
{{--                    </tr>--}}
{{--                    </thead>--}}
{{--                    <tbody>--}}
{{--                    @foreach ($months as $i => $month)--}}
{{--                    <tr>--}}
{{--                        <td>{{ $month }}</td>--}}
{{--                        <td>{{ isset($allocMap[$i+1]) ? number_format($allocMap[$i+1]->Allocation ?? $allocMap[$i+1]->Amount, 2) : '0.00' }}</td>--}}
{{--                    </tr>--}}
{{--                    @endforeach--}}
{{--                    </tbody>--}}
{{--                </table>--}}
{{--            @else--}}
{{--                <div class="alert alert-warning">--}}
{{--                    No monthly allocations found. Please add allocations to proceed.--}}
{{--                </div>--}}
{{--            @endif--}}

{{--        </div>--}}



    </div>
    </form>
    </div>


    @include('components.modals.delete-confirm')
@endsection
