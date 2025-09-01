@extends('layouts.app')
@section('title', 'Edit Loan Security')

@section('content')
<div class="card p-1 shadow rounded-4">
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <p class="text-muted">Update the details of this loan security or collateral.</p>

        <form action="{{ route('legal.securities.update', $security->Id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Security Type</label>
                    <select class="form-select" name="SecurityType" id="SecurityType" required>
                        <option disabled value="">-- Select Security Type --</option>
                        @foreach($details as $item)
                            <option value="{{ $item->Value }}" 
                                {{ $security->SecurityType == $item->Value ? 'selected' : '' }}>
                                {{ $item->Value }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Owner Name</label>
                    <input type="text" name="OwnerName" class="form-control"
                        value="{{ old('OwnerName', $security->OwnerName) }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Owner ID Number</label>
                    <input type="text" name="OwnerIDNumber" class="form-control"
                        value="{{ old('OwnerIDNumber', $security->OwnerIDNumber) }}" required>
                </div>

                <div class="col-md-6">
                    <label>Loan Account Number</label>
                    <input type="text" name="LoanAccountNumber" class="form-control"
                        value="{{ old('LoanAccountNumber', $security->LoanAccountNumber) }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Value</label>
                    <input type="number" name="Value" class="form-control" step="0.01"
                        value="{{ old('Value', $security->Value) }}" required>
                </div>

                <div class="col-md-6">
                    <label>Institution</label>
                    <input type="text" name="Institution" class="form-control"
                        value="{{ old('Institution', $security->Institution) }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Registration Details</label>
                    <input name="RegistrationDetails" class="form-control"
                        value="{{ old('RegistrationDetails', $security->RegistrationDetails) }}" required>
                </div>

                <div class="col-md-6">
                    <label>Location</label>
                    <select class="form-select" name="Locations" id="Locations" required>
                        <option disabled value="">-- Select Location --</option>
                        @foreach($locations as $item)
                            <option value="{{ $item->Value }}" 
                                {{ $security->Locations == $item->Value ? 'selected' : '' }}>
                                {{ $item->Value }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="SecurityStatus" class="form-label">Security Status</label>
                <select name="SecurityStatus" class="form-select" required>{{ old('SecurityStatus', $security->SecurityStatus)}}
                    <option value="Held">Held</option>
                    <option value="Released">Released</option>
                    <option value="Discharged">Discharged</option>
                </select>
            </div>


            <div class="mb-3">
                <label>Remarks</label>
                <textarea name="Remarks" class="form-control" rows="2" required>{{ old('Remarks', $security->Remarks) }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-3">
                <a href="{{ route('legal.securities.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-long-arrow-alt-left"></i> Back
                </a>
                <button type="submit" class="btn btn-info"
                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit();}">
                    <i class="fas fa-edit"></i> Edit Security
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
