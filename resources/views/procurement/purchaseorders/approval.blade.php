@extends('layouts.app')
@section('title', 'Approve LPO')
@section('content')

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            ✅ Approve LPO – LPO/2025/032
        </div>
        <div class="card-body">
            <form>
                <p><strong>Supplier:</strong> OfficePro Suppliers</p>
                <p><strong>Amount:</strong> KES 96,000</p>
                <p><strong>Delivery Location:</strong> Main Office Stores</p>
                <p><strong>Timeline:</strong> 7 Days</p>

                <div class="mb-3">
                    <label class="form-label">Approver Comments</label>
                    <textarea class="form-control" rows="3" placeholder="E.g. Approved as per budget availability."></textarea>
                </div>

                <div class="text-end">
                    <button class="btn btn-success">✅ Approve</button>
                    <button class="btn btn-danger">❌ Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
