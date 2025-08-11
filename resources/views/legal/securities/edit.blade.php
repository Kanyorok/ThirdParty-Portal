@extends('layouts.app')
@section('title', 'Edit Loan Security')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">✏️ Edit Loan Security</h4>

    <form action="{{ route('legal.securities.update', $security->ID) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Security Type</label>
                <input type="text" name="SecurityType" class="form-control" value="{{ $security->SecurityType }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Owner Name</label>
                <input type="text" name="OwnerName" class="form-control" value="{{ $security->OwnerName }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Owner ID Number</label>
                <input type="text" name="OwnerIDNumber" class="form-control" value="{{ $security->OwnerIDNumber }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Loan Account Number</label>
                <input type="text" name="LoanAccountNumber" class="form-control" value="{{ $security->LoanAccountNumber }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Value</label>
                <input type="number" name="Value" class="form-control" step="0.01" value="{{ $security->Value }}">
            </div>
            <div class="col-md-6 mb-3">
                <label>Institution</label>
                <input type="text" name="Institution" class="form-control" value="{{ $security->Institution }}">
            </div>
            <div class="col-md-12 mb-3">
                <label>Registration Details</label>
                <textarea name="RegistrationDetails" class="form-control" rows="2">{{ $security->RegistrationDetails }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label>Security Status</label>
                <select name="SecurityStatus" class="form-control">
                    <option value="Held" {{ $security->SecurityStatus == 'Held' ? 'selected' : '' }}>Held</option>
                    <option value="Released" {{ $security->SecurityStatus == 'Released' ? 'selected' : '' }}>Released</option>
                    <option value="Discharged" {{ $security->SecurityStatus == 'Discharged' ? 'selected' : '' }}>Discharged</option>
                </select>
            </div>
            <div class="col-md-12 mb-3">
                <label>Remarks</label>
                <textarea name="Remarks" class="form-control" rows="2">{{ $security->Remarks }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-success">💾 Update Security</button>
    </form>
</div>
@endsection
