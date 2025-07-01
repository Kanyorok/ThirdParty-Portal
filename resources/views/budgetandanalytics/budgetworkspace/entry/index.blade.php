@extends('layouts.app')
@section('title', '📋 Budget Projections Overview')
 
@section('content')
<div class="card p-3">
    <p class="muted">
        The table below displays forecasted financial values (projections) linked to products from the CBS.
        Each projection is based on specific drivers and rate types (e.g., interest or growth rates) and helps estimate
        expected income or expenses over a given period.
    </p>
    {{-- <h5>📋 Budget Projections</h5> --}}
 
    <div class="mb-3 d-flex justify-content-between">
        <a href="{{ route('budgetprojections.create') }}" class="btn btn-success">➕ Add Projection</a>
    </div>
 
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Budget</th>
                    {{-- <th>Currency</th> --}}
                    <th>Products</th>
                    <th>No of Accounts</th>
                    {{-- <th>Projected Value</th> --}}
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupedProjections as $budgetId => $projection)
                @php
                    $item = $projection->first();
                    $total_volume = $projection->flatMap->projections->sum('Volume');
                    $total_value = $projection->flatMap->projections->sum('Value');
                    $total_products = $projection->flatMap->projections->count();
                @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->budget->Name  ?? $item->BudgetID }}</td>
                        {{-- <td>Branch</td> --}}
                        {{-- <td>{{ $projection->scenario->scenarioName }}</td> --}}
                        {{-- <td>{{ $item->currency->Code ?? $item->BudgetID}}</td> --}}
                        <td>{{ $projection->flatMap->projections->count() }}</td>
                        {{-- <td>{{ $projection->period->fiscalYear }}</td> --}}
                        <td>{{ isset($item->total_volume) ? number_format($item->total_volume) : '0' }}</td>
                        {{-- <td>{{ isset($item->total_value) ? number_format($item->total_value, 2) : '0.00' }}</td> --}}
                        {{-- <td><span class="badge bg-warning">Pending</span></td> --}}
                        <td>
                            <a href="{{route('budgetprojections.show',$item->Id)}}" class="btn btn-sm btn-outline-info">👁</a>
                            <form action="{{ route('budgetprojections.destroy', $item->Id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this entry?')">🗑</button>
                            </form>
                            <a href="{{route('budgetprojections.edit',$item->Id)}}" class="btn btn-sm btn-outline-info">🖉 </a>
                            <a href="{{route('monthly.create', ['id'=>$item->Id])}}" class="btn btn-sm btn-outline-info">Monthly</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
