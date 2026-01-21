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
                    <label for="StaffNumber" class="form-label">Staff Member<span class="text-danger">*</span></label>
                    <select name="StaffNumber" id="StaffNumber" class="form-select" required>
                        <option value="">-- Select Staff Member --</option>
                        @foreach ($staffNo as $employee)
                            <option
                                value="{{ $employee->Id }}"
                                data-firstname="{{ $employee->FirstName }}"
                                data-lastname="{{ $employee->LastName }}"
                                data-email="{{ $employee->Email }}"
                                data-phone="{{ $employee->Phone }}"
                                data-image="{{ $employee->image ? $employee->image->image_src : '' }}"
                                data-image-id="{{ $employee->ImageId ?? '' }}"
                                {{ old('StaffNumber') == $employee->Id ? 'selected' : '' }}>
                                {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeID }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Full Name --}}
                <div class="col-md-6">
                    <label for="FullName" class="form-label">Full Name<span class="text-danger">*</span></label>
                    <input type="text" name="FullName" id="FullName" class="form-control"
                           value="{{ old('FullName') }}" required readonly>
                </div>

                {{-- National ID --}}
                <div class="col-md-6">
                    <label for="NationalID" class="form-label">National ID<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="NationalID" id="NationalID"
                           value="{{ old('NationalID') }}" minlength="8"
                           maxlength="9"
                           pattern="\d{8,9}"
                           title="National ID must be exactly 8 or 9 digits"
                           required>
                </div>


            {{-- Phone --}}
            <div class="col-md-6">
                <label for="Phone" class="form-label">Phone Number<span class="text-danger">*</span></label>
                <input type="text" name="Phone" id="Phone" class="form-control" value="{{ old('Phone') }}" readonly>
            </div>

                {{-- Email --}}
                <div class="col-md-6">
                    <label for="Email" class="form-label">Email<span class="text-danger">*</span></label>
                    <input type="text" name="Email" id="Email" class="form-control" value="{{ old('Email') }}" readonly>
            </div>

                {{-- Employment Type --}}
                <div class="col-md-6">
                    <label for="EmploymentType" class="form-label">Employment Type<span class="text-danger">*</span></label>
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
                    <label for="Notes" class="form-label">Notes<span class="text-danger">*</span></label>
                    <textarea name="Notes" class="form-control" rows="2">{{ old('Notes') }}</textarea>
                </div>


                {{-- Driver Image Preview --}}
                <div class="mb-3 mt-3">
                    <label class="form-label">Driver Image<span class="text-danger">*</span></label>
                    <div class="mb-2">
                        @php
                            $preselectedEmployee = $staffNo->firstWhere('Id', old('StaffNumber'));
                            $initialImage = $preselectedEmployee && $preselectedEmployee->image
                                ? $preselectedEmployee->image->image_src
                                : '/images/default.png';
                        @endphp
                        <img id="DriverImagePreview"
                             src="{{ $initialImage }}"
                             alt="Driver Image"
                             class="img-thumbnail"
                             style="max-height:150px;">
                    </div>
                    <input type="hidden" name="ImageId" id="ImageId" value="{{ old('ImageId') ?? '' }}">
                </div>
            </div>

            {{-- Is Active --}}
            <div class="col-md-4 mt-3">
                <input type="hidden" name="IsActive" value="0">
                <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input"
                       value="1" {{ old('IsActive') ? 'checked' : '' }}>
                <label for="IsActive" class="form-check-label">Active</label>
            </div>


            {{-- Document Upload --}}
            <div class="mb-3 mt-3">
                <label class="form-label">Upload Supporting Document<span class="text-danger">*</span></label>
                <input type="file" name="Document" class="form-control">
                <small class="text-muted">Attach inspection sheet, photos, or related files</small>
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
            const imagePreview = document.getElementById('DriverImagePreview');
            const imageIdInput = document.getElementById('ImageId');

            function updateDriverDetails() {
                const selectedOption = staffSelect.options[staffSelect.selectedIndex];

                // If no staff member is selected, clear all fields and set the default image
                if (!selectedOption.value) {
                    fullNameInput.value = '';
                    phoneInput.value = '';
                    emailInput.value = '';
                    imagePreview.src = '/images/default.png';
                    imageIdInput.value = '';
                    return;
                }

                const firstName = selectedOption.getAttribute('data-firstname') || '';
                const lastName = selectedOption.getAttribute('data-lastname') || '';
                const phone = selectedOption.getAttribute('data-phone') || '';
                const email = selectedOption.getAttribute('data-email') || '';
                const imageUrl = selectedOption.getAttribute('data-image') || '';
                const imageId = selectedOption.getAttribute('data-image-id') || '';

                // Update the form fields with the data from the selected employee
                fullNameInput.value = `${firstName} ${lastName}`.trim();
                phoneInput.value = phone;
                emailInput.value = email;
                imageIdInput.value = imageId;

                // Update the image preview source
                imagePreview.src = imageUrl ? imageUrl : '/images/default.png';
            }

            // Add event listener to the staff select dropdown
            staffSelect.addEventListener('change', updateDriverDetails);

            // Call the function on page load to handle pre-selected values (e.g., after validation fails)
            updateDriverDetails();
        });
    </script>
@endpush
