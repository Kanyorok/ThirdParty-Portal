@extends('layouts.app')
@section('title', '📋 Budget Projections Overview')

@section('content')
    <div class="card p-3">
        <p class="muted">
            The table below displays forecasted financial values (projections) linked to products from the CBS.
            Each projection is based on specific drivers and rate types (e.g., interest or growth rates) and helps
            estimate
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
                <th>Currency</th>
                <th>Products</th>
                <th>No of Accounts</th>
                <th>Projected Value</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($projections as $projection)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $projection->budget ? $projection->budget->Name : '-' }}</td>
                    {{-- <td>Branch</td> --}}
                    {{-- <td>{{ $projection->scenario->scenarioName }}</td> --}}
                    <td>{{ $projection->currency ? $projection->currency->Code : '-' }}</td>
                    <td>{{ $projection->projections ? $projection->projections->count() : 0 }}</td>
                    {{-- <td>{{ $projection->period->fiscalYear }}</td> --}}
                    <td>{{ isset($projection->total_volume) ? number_format($projection->total_volume) : '0' }}</td>
                    <td>{{ isset($projection->total_value) ? number_format($projection->total_value, 2) : '0.00' }}</td>
                    {{-- <td><span class="badge bg-warning">Pending</span></td> --}}
                    <td>
                        <a href="{{route('budgetprojections.show',$projection->Id)}}"
                           class="btn btn-sm btn-outline-info">👁</a>
                        <form action="#" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Delete this entry?')">🗑
                            </button>
                        </form>
                        <a href="{{route('budgetprojections.edit',$projection->Id)}}"
                           class="btn btn-sm btn-outline-info">🖉 </a>
                        <a href="{{route('monthly.create', ['id'=>$projection->Id])}}"
                           class="btn btn-sm btn-outline-info">Monthly</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>
@endsection
