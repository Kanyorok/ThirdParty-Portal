@extends('layouts.app')
@section('title', 'Credit Note - Accounts Payable')
@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 id="noteTypeTitle">All Notes</h2>
        <a href="{{ route('creditnote.create') }}" class="btn btn-primary">Add Credit/Debit Note</a>
    </div>

    <div class="mb-3">
        <label class="form-label me-2">Filter By:</label>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary" onclick="filterNotes('All')">All</button>
            <button type="button" class="btn btn-outline-success" onclick="filterNotes('Credit')">Credit Notes</button>
            <button type="button" class="btn btn-outline-danger" onclick="filterNotes('Debit')">Debit Notes</button>
        </div>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Note Type</th>
                <th>Note Number</th>
                <th>Date</th>
                <th>Vendor</th>
                <th>Reference Invoice</th>
                <th>Amount (Ksh)</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            <!-- Sample Rows -->
            <tr class="note-row" data-type="Credit">
                <td>1</td>
                <td>Credit</td>
                <td>CN001</td>
                <td>2025-05-01</td>
                <td>XYZ Suppliers Ltd</td>
                <td>INV101</td>
                <td>10,000</td>
                <td>Overbilling adjustment</td>
            </tr>
            <tr class="note-row" data-type="Debit">
                <td>2</td>
                <td>Debit</td>
                <td>DN002</td>
                <td>2025-05-02</td>
                <td>ABC Traders</td>
                <td>INV202</td>
                <td>7,500</td>
                <td>Short delivery charge</td>
            </tr>
            <tr class="note-row" data-type="Credit">
                <td>3</td>
                <td>Credit</td>
                <td>CN003</td>
                <td>2025-05-03</td>
                <td>Quick Logistics</td>
                <td>INV303</td>
                <td>5,000</td>
                <td>Returned goods</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
