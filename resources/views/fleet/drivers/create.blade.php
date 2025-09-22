@extends('layouts.app')
@section('title', 'Register New Driver')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">🧑‍✈️ Register New Driver</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('fleet.drivers.store') }}" enctype="multipart/form-data" method="POST">
            @csrf
            <div class="row g-3">


                {{-- Staff Number --}}
                <div class="col-md-6">
                    <label for="StaffNumber" class="form-label">Staff Member</label>
                    <select name="StaffNumber" id="StaffNumber" class="form-select" required>
                        <option value="">-- Select Staff Member --</option>
                        @foreach ($staffNo as $employee)
                            <option
                                value="{{ $employee->Id }}"
                                data-firstname="{{ $employee->FirstName }}"
                                data-lastname="{{ $employee->LastName }}"
                                data-email="{{ $employee->Email }}"
                                data-phone="{{ $employee->Phone }}"
                                {{ old('StaffNumber') == $employee->Id ? 'selected' : '' }}>
                                {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeID }})
                            </option>
                        @endforeach
                    </select>
                </div>


                {{-- Full Name (autopopulated) --}}
                <div class="col-md-6">
                    <label for="FullName" class="form-label">Full Name</label>
                    <input type="text" name="FullName" id="FullName" class="form-control"
                           value="{{ old('FullName') }}" required readonly>
                </div>

                {{-- National ID --}}
                <div class="col-md-6">
                    <label for="NationalID" class="form-label">National ID</label>
                    <input type="text" name="NationalID" class="form-control" value="{{ old('NationalID') }}">
                </div>

                {{-- Phone --}}
                <div class="col-md-6">
                    <label for="Phone" class="form-label">Phone Number</label>
                    <input type="text" name="Phone" id="Phone" class="form-control" value="{{ old('Phone') }}">
                </div>

                {{-- Email --}}
                <div class="col-md-6">
                    <label for="Email" class="form-label">Email</label>
                    <input type="text" name="Email" id="Email" class="form-control" value="{{ old('Email') }}">
                </div>

                {{-- Employment Type --}}
                <div class="col-md-6">
                    <label for="EmploymentType" class="form-label">Employment Type</label>
                    <select name="EmploymentType" class="form-select">
                        <option value="">Select Type</option>
                        @foreach($employmentType as $type)
                            <option value="{{ $type->ID }}" {{ old('EmploymentType') == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Notes --}}
                <div class="col-md-12">
                    <label for="Notes" class="form-label">Notes</label>
                    <textarea name="Notes" class="form-control" rows="2">{{ old('Notes') }}</textarea>
                </div>

                {{-- Is Active --}}
                <div class="col-md-4 mt-3">
                    <input type="hidden" name="IsActive" value="0">
                    <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input"
                           value="1" {{ old('IsActive') ? 'checked' : '' }}>
                    <label for="IsActive" class="form-check-label">Active</label>
                </div>
            </div>

            <!-- Document Upload -->
            <div class="mb-3 mt-3">
                <label class="form-label">Upload Supporting Documents</label>
                <input type="file" name="Document" class="form-control" multiple>
                <small class="text-muted">e.g. ID copy, Certificate of Incorporation</small>
            </div>

            <div class="mt-4">
                <button class="btn btn-success" type="submit">💾 Save Driver</button>
                <a href="{{ route('fleet.drivers.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const staffSelect = document.getElementById('StaffNumber');
            const fullNameInput = document.getElementById('FullName');
            const phoneInput = document.getElementById('Phone');
            const emailInput = document.getElementById('Email');

            staffSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];

                if (!selectedOption.value) {
                    fullNameInput.value = '';
                    phoneInput.value = '';
                    emailInput.value = '';
                    return;
                }

                const firstName = selectedOption.getAttribute('data-firstname') || '';
                const lastName = selectedOption.getAttribute('data-lastname') || '';
                const phone = selectedOption.getAttribute('data-phone') || '';
                const email = selectedOption.getAttribute('data-email') || '';

                fullNameInput.value = `${firstName} ${lastName}`.trim();
                phoneInput.value = phone;
                emailInput.value = email;
            });
        });
    </script>
@endpush
