@extends('layouts.app')

@section('title', 'Monthly Budget Allocations')

@section('content')
<div class="card p-4">
    <h5>📊 Monthly Budget Allocations</h5>
    <p class="text-muted">Summary of budget allocations for each month</p>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>January</th>
                <th>February</th>
                <th>March</th>
                <th>April</th>
                <th>May</th>
                <th>June</th>
                <th>July</th>
                <th>August</th>
                <th>September</th>
                <th>October</th>
                <th>November</th>
                <th>December</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>12000</td>
                <td>15000</td>
                <td>13000</td>
                <td>12500</td>
                <td>14000</td>
                <td>16000</td>
                <td>15500</td>
                <td>14500</td>
                <td>15000</td>
                <td>13500</td>
                <td>14800</td>
                <td>16000</td>
                <td><strong>181800</strong></td>
            </tr>
            <tr>
                <td>2</td>
                <td>25000</td>
                <td>26000</td>
                <td>27000</td>
                <td>28000</td>
                <td>29000</td>
                <td>30000</td>
                <td>31000</td>
                <td>32000</td>
                <td>33000</td>
                <td>34000</td>
                <td>35000</td>
                <td>36000</td>
                <td><strong>386000</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="mt-4 text-end">
        <a href="{{ route('entrybyproduct.create') }}" class="btn btn-success">
            ➕ Add New Allocation
        </a>
    </div>
</div>
@endsection
