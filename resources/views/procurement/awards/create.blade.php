@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                🏆 Award RFQ: RFQ/ADMIN/2025/022 – Supply of Office Chairs
            </div>
            <div class="card-body">
                <form>
                    <!-- Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Initiated By</label>
                            <input type="text" class="form-control" value="Procurement Officer – Admin Dept" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Submission Deadline</label>
                            <input type="text" class="form-control" value="2025-06-22" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Quotes Received</label>
                            <input type="text" class="form-control" value="4" readonly>
                        </div>
                    </div>

                    <!-- Quote Comparison Table -->
                    <h5 class="mb-3">📋 Quotation Summary</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light text-center">
                            <tr>
                                <th>#</th>
                                <th>Supplier</th>
                                <th>Quoted Amount</th>
                                <th>Delivery Time</th>
                                <th>Payment Terms</th>
                                <th>Responsive?</th>
                                <th>Select</th>
                            </tr>

                            </thead>
                            <tbody>
                            <tr>
                                <td>1</td>
                                <td>OfficePro Suppliers</td>
                                <td>KES 96,000</td>
                                <td>7 Days</td>
                                <td>30 Days</td>
                                <td><span class="badge bg-success">Yes</span></td>
                                <td class="text-center">
                                    <input type="radio" name="winning_bidder" value="OfficePro">
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Chairs & Co. Ltd</td>
                                <td>KES 100,000</td>
                                <td>14 Days</td>
                                <td>60 Days</td>
                                <td><span class="badge bg-success">Yes</span></td>
                                <td class="text-center">
                                    <input type="radio" name="winning_bidder" value="ChairsCo">
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>LowBid Traders</td>
                                <td>KES 88,000</td>
                                <td>30 Days</td>
                                <td>Cash on Delivery</td>
                                <td><span class="badge bg-danger">No</span></td>
                                <td class="text-center">
                                    <input type="radio" disabled title="Non-responsive">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Justification & Notifications -->
                    <div class="mb-3">
                        <label class="form-label">Award Justification</label>
                        <textarea class="form-control" rows="3"
                                  placeholder="Lowest responsive bid with acceptable delivery and payment terms."></textarea>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="notifyUnsuccessful">
                        <label class="form-check-label" for="notifyUnsuccessful">
                            Send regret letter to unsuccessful suppliers
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-success">
                            ✅ Confirm Award and Proceed to LPO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
