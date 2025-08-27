@extends('layouts.app')

@section('content')
<div class="container">
    <h4>New Contribution — {{ $medical_fund->FundName }}</h4>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('bancassurance.medicalfunds.contributions.store',$medical_fund->ID) }}" method="POST">
                @csrf
                    <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date *</label>
                        <input type="date" name="ContributionDate" class="form-control" value="{{ old('ContributionDate', now()->toDateString()) }}" required>
                    </div>

                    {{-- If preselected contributor (coming from Contributor Show), show as locked --}}
                    @if(isset($contributor) && $contributor)
                        <input type="hidden" name="ContributorID" value="{{ $contributor->ID }}">
                        <div class="col-md-6">
                        <label class="form-label">Contributor</label>
                        <input type="text" class="form-control" value="{{ $contributor->FullName }}" disabled>
                        <div class="form-text">Locked to this contributor.</div>
                        </div>
                    @else
                        <div class="col-md-6">
                        <label class="form-label">Contributor *</label>
                        <select name="ContributorID" class="form-select" required>
                            <option value="">-- select contributor --</option>
                            @foreach($contributors as $c)
                            <option value="{{ $c->ID }}" @selected(old('ContributorID')==$c->ID)>{{ $c->FullName }}</option>
                            @endforeach
                        </select>
                        </div>
                    @endif

                    <div class="col-md-3">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount') }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="Notes" class="form-control" rows="2">{{ old('Notes') }}</textarea>
                    </div>
                    </div>

                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary">Save</button>
                    <a href="{{ route('bancassurance.medicalfunds.contributions.index',$medical_fund->ID) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
