@extends('layouts.app')
@section('title', 'Vouchers')
@section('content')

<div class="container py-5">
        <div class="text-center mb-4">
            <h2>Voucher Management</h2>
            <p class="text-muted">Manage payment and receipt vouchers efficiently</p>
        </div>

        <div class="d-flex justify-content-end mb-3 gap-2">
            <a href="{{route('paymentandreceiptvouchers.create')}} " class="btn btn-success">+ New Payment Voucher</a>
            <a href="{{route('paymentandreceiptvouchers.create')}} " class="btn btn-primary">+ New Receipt Voucher</a>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <strong>Voucher Records</strong>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Payee/Payer</th>
                                    <th>Amount (KSh)</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>2025-05-01</td>
                                    <td>Payment</td>
                                    <td>Jane Njeri</td>
                                    <td>18,000</td>
                                    <td>Office rent April</td>
                                    <td><span class="badge bg-success">Approved</span></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>2025-05-04</td>
                                    <td>Receipt</td>
                                    <td>JK Holdings</td>
                                    <td>45,000</td>
                                    <td>Project deposit</td>
                                    <td><span class="badge bg-info text-dark">Received</span></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>2025-05-06</td>
                                    <td>Payment</td>
                                    <td>Mary Atieno</td>
                                    <td>22,500</td>
                                    <td>Consultancy services</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection