@extends('layouts.app')
@section('title', 'Create Medical Fund')

@section('content')
<div class="container mt-5" style="max-width: 950px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary text-white rounded-top-4 d-flex justify-content-between align-items-center">
            <p class="mb-0 fw-bold"><b>Medical Fund info</b></p>
            <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-light btn-sm rounded-3">
                <i class="bi bi-arrow-left-circle me-1"></i> Back
            </a>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>There were some issues with your submission:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('bancassurance.medicalfunds.store') }}" method="POST">
                @csrf
                <div class="row g-4">

                    {{-- Fund Name --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fund Name <span class="text-danger">*</span></label>
                        <input type="text" name="FundName" value="{{ old('FundName') }}" 
                               class="form-control rounded-3" placeholder="Enter fund name" required>
                    </div>

                    {{-- Provider --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Provider <span class="text-danger">*</span></label>
                        <select name="ProviderId" class="form-select rounded-3" required>
                            <option value="">-- Select Provider --</option>
                            @foreach ($providers as $p)
                                <option value="{{ $p->ID }}" @selected(old('ProviderID')==$p->ID)>
                                    {{ $p->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Coverage Type --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Coverage Type<span class="text-danger">*</span></label>
                        <select name="CoverageType" class="form-select rounded-3">
                            <option value="">-- Select Coverage Type --</option>
                            @foreach ($coverageTypes as $ct)
                                <option value="{{ $ct->ID }}" @selected(old('CoverageType')==$ct->ID)>
                                    {{ $ct->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Coverage Limit --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Coverage Limit<span class="text-danger">*</span></label>
                        <input type="number" name="CoverageLimit" step="0.01" min="0"
                               value="{{ old('CoverageLimit') }}" 
                               class="form-control rounded-3 text-end" placeholder="e.g. 10000.00" 
                               onblur="fixDecimal(this)">
                    </div>

                    {{-- Active Status --}}
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="IsActive" id="isActive" value="1"
                                   {{ old('IsActive', 1) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActive">
                                Active Fund
                            </label>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="Description" class="form-control rounded-3" rows="3" 
                                  placeholder="Enter short description...">{{ old('Description') }}</textarea>
                    </div>
                </div>

                {{-- Footer Buttons --}}
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-outline-secondary rounded-3 px-4">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success rounded-3 px-4">
                        <i class="bi bi-check-circle me-1"></i> Save Fund
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Format Coverage Limit to two decimals --}}
<script>
function fixDecimal(input) {
    let val = parseFloat(input.value);
    if (!isNaN(val)) {
        input.value = val.toFixed(2);
    }
}
</script>
@endsection
