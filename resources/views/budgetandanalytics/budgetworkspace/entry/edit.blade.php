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

        <form>

            <!-- Scenario & Currency -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="scenario" class="form-label">Scenario</label>
                    <input type="text" id="scenario" class="form-control" value="Base Case">
                </div>
                <div class="col-md-6">
                    <label for="currency" class="form-label">Currency</label>
                    <input type="text" id="currency" class="form-control" value="KES">
                </div>
            </div>

            <!-- Period -->
            <div class="mb-4">
                <label for="period" class="form-label">Period</label>
                <input type="text" id="period" class="form-control" value="2025">
            </div>

            <!-- Product Table -->
            <div class="table-responsive mb-4">
                <div class="mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm">➕ Add Product Row</button>
                </div>
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Volume</th>
                        <th>Projected Value (KES)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>1</td>
                        <td><input type="text" class="form-control" value="Product A"></td>
                        <td><input type="text" class="form-control" value="100"></td>
                        <td><input type="text" class="form-control" value="1,000,000.00"></td>
                        <td><span class="badge bg-warning">Pending</span></td>
                        <td>
                            <button type="button" class="btn btn-outline-danger btn-sm">🗑</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><input type="text" class="form-control" value="Product B"></td>
                        <td><input type="text" class="form-control" value="2500"></td>
                        <td><input type="text" class="form-control" value="1,200,000.00"></td>
                        <td><span class="badge bg-success">Approved</span></td>
                        <td>
                            <button type="button" class="btn btn-outline-danger btn-sm">🗑</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Monthly Allocations (Vertical Layout) -->
            <!--   <div class="mb-4">
               <table class="table table-bordered table-striped">
                   <thead class="table-light">
                   <tr>
                       <th>Month</th>
                       <th>Product A</th>
                       <th>Product B</th>
                       <th>Total</th>
                   </tr>
                   </thead>
                   <tbody>
                   <tr>
                       <td>January</td>
                       <td><input type="text" class="form-control" value="12000"></td>
                       <td><input type="text" class="form-control" value="15000"></td>
                       <td><strong>27000</strong></td>
                   </tr>
                   <tr>
                       <td>February</td>
                       <td><input type="text" class="form-control" value="13000"></td>
                       <td><input type="text" class="form-control" value="12500"></td>
                       <td><strong>25500</strong></td>
                   </tr>
                   <tr>
                       <td>March</td>
                       <td><input type="text" class="form-control" value="16000"></td>
                       <td><input type="text" class="form-control" value="14500"></td>
                       <td><strong>30500</strong></td>
                   </tr>
                    Add more rows as needed
                   </tbody>
               </table>
               </div>-->
            <div class="text-end mt-4">
                <button type="submit" class="btn btn-success">
                    💾 Save Projections
                </button>
            </div>


        </form>
    </div>
@endsection
