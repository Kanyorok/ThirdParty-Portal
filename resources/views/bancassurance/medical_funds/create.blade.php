@extends('layouts.app')
@section('title', 'Create Medical Funds')

@section('content')
<div class="container">

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>There were validation errors:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-info mb-3">
        <div class="fw-semibold">Heads up</div>
        <div class="small">
            Detailed benefits and premiums are configured as <strong>Packages</strong> after you create the fund.
            Packages are built from your Coverage Catalog and can include compulsory or optional coverages.
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('bancassurance.medicalfunds.store') }}" method="POST" id="fundCreateForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Fund Name <span class="text-danger">*</span></label>
                        <input type="text" name="FundName" class="form-control" value="{{ old('FundName') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Provider <span class="text-danger">*</span></label>
                        <select name="ProviderId" class="form-select" required>
                            <option value="">-- select provider --</option>
                            @foreach($providers as $p)
                                <option value="{{ $p->Id }}" @selected(old('ProviderId')==$p->Id)>{{ $p->Name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Manage providers in <code>Insurance Providers</code>.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Coverage Type<span class="text-danger">*</span></label>
                        <select name="CoverageType" class="form-select rounded-3" required>
                            <option value="">-- Select Coverage Type --</option>
                            @foreach ($coverageTypes as $ct)
                                <option value="{{ $ct->ID }}" @selected(old('CoverageType')==$ct->ID)>
                                    {{ $ct->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Coverage Limit<span class="text-danger">*</span></label>
                        <input type="number" name="CoverageLimit" step="0.01" min="0"
                               value="{{ old('CoverageLimit') }}" 
                               class="form-control rounded-3 text-end" placeholder="e.g. 10000.00" 
                               onblur="fixDecimal(this)">
                        <div class="form-text">Optional headline limit for display. Package rules still apply.</div>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="isActive" {{ old('IsActive',1) ? 'checked':'' }}>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="3" placeholder="Notes about the fund or enrollment rules">{{ old('Description') }}</textarea>
                    </div>
                </div>

                <div class="mt-3 d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" name="next" value="none">Save</button>

                    <!-- Optional guided flows after create -->
                    <button class="btn btn-outline-primary" name="next" value="packages">
                        Save &amp; Configure Packages
                    </button>
                    <button class="btn btn-outline-secondary" name="next" value="contributors">
                        Save &amp; Add Contributors
                    </button>

                    <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-outline-dark ms-auto">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function fixDecimal(input) {
    let val = parseFloat(input.value);
    if (!isNaN(val)) {
        input.value = val.toFixed(2);
    }
}
</script>
@endsection
