@extends('layouts.app')
@section('title', 'Budget Entry Listing')
@section('content')
<div class="card mt-4">
       <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('entrybyglline.create') }}" class="btn btn-success">➕ Add Entry</a>

</div >
   <div class="card-header bg-dark text-white">📑 Budget Entries by Line</div>
    <div class="card-body">
        <!-- Filters -->
        <form class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Budget Period</label>
                <select class="form-select">
                    <option selected>FY2025-Q1</option>
                    <option>FY2025-Q2</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Budget Table -->
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Branch</th>
                    <th>Budget Line</th>
                    <th>Entry Method</th>
                    <th>Amount</th>
                    <th>Rate %</th>
                    <th>Source</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Main Branch</td>
                    <td>Salaries – Staff Costs</td>
                    <td>Manual</td>
                    <td>500,000</td>
                    <td>-</td>
                    <td>Manual Entry</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Main Branch</td>
                    <td>SME Loan Interest Income</td>
                    <td>Driver-Based</td>
                    <td>1,100,000</td>
                    <td>11.00%</td>
                    <td>KPI: Projected Loan</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Westlands Branch</td>
                    <td>Agri Loan Yield</td>
                    <td>Driver-Based</td>
                    <td>950,000</td>
                    <td>9.50%</td>
                    <td>KPI: Agri Product</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
