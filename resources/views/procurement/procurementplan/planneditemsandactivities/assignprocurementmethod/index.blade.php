@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')

<div class="container mt-4">
    <h4 class="mb-4">⚙️ Assign Procurement Method</h4>

    <!-- Plan Selection -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Select Approved Plan</label>
                    <select class="form-select">
                        <option selected disabled>-- Choose Plan --</option>
                        <option value="1">PLAN/ICT/2025/001 – Nairobi HQ – ICT</option>
                        <option value="2">PLAN/FIN/2025/002 – Mombasa – Finance</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary w-100">🔄 Load Items</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Planned Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Est. Cost</th>
                            <th>Suggested Method</th>
                            <th>Assign Method</th>
                            <th>Justification (if override)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample Row -->
                        <tr>
                            <td>Desktop Computers</td>
                            <td>12</td>
                            <td>KES 720,000</td>
                            <td><span class="badge bg-secondary">Tender</span></td>
                            <td>
                                <select class="form-select">
                                    <option>Tender</option>
                                    <option>RFQ</option>
                                    <option>Direct</option>
                                    <option>Framework</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control" placeholder="Only if changing from suggestion">
                            </td>
                        </tr>
                        <tr>
                            <td>Printer Ink</td>
                            <td>20</td>
                            <td>KES 100,000</td>
                            <td><span class="badge bg-info">RFQ</span></td>
                            <td>
                                <select class="form-select">
                                    <option>RFQ</option>
                                    <option>Direct</option>
                                    <option>Tender</option>
                                    <option>Framework</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control">
                            </td>
                        </tr>
                        <!-- More rows -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="text-end">
        <button class="btn btn-primary">💾 Save Assigned Methods</button>
        <button class="btn btn-outline-secondary">Cancel</button>
    </div>
</div>

@endsection