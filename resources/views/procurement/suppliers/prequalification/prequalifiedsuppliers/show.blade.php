@extends('layouts.app')
@section('title', 'View Prequalified Supplier')

@section('content')
    <div class="card">
        <div class="card-header bg-secondary text-white">👤 Supplier: TechLink Solutions Ltd.</div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6"><strong>Period:</strong> 2025 ICT Suppliers</div>
                <div class="col-md-6"><strong>Category:</strong> ICT Services</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><strong>Score:</strong> 88%</div>
                <div class="col-md-6"><strong>Approval Date:</strong> 2025-06-08</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><strong>Status:</strong>
                    <span class="badge bg-success">Prequalified</span>
                </div>
                <div class="col-md-6">
                    <form>
                        <label>Change Status:</label>
                        <select class="form-select" name="status">
                            <option>Prequalified</option>
                            <option>Suspended</option>
                            <option>Blacklisted</option>
                        </select>
                        <button class="btn btn-sm btn-warning mt-2">Update</button>
                    </form>
                </div>
            </div>

            <hr>

            <h5>📎 Submitted Documents</h5>
            <ul>
                <li><a href="#">Certificate of Incorporation.pdf</a></li>
                <li><a href="#">Tax Compliance.pdf</a></li>
            </ul>

            <hr>

            <div class="d-flex justify-content-end">
                <button class="btn btn-danger">Disqualify Supplier</button>
                <button class="btn btn-outline-secondary ms-2">Print Summary</button>
            </div>
        </div>
    </div>
@endsection
