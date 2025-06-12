@extends('layouts.app')
@section('title', 'Budget Entries by Product')

@section('content')
<div class="card p-3">
    <h5>📋 Budget Entries by Product</h5>

    <div class="mb-3 d-flex justify-content-between">
        <a href="{{ route('entrybyproduct.create') }}" class="btn btn-success">➕ Add Entries</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Scenario</th>
                    <th>Currency</th>
                    <th>Product</th>
                    <th>Period</th>
                    <th>Volume</th>
                    <th>Projected Value (KES)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Base Case</td>
                    <td>KES</td>
                    <td>Product A</td>
                    <td>2025</td>
                    <td>1,000</td>
                    <td>250,000.00</td>
                    <td><span class="badge bg-warning">Pending</span></td>
                    <td>
                        <a href="{{route('entrybyproduct.show',1)}}" class="btn btn-sm btn-outline-info">👁</a>
                        <form action="#" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this entry?')">🗑</button>
                        </form>

                        <a href="{{route('entrybyproduct.edit',1)}}" class="btn btn-sm btn-outline-info">🖉 </a>
                        <a href="{{route('monthly.create',1)}}" class="btn btn-sm btn-outline-info">Monthly</a> 
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Optimistic</td>
                    <td>USD</td>
                    <td>Product B</td>
                    <td>2026</td>
                    <td>2,500</td>
                    <td>1,200,000.00</td>
                    <td><span class="badge bg-success">Approved</span></td>
                    <td>
                        <a href="{{route('entrybyproduct.show',2)}}" class="btn btn-sm btn-outline-info">👁</a>
                        <form action="#" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this entry?')">🗑</button>
                        </form>
                        <a href="{{route('entrybyproduct.edit',1)}}" class="btn btn-sm btn-outline-info">🖉 </a>
                        <a href="{{route('monthly.create',1)}}" class="btn btn-sm btn-outline-info">Monthly</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
