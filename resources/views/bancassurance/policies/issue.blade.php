@extends('layouts.app')
@section('title', 'Issue Policy')

@section('content')
    <div class="container mt-4">
        <h4>📄 Issue Policy: {{ $policy->PolicyNumber ?? 'Pending Number' }}</h4>

        <form action="{{ route('bancassurance.policies.storeIssuance', $policy->Id) }}" method="POST"
              enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Customer</label>
                <input type="text" class="form-control" value="{{ $policy->CustomerName }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Policy Number</label>
                <input type="text" class="form-control" name="PolicyNumber" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Issued Date</label>
                <input type="date" class="form-control" name="IssuedDate" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Expiry Date</label>
                <input type="date" class="form-control" name="ExpiryDate" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Attach Policy Document (PDF)</label>
                <input type="file" class="form-control" name="PolicyDocument" accept=".pdf,.doc,.docx">
            </div>

            <button type="submit" class="btn btn-success">✅ Mark as Issued</button>
        </form>
    </div>
@endsection
