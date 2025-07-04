@extends('layouts.app')
@section('content')
<div class="container-fluid" style="margin-top:-50px">
    <h4 class="mb-0">📊 Statement of Financial Position</h4>
    @if ($isSet)
        <h4>{{ $budgetName }} for {{ $period }}</h4>
    @endif

    <!-- Budget Selection Form -->
    <form action="{{ route('budgetconsolidation.index') }}" method="POST">
        @csrf
        @method('GET')
        <div class="row mb-4">
            <div class="col-md-12">
                @if ($isSet)
                    {{-- <label class="form-label fw-semibold">{{ $budgetName }}</label> --}}
                @else
                    <label class="form-label fw-semibold">Select a Budget</label>
                @endif
                <select name="BudgetLineID" class="form-select" required onchange="this.form.submit()">
                    @if ($isSet)
                        <option disabled value="">-- Select Budget --</option>
                        @foreach ($budgets as $item)
                            <option value="{{ $item->Id }}" {{ $budgetId==$item->Id?'slected':'' }}>{{ $item->Name }}</option>
                        @endforeach
                    @else
                        <option disabled selected>-- Select Budget --</option>
                        @foreach ($budgets as $item)
                            <option value="{{ $item->Id }}">{{ $item->Name }}</option>
                        @endforeach
                    @endif
                </select>
                @error('BudgetLineID') 
                    <small class="text-danger">{{ $message }}</small> 
                @enderror
            </div>
        </div>
    </form>

    @php $months = range(1, 12);$grandTotal = 0; @endphp
    @foreach($data as $category => $subTypes)
        @php $categoryTotal = 0; @endphp
        <div class="card mb-5 shadow border-0">
            <div class="card-header bg-primary text-white fw-bold fs-5">{{ strtoupper($category) }}</div>
            <div class="card-body p-0 overflow-auto">
                @php $categoryTotal = 0; @endphp
                @foreach($subTypes as $subTypeName => $entries)
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-center mb-0" style="min-width: 1800px;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start ps-3">{{ $subTypeName }}</th>
                                    <th class="bg-warning-subtle">Prev. Year</th>
                                    @foreach($months as $m)
                                        <th>Month {{ $m }}</th>
                                    @endforeach
                                    <th class="bg-success-subtle">Total</th>
                                    <th class="bg-info-subtle">Actuals</th>
                                    <th class="bg-danger-subtle">% Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $subTotal = 0; @endphp
                                @foreach($entries as $entry)
                                @php
                                    $total = $entry['allocationType'] === 'monthly'
                                        ? array_sum(array_map('floatval', $entry['allocationValues']))
                                        : floatval($entry['fullAllocation']);
                                    $subTotal += $total;
                                    $categoryTotal += $total;

                                    $prev = floatval($entry['prevYear'] ?? 0);
                                    $actual = floatval($entry['actuals'] ?? 0);
                                    $percentChange = $prev > 0 ? (($total - $prev) / $prev) * 100 : 0;
                                @endphp
                                <tr>
                                    <td class="text-start ps-3">{{ $entry['budgetLineName'] }}</td>
                                    <td class="bg-warning-subtle">{{ number_format($prev, 2) }}</td>
                                    @foreach($months as $m)
                                        <td>
                                            @if ($entry['allocationType'] === 'monthly')
                                                {{ isset($entry['allocationValues'][$m]) && $entry['allocationValues'][$m] != '.00'
                                                    ? number_format($entry['allocationValues'][$m], 2)
                                                    : '-' }}
                                            @else
                                                {{ $m == 12 ? number_format($entry['fullAllocation'], 2) : '-' }}
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="bg-success-subtle fw-bold">{{ number_format($total, 2) }}</td>
                                    <td class="bg-info-subtle">{{ number_format($actual, 2) }}</td>
                                    <td class="bg-danger-subtle">{{ number_format($percentChange, 2) }}%</td>
                                </tr>
                                @endforeach
                                <tr class="table-secondary fw-semibold">
                                    <td class="text-start ps-3">Subtotal – {{ $subTypeName }}</td>
                                    <td></td>
                                    @foreach($months as $m) <td></td> @endforeach
                                    <td class="text-end pe-2">{{ number_format($subTotal, 2) }}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach

                <div class="text-end mt-3 mb-3 pe-3 fw-bold text-primary">
                    Total for {{ $category }}: {{ number_format($categoryTotal, 2) }}
                </div>
            </div>
        </div>
        @php $grandTotal += $categoryTotal; @endphp
    @endforeach
    <div class="text-end fs-5 fw-bold text-success me-3 mb-4">
        💰 Total Budget Cost: {{ number_format($grandTotal, 2) }}
    </div>

    <div class="text-end mb-5">
        {{-- <a href="{{ route('budgetconsolidation.export', ['format' => 'excel']) }}" class="btn btn-success me-2">📥 Export to Excel</a>
        <a href="{{ route('budgetconsolidation.export', ['format' => 'pdf']) }}" class="btn btn-danger">📄 Export to PDF</a> --}}
    </div>
</div>
@endsection
