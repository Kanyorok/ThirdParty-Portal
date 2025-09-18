@extends('layouts.app')
@section('title', 'Create Bank')

@section('content')
<div class="container mt-0">
    <div class="card shadow rounded-4">
        <div class="card-header bg-light py-2 px-3 d-flex align-items-center">
            <h6 class="mb-0 text-muted">
                <i class="fas fa-university text-info"></i> 
            </h6>
        </div>

        <div class="card-body">
            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger rounded-3">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('finance.bank.store') }}" method="POST" novalidate>
        @csrf

                {{-- Bank Info --}}
                <div class="card mb-3 border-0 shadow-sm rounded-3">
                    <div class="card-header bg-light py-2 px-3">
                        <h6 class="mb-0 text-primary"><i class="fas fa-info-circle me-1"></i> Bank Info</h6>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                            <input name="BankName" class="form-control" value="{{ old('BankName') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Short Name</label>
                            <input name="ShortName" class="form-control" value="{{ old('ShortName') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bank Code</label>
                            <input name="BankCode" class="form-control" value="{{ old('BankCode') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SWIFT Code</label>
                            <input name="SwiftCode" class="form-control" value="{{ old('SwiftCode') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Clearing Code</label>
                            <input name="ClearingCode" class="form-control" value="{{ old('ClearingCode') }}" required>
                        </div>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div class="card mb-3 border-0 shadow-sm rounded-3">
                    <div class="card-header bg-light py-2 px-3">
                        <h6 class="mb-0 text-primary"><i class="fas fa-address-book me-1"></i> Contact Info</h6>
                    </div>
                    <div class="card-body row g-3">
                         <div class="col-md-6">
                             <label class="form-label">Country</label>
                             <select name="CountryID" class="form-select select2-country" required>
                                 <option value="" disabled selected>Select Country</option>
                                 @foreach(\App\Models\Core\Country::active()->ordered()->get(['Id','Name']) as $c)
                                     <option value="{{ $c->Id }}" {{ old('CountryID') == $c->Id ? 'selected' : '' }}>{{ $c->Name }}</option>
                                 @endforeach
                             </select>
                         </div>
                         <div class="col-md-6">
                             <label class="form-label">Email</label>
                             <input type="email" name="EmailID" class="form-control" value="{{ old('EmailID') }}" required>
                         </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input name="Phone" class="form-control" value="{{ old('Phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website</label>
                            <input type="url" name="Website" class="form-control" value="{{ old('Website') }}" required>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('finance.bank.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success"
                            onclick="if(this.form.checkValidity()){
                                this.disabled = true;
                                this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Please Wait...';
                                this.form.submit();
                            }">
                        <i class="fas fa-save me-1"></i> Create Bank
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function(){
        if (window.jQuery && $.fn.select2) {
            $('.select2-country').select2({
                width: '100%',
                placeholder: 'Select Country',
                allowClear: true
            });
        }
    })();
</script>
@endsection
