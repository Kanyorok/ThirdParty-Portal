@extends('layouts.app')
@section('title', 'Register New Loan Security')

@section('content')
<div class="card p-1 shadow rounded-4">
    <div class="card-body">
        <p class="text-muted">Fill in the details below to register a new loan security or collateral.</p>
        <form action="{{ route('legal.securities.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Security Type</label>
                    <select class="form-select" name="SecurityType" id="SecurityType">
                        <option selected disabled value="">-- Select Security Type --</option>
                            @foreach( $details as $item)
                                <option value="{{ $item->Value}}">{{ $item->Value }}</option>
                            @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Owner Name</label>
                    <input type="text" name="OwnerName" class="form-control" placeholder="Full name of the owner" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Owner ID Number</label>
                    <input type="text" name="OwnerIDNumber" class="form-control" placeholder="National ID or Passport" required>
                </div>
                <div class="col-md-6">
                    <label>Loan Account Number</label>
                    <input type="text" name="LoanAccountNumber" class="form-control" placeholder="e.g. LN123456" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Value</label>
                    <input type="number" name="Value" class="form-control" placeholder="e.g. 100000.00" step="0.01" required>
                </div>
                <div class="col-md-6">
                    <label>Institution</label>
                    <input type="text" name="Institution" class="form-control" placeholder="Bank or institution name" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Registration Details</label>
                    <input name="RegistrationDetails" class="form-control" placeholder="e.g. Registered at Lands Office" required>
                </div>
                <div class="col-md-6">
                    <label>Loacation</label>
                    <select class="form-select" name="Locations" id="SecurityType">
                        <option selected disabled value="">-- Select Loacation  --</option>
                            @foreach( $locations as $item)
                                <option value="{{ $item->Value}}">{{ $item->Value }}</option>
                            @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label>Remarks</label>
                <textarea name="Remarks" class="form-control" rows="2" placeholder="Additional notes or details"></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2 mb-3">
                <a href="{{ route('legal.securities.index') }}" class="btn btn-outline-secondary"><i class="fas fa-long-arrow-alt-left"></i> Back</a>
                <button type="submit" class="btn btn-info" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}"><i class="fas fa-save"></i> Save security</button>
            </div>
        </form>
    </div>
</div>
@endsection
