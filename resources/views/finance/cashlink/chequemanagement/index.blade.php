@extends('layouts.app')
@section('title', 'Cheque Management')
@section('content')

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Cheque Management</h2>
        <a href="{{route('chequemanagement.create')}} " class="btn btn-outline-primary">+ New Cheque Entry</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Payee</th>
                    <th>Amount (KSh)</th>
                    <th>Bank</th>
                    <th>Cheque No.</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>2025-04-25</td>
                    <td>ABC Supplies Ltd.</td>
                    <td>50,000</td>
                    <td>Equity Bank</td>
                    <td>CHQ001245</td>
                    <td><span class="badge bg-success">Cleared</span></td>
                    <td>Payment for office furniture</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>2025-04-28</td>
                    <td>John Mwangi</td>
                    <td>12,000</td>
                    <td>KCB Bank</td>
                    <td>CHQ001256</td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                    <td>Travel reimbursement</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>2025-05-03</td>
                    <td>Nairobi Printers</td>
                    <td>25,500</td>
                    <td>Co-op Bank</td>
                    <td>CHQ001267</td>
                    <td><span class="badge bg-danger">Bounced</span></td>
                    <td>Invoice #7890</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


@endsection