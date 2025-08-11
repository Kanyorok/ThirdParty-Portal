@extends('layouts.app')
@section('title', 'Register New Loan Security')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">➕ Register New Loan Security</h4>

    <form action="{{ route('legal.securities.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Security Type</label>
                <input type="text" name="SecurityType" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Owner Name</label>
                <input type="text" name="OwnerName" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Owner ID Number</label>
                <input type="text" name="OwnerIDNumber" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Loan Account Number</label>
                <input type="text" name="LoanAccountNumber" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Value</label>
                <input type="number" name="Value" class="form-control" step="0.01">
            </div>
            <div class="col-md-6 mb-3">
                <label>Institution</label>
                <input type="text" name="Institution" class="form-control">
            </div>
            <div class="col-md-12 mb-3">
                <label>Registration Details</label>
                <textarea name="RegistrationDetails" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label>Security Status</label>
                <select name="SecurityStatus" class="form-control">
                    <option value="Held">Held</option>
                    <option value="Released">Released</option>
                    <option value="Discharged">Discharged</option>
                </select>
            </div>
            <div class="col-md-12 mb-3">
                <label>Remarks</label>
                <textarea name="Remarks" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-success">💾 Save Security</button>
    </form>
</div>
@endsection
