@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Edit Medical Fund</h4>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>There were validation errors:</strong>
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('bancassurance.medicalfunds.update', ['medical_fund' => $medical_fund->ID]) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Fund Name *</label>
                        <input type="text" name="FundName" class="form-control" value="{{ old('FundName',$medical_fund->FundName) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Provider *</label>
                        <select name="ProviderID" class="form-select" required>
                            @foreach($providers as $p)
                                <option value="{{ $p->ID }}" @selected(old('ProviderID',$medical_fund->ProviderID)==$p->ID)>{{ $p->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Coverage Type</label>
                        <input type="text" name="CoverageType" class="form-control" value="{{ old('CoverageType',$medical_fund->CoverageType) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Coverage Limit</label>
                        <input type="number" step="0.01" name="CoverageLimit" class="form-control" value="{{ old('CoverageLimit',$medical_fund->CoverageLimit) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="isActive" {{ old('IsActive',$medical_fund->IsActive) ? 'checked':'' }}>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="3">{{ old('Description',$medical_fund->Description) }}</textarea>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row mt-4 g-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title">Beneficiaries</h6>
                    <p class="text-muted mb-2">Manage fund beneficiaries.</p>
                    <a class="btn btn-sm btn-outline-secondary"
                       href="{{ route('bancassurance.medicalfunds.beneficiaries.index', ['medical_fund' => $medical_fund->ID]) }}">
                       Open
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title">Contributions</h6>
                    <p class="text-muted mb-2">Record & review contributions.</p>
                    <a class="btn btn-sm btn-outline-secondary"
                       href="{{ route('bancassurance.medicalfunds.contributions.index', ['medical_fund' => $medical_fund->ID]) }}">
                       Open
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title">Disbursements</h6>
                    <p class="text-muted mb-2">Authorize & track disbursements.</p>
                    <a class="btn btn-sm btn-outline-secondary"
                       href="{{ route('bancassurance.medicalfunds.disbursements.index', ['medical_fund' => $medical_fund->ID]) }}">
                       Open
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
