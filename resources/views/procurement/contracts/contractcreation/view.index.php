@extends('layouts.app')
@section('title', 'View Contract')

@section('content')
<div class="container mt-4">
    <h4>📄 View Contract</h4>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Award Reference</label>
            <input type="text" class="form-control" value="AWRD/2025/004 – OfficePro Suppliers" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label">Contract Reference</label>
            <input type="text" class="form-control" value="CONTRACT/PROC/2025/009" readonly>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Contract Title</label>
        <input type="text" class="form-control" value="Supply of Office Furniture" readonly>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" value="2025-07-01" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" value="2025-12-31" readonly>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Uploaded Contract (PDF)</label>
        <input type="text" class="form-control" value="contract_office_furniture.pdf" readonly>
    </div>

    <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea class="form-control" rows="3"
                  readonly>Standard 6-month delivery agreement with phased supply.</textarea>
    </div>

    <!-- 📦 Breakdown -->
    <h5 class="mt-4">📦 Contract Items Breakdown</h5>
    <table class="table table-bordered">
        <thead class="table-light">
        <tr>
            <th>Item Description</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
            <th>Delivery Timeline</th>
            <th>Milestone</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Office Desk – Executive</td>
            <td>50</td>
            <td>15,000</td>
            <td>750,000</td>
            <td>Within 30 days</td>
            <td>Phase 1 Delivery</td>
        </tr>
        </tbody>
    </table>
</div>
@endsection
