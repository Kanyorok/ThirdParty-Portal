@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Edit Contribution — {{ $medical_fund->FundName }}</h4>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('bancassurance.medicalfunds.contributions.update',$contribution->ID) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date *</label>
                        <input type="date" name="ContributionDate" class="form-control" value="{{ old('ContributionDate', optional($contribution->ContributionDate)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contributor Type *</label>
                        <input type="text" name="ContributorType" class="form-control" value="{{ old('ContributorType',$contribution->ContributorType) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contributor ID</label>
                        <input type="number" name="ContributorID" class="form-control" value="{{ old('ContributorID',$contribution->ContributorID) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount',$contribution->Amount) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <input type="text" name="Notes" class="form-control" value="{{ old('Notes',$contribution->Notes) }}">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('bancassurance.medicalfunds.contributions.index',$medical_fund->ID) }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
