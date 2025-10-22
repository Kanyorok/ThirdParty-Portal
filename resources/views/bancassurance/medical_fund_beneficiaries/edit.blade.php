@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Edit Beneficiary — {{ $medical_fund->FundName }}</h4>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            {{-- 🔁 Shallow member route for update --}}
            <form action="{{ route('bancassurance.beneficiaries.update', $beneficiary->ID) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="FullName" class="form-control"
                               value="{{ old('FullName', $beneficiary->FullName) }}" required>
                    </div>

                    {{-- 🔽 Relationship dropdown (uses $relationships if provided) --}}
                    <div class="col-md-3">
                        <label class="form-label">Relationship *</label>
                        @php
                          $rels = isset($relationships) && count($relationships)
                              ? $relationships
                              : collect([(object)['Name'=>'Self'],(object)['Name'=>'Spouse'],(object)['Name'=>'Child'],
                                         (object)['Name'=>'Parent'],(object)['Name'=>'Guardian'],(object)['Name'=>'Sibling'],
                                         (object)['Name'=>'Other']]);
                          $currentRel = old('Relationship', $beneficiary->Relationship);
                        @endphp
                        <select name="Relationship" class="form-select" required>
                            <option value="">-- select --</option>
                            @foreach($rels as $rel)
                                <option value="{{ $rel->Name }}" @selected($currentRel === $rel->Name)>{{ $rel->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="DateOfBirth" class="form-control"
                               value="{{ old('DateOfBirth', optional($beneficiary->DateOfBirth)->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">National ID</label>
                        <input type="text" name="NationalID" class="form-control"
                               value="{{ old('NationalID', $beneficiary->NationalID) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Contact</label>
                        <input type="text" name="Contact" class="form-control"
                               value="{{ old('Contact', $beneficiary->Contact) }}">
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="IsActive" id="isActive" value="1"
                                   {{ old('IsActive', $beneficiary->IsActive) ? 'checked' : '' }}>
                            <label for="isActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary">Update</button>
                    {{-- 📚 Collection route remains nested (by fund) --}}
                    <a href="{{ route('bancassurance.medicalfunds.beneficiaries.index', ['medical_fund' => $medical_fund->ID]) }}"
                       class="btn btn-outline-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
