@extends('layouts.app')
@section('title', 'Edit Driver')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">✏️ Edit Driver</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('fleet.drivers.update', $driver->Id) }}" enctype="multipart/form-data" method="POST">
            @csrf
            @method('PUT')

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
                                data-image="{{ $employee->image ? $employee->image->image_src : '' }}"
                                data-image-id="{{ $employee->ImageId ?? '' }}"
                                {{ old('StaffNumber', $driver->StaffNumber) == $employee->Id ? 'selected' : '' }}>
                                {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeID }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Full Name --}}
                <div class="col-md-6">
                    <label for="FullName" class="form-label">Full Name</label>
                    <input type="text" name="FullName" id="FullName" class="form-control"
                           value="{{ old('FullName', $driver->FullName) }}" required readonly>
                </div>

                {{-- National ID --}}
                <div class="col-md-6">
                    <label for="NationalID" class="form-label">National ID</label>
                    <input type="text" class="form-control" name="NationalID" id="NationalID"
                           value="{{ old('NationalID', $driver->NationalID) }}"
                           minlength="8" maxlength="9" pattern="\d{8,9}"
                           title="National ID must be exactly 8 or 9 digits"
                           required>
                </div>

                {{-- Phone --}}
                <div class="col-md-6">
                    <label for="Phone" class="form-label">Phone Number</label>
                    <input type="text" name="Phone" id="Phone" class="form-control"
                           value="{{ old('Phone', $driver->Phone) }}" readonly>
                </div>

                {{-- Email --}}
                <div class="col-md-6">
                    <label for="Email" class="form-label">Email</label>
                    <input type="text" name="Email" id="Email" class="form-control"
                           value="{{ old('Email', $driver->Email) }}" readonly>
                </div>

                {{-- Employment Type --}}
                <div class="col-md-6">
                    <label for="EmploymentType" class="form-label">Employment Type</label>
                    <select name="EmploymentType" class="form-select">
                        <option value="">Select Type</option>
                        @foreach($employmentType as $type)
                            <option value="{{ $type->ID }}"
                                {{ old('EmploymentType', $driver->EmploymentType) == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Notes --}}
                <div class="col-md-12">
                    <label for="Notes" class="form-label">Notes</label>
                    <textarea name="Notes" class="form-control" rows="2">{{ old('Notes', $driver->Notes) }}</textarea>
                </div>

                {{-- Driver Image Preview --}}
                <div class="mb-3 mt-3">
                    <label class="form-label">Driver Image</label>
                    <div class="mb-2">
                        @php
                            $initialImage = $driver->image ? $driver->image->image_src : '/images/default.png';
                        @endphp
                        <img id="DriverImagePreview"
                             src="{{ $initialImage }}"
                             alt="Driver Image"
                             class="img-thumbnail"
                             style="max-height:150px;">
                    </div>
                    <input type="hidden" name="ImageId" id="ImageId" value="{{ old('ImageId', $driver->ImageId) }}">
                </div>
            </div>

            {{-- Is Active --}}
            <div class="col-md-4 mt-3">
                <input type="hidden" name="IsActive" value="0">
                <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input"
                       value="1" {{ old('IsActive', $driver->IsActive) ? 'checked' : '' }}>
                <label for="IsActive" class="form-check-label">Active</label>
            </div>

            {{-- Document Upload --}}
            <div class="mb-3 mt-3">
                <label class="form-label">Supporting Documents</label>

                {{-- Existing documents --}}
                <div class="card-footer bg-light">
                    <h6 class="fw-bold mb-2">📄 Documents</h6>

                    {{-- Existing documents --}}
                    @forelse($driver->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                        {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                    @empty
                        <p class="text-muted mb-0">No documents uploaded.</p>
                    @endforelse

                    {{-- Upload new documents
                    <div class="mb-3 mt-3">
                        <label class="form-label">Upload Supporting Document</label>
                        <input type="file" name="Document" class="form-control">
                        <small class="text-muted">Attach inspection sheet, photos, or related files</small>
                    </div>
                --}}


                    <div class="mt-4">
                        <button class="btn btn-primary" type="submit">💾 Update Driver</button>
                        <a href="{{ route('fleet.drivers.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
        </form>
    </div>
@endsection

@push('scripts')
    @include('snippets.actions.preview-files')
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

                fullNameInput.value = `${firstName} ${lastName}`.trim();
                phoneInput.value = phone;
                emailInput.value = email;
                imageIdInput.value = imageId;
                imagePreview.src = imageUrl ? imageUrl : '/images/default.png';
            }

            staffSelect.addEventListener('change', updateDriverDetails);
            updateDriverDetails();
        });
    </script>
@endpush
