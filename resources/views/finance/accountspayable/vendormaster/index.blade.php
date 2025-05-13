@extends('layouts.app')
@section('title', 'Vendor Master - Accounts Payable')
@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Vendor List - Accounts Payable</h2>

    <div class="mb-3 text-end">
        <a href="{{ route('vendormaster.create') }}"class="btn btn-primary">Add Vendor</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Vendor Name</th>
                <th>Contact Person</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Bank</th>
                <th>Account</th>
                <th>Payment Terms</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>ABC Supplies Ltd.</td>
                <td>Jane Mwangi</td>
                <td>+254712345678</td>
                <td>accounts@abc.com</td>
                <td>Equity Bank</td>
                <td>1234567890</td>
                <td>Net 30</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Global Parts Co.</td>
                <td>Peter Otieno</td>
                <td>+254701234567</td>
                <td>info@globalparts.co.ke</td>
                <td>KCB</td>
                <td>9876543210</td>
                <td>Due on Receipt</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Express Logistics Ltd.</td>
                <td>Ann Wambui</td>
                <td>+254799998888</td>
                <td>admin@expresslogistics.com</td>
                <td>Co-operative Bank</td>
                <td>1122334455</td>
                <td>Net 60</td>
            </tr>
        </tbody>
    </table>
</div>

@endsection
