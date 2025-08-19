@extends('layouts.app')
@section('title', 'Commissions Earned')

@section('content')
<div class="container mt-4">
    <h4>💼 Commissions Earned</h4>

    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-3">
            <label>Month</label>
            <select name="month" class="form-select">
                <option value="">-- Select Month --</option>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label>Year</label>
            <select name="year" class="form-select">
                @for ($y = now()->year; $y >= 2022; $y--)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">🔍 Filter</button>
        </div>
    </form>

    @if($earneds->isEmpty())
        <p class="text-muted">No earned commissions found for the selected period.</p>
    @else

            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">

            <div class="mb-2 text-end">
                <button class="btn btn-success">💰 Pay All Filtered</button>
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Policy</th>
                        <th>Earned By</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($earneds as $e)
                        <tr>
                            <td>{{ $e->Id }}</td>
                            <td>{{ $e->PolicyNumber }}</td>
                            <td>{{ $e->EarnedByType }} #{{ $e->EarnedById }}</td>
                            <td>{{ number_format($e->EarnedAmount, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($e->EarnedDate)->format('d M Y') }}</td>
                            <td>{{ $e->Status ? 'Paid' : 'Unpaid' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
       
    @endif
</div>
@endsection
