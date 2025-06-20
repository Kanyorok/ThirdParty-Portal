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

        <form action="{{ route('entrybyproduct.store') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="scenario" class="form-label">Scenario</label>
                    <input type="text" name="Scenario" id="scenario" class="form-control" value="Base Case" readonly>
                </div>

                <div class="col-md-6">
                    <label for="currency" class="form-label">Currency</label>
                    <input type="text" name="Currency" id="currency" class="form-control" value="KES" readonly>
                </div>
            </div>

            <div class="mt-3">
                <label for="period" class="form-label">Period</label>
                <input type="text" name="Period" id="period" class="form-control" value="	2025" readonly>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Volume</th>
                        <th>Projected Value (KES)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>1</td>
                        <td>Product A</td>
                        <td>1,000</td>
                        <td>250,000.00</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Product B</td>
                        <td>2,500</td>
                        <td>1,200,000.00</td>
                        <td><span class="badge bg-success">Approved</span></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div>
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
            </div>

        </form>
    </div>
@endsection
