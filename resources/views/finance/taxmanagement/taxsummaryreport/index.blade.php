@extends('layouts.app')
@section('title', 'Tax Summary Report')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">📊 Tax Summary Report</h4>

        <!-- Filters -->
        <form class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Tax Type</label>
                <select class="form-select">
                    <option selected>All</option>
                    <option>VAT</option>
                    <option>WHT</option>
                    <option>GST</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <input type="month" class="form-control" value="{{ date('Y-m') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label d-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary">Generate</button>
            </div>
        </form>

        <!-- Results -->
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>Tax Type</th>
                <th>Jurisdiction</th>
                <th>Taxable Amount</th>
                <th>Tax Amount</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>VAT</td>
                <td>Kenya</td>
                <td>1,250,000.00</td>
                <td>200,000.00</td>
                <td><span class="badge bg-success">Pending Filing</span></td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
