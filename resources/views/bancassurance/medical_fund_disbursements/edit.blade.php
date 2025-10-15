@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Edit Disbursement — {{ $medical_fund->FundName }}</h4>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('bancassurance.medicalfunds.disbursements.update',$disbursement->ID) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Disbursement Date *</label>
                        <input type="date" name="DisbursementDate" class="form-control" value="{{ old('DisbursementDate', optional($disbursement->DisbursementDate)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Beneficiary *</label>
                        <select name="BeneficiaryID" class="form-select" required>
                            @foreach($beneficiaries as $b)
                                <option value="{{ $b->ID }}" @selected(old('BeneficiaryID',$disbursement->BeneficiaryID)==$b->ID)>{{ $b->FullName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount',$disbursement->Amount) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Purpose</label>
                        <input type="text" name="Purpose" class="form-control" value="{{ old('Purpose',$disbursement->Purpose) }}">
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('bancassurance.medicalfunds.disbursements.index',$medical_fund->ID) }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
