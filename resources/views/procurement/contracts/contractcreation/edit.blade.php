@extends('layouts.app')
@section('title', 'Create Contract')

@section('content')
    <div class="container mt-4">
        <h4>➕ New Contract</h4>

        <form>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Award Reference</label>
                    <select class="form-select">
                        <option>AWRD/2025/004 – OfficePro Suppliers</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contract Reference</label>
                    <input type="text" class="form-control" value="CONTRACT/PROC/2025/009">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Contract Title</label>
                <input type="text" class="form-control" value="Supply of Office Furniture">
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" value="2025-07-01">
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" value="2025-12-31">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload Contract (PDF)</label>
                <input type="file" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea class="form-control"
                          rows="3">Standard 6-month delivery agreement with phased supply.</textarea>
            </div>

            <!-- 🧩 Contract Items Breakdown -->
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
                    <td><input type="text" class="form-control" value="Office Desk – Executive"></td>
                    <td><input type="number" class="form-control" value="50"></td>
                    <td><input type="number" class="form-control" value="15000"></td>
                    <td><input type="text" class="form-control" value="750000" readonly></td>
                    <td><input type="text" class="form-control" value="Within 30 days"></td>
                    <td><input type="text" class="form-control" value="Phase 1 Delivery"></td>
                </tr>
                <!-- more rows -->
                </tbody>
            </table>

            <div class="text-end mt-2">
                <button type="button" class="btn btn-outline-secondary btn-sm">➕ Add Item</button>
            </div>

            <div class="mt-4 text-end">
                <button class="btn btn-primary">💾 Save Contract</button>
            </div>
        </form>
    </div>
@endsection
