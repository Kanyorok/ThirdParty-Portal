@extends('layouts.app')
@section('title', 'Award RFQ')
@section('content')

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            🏆 Award RFQ: RFQ/ADMIN/2025/022 – Supply of Office Chairs
        </div>
        <div class="card-body">
            <form>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Initiated By</label>
                        <input type="text" class="form-control" value="Procurement Officer – Admin Dept" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Deadline</label>
                        <input type="text" class="form-control" value="2025-06-22" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quotes Received</label>
                        <input type="text" class="form-control" value="4" readonly>
                    </div>
                </div>

                <h5 class="mb-3">📋 Compare Quotations</h5>
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Delivery</th>
                            <th>Terms</th>
                            <th>Responsive?</th>
                            <th>Select</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>OfficePro</td>
                            <td>KES 96,000</td>
                            <td>7 Days</td>
                            <td>30 Days</td>
                            <td><span class="badge bg-success">Yes</span></td>
                            <td><input type="radio" name="winner" checked></td>
                        </tr>
                        <tr>
                            <td>LowBid Traders</td>
                            <td>KES 88,000</td>
                            <td>30 Days</td>
                            <td>COD</td>
                            <td><span class="badge bg-danger">No</span></td>
                            <td><input type="radio" disabled></td>
                        </tr>
                    </tbody>
                </table>

                <div class="mb-3">
                    <label class="form-label">Award Justification</label>
                    <textarea class="form-control" rows="3">Lowest responsive bid with best terms.</textarea>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="notify" checked>
                    <label class="form-check-label" for="notify">
                        Notify Unsuccessful Suppliers
                    </label>
                </div>

                <div class="text-end">
                    <button class="btn btn-success">✅ Confirm Award</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
