@extends('layouts.app')
@section('title', 'View Supplier Application')
@section('content')

    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            📄 Supplier Application - <strong>ABC Engineering Ltd</strong>
        </div>
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>📌 Basic Info</h5>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Supplier Name:</strong> ABC Engineering Ltd</li>
                        <li class="list-group-item"><strong>Trading Name:</strong> ABC Group</li>
                        <li class="list-group-item"><strong>Business Type:</strong> Company</li>
                        <li class="list-group-item"><strong>Registration No.:</strong> CPR/2020/12345</li>
                        <li class="list-group-item"><strong>Tax PIN:</strong> P0123456789Q</li>
                        <li class="list-group-item"><strong>Country:</strong> Kenya</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>📞 Contact Info</h5>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Phone:</strong> +254712345678</li>
                        <li class="list-group-item"><strong>Email:</strong> info@abceng.com</li>
                        <li class="list-group-item"><strong>Website:</strong> www.abceng.com</li>
                        <li class="list-group-item"><strong>Primary Contact:</strong> John Doe</li>
                        <li class="list-group-item"><strong>Designation:</strong> Procurement Officer</li>
                    </ul>
                </div>
            </div>

            <hr>

            <h5>🏦 Bank Details</h5>
            <table class="table table-bordered mb-4">
                <thead>
                <tr>
                    <th>Bank Name</th>
                    <th>Branch</th>
                    <th>Account No.</th>
                    <th>Currency</th>
                    <th>SWIFT</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Equity Bank</td>
                    <td>Westlands</td>
                    <td>1234567890</td>
                    <td>KES</td>
                    <td>EQBLKENA</td>
                </tr>
                </tbody>
            </table>

            <h5>📦 Applied Categories</h5>
            <ul class="list-group mb-4">
                <li class="list-group-item">Electrical Works</li>
                <li class="list-group-item">Power Backup Systems</li>
            </ul>

            <h5>📁 Submitted Documents</h5>
            <table class="table table-bordered mb-4">
                <thead>
                <tr>
                    <th>Document Type</th>
                    <th>File</th>
                    <th>Expiry Date</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Certificate of Incorporation</td>
                    <td><a href="#">Download</a></td>
                    <td>N/A</td>
                </tr>
                <tr>
                    <td>Tax Compliance</td>
                    <td><a href="#">Download</a></td>
                    <td>2026-05-15</td>
                </tr>
                </tbody>
            </table>

            <h5>📝 Application Metadata</h5>
            <ul class="list-group">
                <li class="list-group-item"><strong>Round:</strong> 2025 General Prequalification</li>
                <li class="list-group-item"><strong>Status:</strong> <span class="badge bg-warning text-dark">Pending Review</span>
                </li>
                <li class="list-group-item"><strong>Applied On:</strong> 2025-06-10</li>
                <li class="list-group-item"><strong>Submitted By:</strong> Supplier Portal User</li>
            </ul>

        </div>
    </div>

    <a href="{{ route('preqapplications.index') }}" class="btn btn-secondary">← Back to Applications</a>
    <a href="{{ route('preqevaluation.index') }}" class="btn btn-primary">🧮 Proceed to Evaluation</a>

@endsection
