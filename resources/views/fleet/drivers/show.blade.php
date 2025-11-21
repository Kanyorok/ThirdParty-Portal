@extends('layouts.app')

@section('title', 'Driver Details')

@section('content')
    <div class="container-fluid py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="row">


            {{-- Left Side Panel: Driver Details --}}
            <div class="col-md-4">
    <div class="card h-100 shadow rounded-4">
        <div class="card-body p-3">
            <h5 class="card-title fw-bold mb-3">{{ $driver->FullName }} Details</h5>
            <hr>


            {{-- Vehicle Images --}}
            @if ($driver->image)
                <img src="data:{{ $driver->image->MIMEType }};base64,{{ $driver->image->Image }}"
                     alt="Driver Image"
                     class="img-fluid mb-3 rounded-circle border shadow"
                     style="width: 200px; height: 200px; object-fit: cover;">
            @else
                <img src="{{ asset('images/vehicle-placeholder.png') }}"
                     alt="No Image"
                     class="img-fluid mb-3 rounded-circle border shadow"
                     style="width: 200px; height: 200px; object-fit: cover;">
            @endif


            <dl class="row g-2">
                <dt class="col-md-5">Driver No:</dt>
                <dd class="col-md-7 text-muted">{{ $driver->DriverNo }}</dd>

                <dt class="col-md-5">Full Name:</dt>
                <dd class="col-md-7 text-muted">{{ $driver->FullName }}</dd>

                <dt class="col-md-5">National ID:</dt>
                <dd class="col-md-7 text-muted">{{ $driver->NationalID }}</dd>

                <dt class="col-md-5">Phone:</dt>
                <dd class="col-md-7 text-muted">{{ $driver->Phone }}</dd>

                <dt class="col-md-5">Employment Type:</dt>
                <dd class="col-md-7 text-muted">{{ $driver->employmentType->Description }}</dd>

                <dt class="col-md-5">Active:</dt>
                <dd class="col-md-7 text-muted">{{ $driver->IsActive ? '✅ Active' : '❌ Inactive' }}</dd>

                <dt class="col-md-5">Notes:</dt>
                <dd class="col-md-7 text-muted">{{ $driver->Notes ?: '—' }}</dd>
            </dl>
        </div>

        <div class="card-footer bg-light">
            <h6 class="fw-bold mb-2">📄 Documents</h6>
            @forelse($driver->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
            @empty
                <p class="text-muted mb-0">No documents uploaded.</p>
            @endforelse
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('fleet.drivers.edit', $driver->Id) }}" class="btn btn-warning">✏️ Edit</a>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteDriverModal">🗑️ Delete</button>
        </div>
    </div>
</div>

        {{-- Right Side Panel: Tabs for Licenses, Assignments, and Trips --}}
        <div class="col-md-8">
            <div class="card h-100 p-3 shadow rounded-4">
            <h5 class="card-title fw-bold">Driver Records</h5>
            <p class="fst-italic mb-3">
              💡  Assignments can be created manually, but are also added automatically when a trip is created. Trips are loaded automatically and cannot be added manually.
            </p>
            <hr>

                <ul class="nav nav-tabs mb-3" id="driverTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#licenses">👤 Licenses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#assignments">🚚 Assignments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#trips">🗺️ Trips</a>
                    </li>
                </ul>

                    <div class="tab-content">
                        {{-- Licenses Tab Content --}}
                        <div class="tab-pane fade show active" id="licenses">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5>Licenses</h5>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addLicenseModal">➕ Add License
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>License No</th>
                                        <th>Category</th>
                                        <th>Issued</th>
                                        <th>Expiry</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody id="licensesTable">
                                    @foreach($licenses as $license)
                                        <tr data-id="{{ $license->Id }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $license->LicenseNumber }}</td>
                                            <td>{{ $license->LicenseCategory }}</td>
                                            <td>{{ $license->IssueDate }}</td>
                                            <td>{{ $license->ExpiryDate }}</td>
                                            <td>{{ $license->Notes }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-warning btn-edit" data-type="license"
                                                        data-id="{{ $license->Id }}">✏️
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-delete" data-type="license"
                                                        data-id="{{ $license->Id }}">🗑️
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Assignments Tab Content --}}
                        <div class="tab-pane fade" id="assignments">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5>Assignments</h5>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addAssignmentModal">➕ Add Assignment
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Vehicle</th>
                                        <th>Assigned On</th>
                                        <th>Unassigned On</th>
                                        <th>Purpose</th>
                                        <th>AssignedBy</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody id="assignmentsTable">
                                    @foreach($assignments as $assignment)
                                        <tr data-id="{{ $assignment->Id }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $assignment->vehicle?->RegistrationNo?? '' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($assignment->AssignmentDate)->format('d M Y') }}</td>                                           <td>{{ $assignment->UnassignmentDate ? \Carbon\Carbon::parse($assignment->UnassignmentDate)->format('d M Y') : '—' }}</td>
                                            <td>{{ $assignment->Purpose ?: '—' }}</td>
                                            <td>{{ $assignment->assignedBy?->LastName ?? '—' }}</td>
                                            <td>{{ $assignment->Notes ?: '—' }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-warning btn-edit" data-type="assignment"
                                                        data-id="{{ $assignment->Id }}">✏️
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-delete" data-type="assignment"
                                                        data-id="{{ $assignment->Id }}">🗑️
                                                </button>

                                                {{-- Assign Inspection Button (commented out) --}}
                                                {{-- <a href="{{ route('fleet.driver_assignments.assign_inspection', $assignment->Id) }}"
                                                   class="btn btn-sm btn-success">Assign Inspection</a> --}}

                                                {{-- Unassign Inspection Button (commented out) --}}
                                                {{-- <a href="{{ route('fleet.driver_assignments.unassign_inspection', $assignment->Id) }}"
                                                   class="btn btn-sm btn-danger">Unassign Inspection</a> --}}

                                                {{-- Existing Unassign form (can be removed if using above buttons) --}}
                                                {{-- <form action="{{ route('fleet.vehicle_inspection.create', $assignment->Id) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirmUnassign(event)">
                                                    @csrf
                                                    @method('POST')
                                                    <input type="hidden" name="assignment_id" value="{{ $assignment->Id }}">
                                                    <button type="submit" class="btn btn-sm btn-primary">Unassign</button>
                                                </form> --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="trips">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5>Trips</h5>
                            </div>
                            <h5 class="mt-4">🚗 Assigned Trips</h5>

                        @if ($driver->trips->isEmpty())
                            <p>No trips assigned to this driver.</p>
                        @else
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Trip No</th>
                                        <th>Vehicle Type</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Start</th>
                                        <th>End</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($driver->trips as $trip)
                                        <tr>
                                            <td>{{ $trip->TripNo }}</td>
                                            <td>{{ $trip->parentVehicleType->Description ?? '-' }}</td>
                                            <td>{{ $trip->Purpose ?? '-' }}</td>
                                            <td>{{ $trip->statusDetail->Description ?? '-' }}</td>
                                            <td>{{ $trip->TripStartDate ?? '-' }}</td>
                                            <td>{{ $trip->TripEndDate ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                            </div>

                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    {{-- Add License Modal --}}

    <div class="modal fade" id="addLicenseModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="addLicenseForm" action="{{ route('fleet.licenses.store') }}" method="POST">
                @csrf
                <input type="hidden" name="DriverID" value="{{ $driver->Id }}">

                <input type="hidden" name="DriverID" value="{{ $driver->Id }}">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title">Add License</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">License Number</label>
                        <input type="text" name="LicenseNumber" class="form-control" required>
                        <label class="form-label mt-2">Category</label>
                        <input type="text" name="LicenseCategory" class="form-control">
                        <label class="form-label mt-2">Issue Date</label>
                        <input type="date" name="IssueDate" class="form-control">
                        <label class="form-label mt-2">Expiry Date</label>
                        <input type="date" name="ExpiryDate" class="form-control" required>
                        <label class="form-label mt-2">Renewal Date</label>
                        <input type="date" name="RenewalDate" class="form-control">
                        <label class="form-label mt-2">Notes</label>
                        <textarea name="Notes" class="form-control"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Assignment Modal --}}
    <div class="modal fade" id="addAssignmentModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="addAssignmentForm" action="{{ route('fleet.driver_assignments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="DriverID" value="{{ $driver->Id }}">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Assignment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Vehicle</label>
                        <select name="VehicleID" class="form-control" required>
                            <option value="">-- Select Vehicle --</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->Id }}">{{ $vehicle->RegistrationNo }}</option>
                            @endforeach
                        </select>

                        <label class="form-label mt-2">Assignment Date</label>
                        <input type="date" name="AssignmentDate" class="form-control" required>
                        <label class="form-label mt-2">Unassignment Date</label>
                        <input type="date" name="UnassignmentDate" class="form-control">
                        <label class="form-label mt-2">Purpose</label>
                        <input type="text" name="Purpose" class="form-control">
                        <label for="AssignedBy" class="form-label">Assigned By</label>
                        <select name="AssignedBy" id="AssignedBy" class="form-select" required>
                            <option value="">-- Select Assigned By --</option>
                            @foreach ($assigners as $id => $name)
                                <option
                                    value="{{ $id }}" {{ old('AssignedBy', auth()->id()) == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        <label class="form-label mt-2">Notes</label>
                        <textarea name="Notes" class="form-control"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit License Modal --}}
    <div class="modal fade" id="editLicenseModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editLicenseForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="Id" id="editLicenseId">
                <input type="hidden" name="DriverID" value="{{ $driver->Id }}">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit License</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">License Number</label>
                        <input type="text" name="LicenseNumber" id="editLicenseNumber" class="form-control" required>
                        <label class="form-label mt-2">Category</label>
                        <input type="text" name="LicenseCategory" id="editLicenseCategory" class="form-control">
                        <label class="form-label mt-2">Issue Date</label>
                        <input type="date" name="IssueDate" id="editLicenseIssue" class="form-control">
                        <label class="form-label mt-2">Expiry Date</label>
                        <input type="date" name="ExpiryDate" id="editLicenseExpiry" class="form-control" required>
                        <label class="form-label mt-2">Notes</label>
                        <textarea name="Notes" id="editLicenseNotes" class="form-control"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Assignment Modal --}}
    <div class="modal fade" id="editAssignmentModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editAssignmentForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="DriverID" value="{{ $driver->Id }}">
                <input type="hidden" name="Id" id="editAssignmentId">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Assignment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Vehicle</label>
                        <select name="VehicleID" id="editAssignmentVehicle" class="form-control" required>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->Id }}">{{ $vehicle->RegistrationNo }}</option>
                            @endforeach
                        </select>
                        <label class="form-label mt-2">Assignment Date</label>
                        <input type="date" name="AssignmentDate" id="editAssignmentDate" class="form-control" required>
                        <label class="form-label mt-2">Unassignment Date</label>
                        <input type="date" name="UnassignmentDate" id="editUnassignmentDate" class="form-control">
                        <label class="form-label mt-2">Purpose</label>
                        <input type="text" name="Purpose" id="editAssignmentPurpose" class="form-control">
                        <label for="AssignedBy" class="form-label">Assigned By</label>
                        <select name="AssignedBy" id="editAssignmentAssignedBy" class="form-select" required>
                            <option value="">-- Select Assigned By --</option>
                            @foreach ($assigners as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <label class="form-label mt-2">Notes</label>
                        <textarea name="Notes" id="editAssignmentNotes" class="form-control"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Driver Modal --}}
    <div class="modal fade" id="deleteDriverModal" tabindex="-1" aria-labelledby="deleteDriverModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteDriverModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this driver? This action will also delete all associated licenses
                        and assignments and cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteDriverForm" method="POST"
                          action="{{ route('fleet.drivers.destroy', $driver->Id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete License Modal --}}
    <div class="modal fade" id="deleteLicenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this license? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" id="confirmDeleteLicenseBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Assignment Modal --}}
    <div class="modal fade" id="deleteAssignmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this assignment? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" id="confirmDeleteAssignmentBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
 @include('snippets.actions.preview-files')
    <script>
        let storeLicenseUrl = "{{ route('fleet.licenses.store') }}";
        let updateLicenseUrl = "{{ url('fleet/driver-licenses') }}/";
        let deleteLicenseUrl = "{{ url('fleet/driver-licenses') }}/";


        let storeAssignmentUrl = "{{ route('fleet.driver_assignments.store') }}";
        let updateAssignmentUrl = "{{ url('fleet/driver-assignments') }}/";
        let deleteAssignmentUrl = "{{ url('fleet/driver-assignments') }}/";

        function showSuccess(message) {
            let alert = `<div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
            $(".container-fluid").prepend(alert);
            setTimeout(() => {
                $(".alert").alert('close');
            }, 4000);
        }

        $(function () {
            // ================== ADD LICENSE ==================
            $("#addLicenseForm").submit(function (e) {
                e.preventDefault();
                $.post(storeLicenseUrl, $(this).serialize())
                    .done(function () {
                        $("#addLicenseModal").modal("hide");
                        showSuccess("✅ License created successfully!");
                        location.reload();
                    })
                    .fail(function (xhr) {
                        alert("Error: " + (xhr.responseJSON?.message ?? "Something went wrong"));
                    });
            });

            // ================== ADD ASSIGNMENT ==================
            $("#addAssignmentForm").submit(function (e) {
                e.preventDefault();
                $.post(storeAssignmentUrl, $(this).serialize())
                    .done(function () {
                        $("#addAssignmentModal").modal("hide");
                        showSuccess("✅ Assignment created successfully!");
                        location.reload();
                    })
                    .fail(function (xhr) {
                        alert("Error: " + (xhr.responseJSON?.message ?? "Something went wrong"));
                    });
            });

            // ================== EDIT LICENSE ==================
            $(document).on("click", ".btn-edit[data-type='license']", function () {
                let id = $(this).data("id");
                $.get(updateLicenseUrl + id + "/edit", function (data) {
                    $("#editLicenseId").val(data.Id);
                    $("#editLicenseNumber").val(data.LicenseNumber);
                    $("#editLicenseCategory").val(data.LicenseCategory);
                    $("#editLicenseIssue").val(data.IssueDate);
                    $("#editLicenseExpiry").val(data.ExpiryDate);
                    $("#editLicenseNotes").val(data.Notes);
                    $("#editLicenseModal").modal("show");
                })
                    .fail(function () {
                        alert("Error fetching license details.");
                    });
            });

            $("#editLicenseForm").submit(function (e) {
                e.preventDefault();
                let id = $("#editLicenseId").val();
                let formData = $(this).serialize();
                $.post({
                    url: updateLicenseUrl + id,
                    data: formData,
                    success: function () {
                        $("#editLicenseModal").modal("hide");
                        showSuccess("✅ License updated successfully!");
                        location.reload();
                    },
                    error: function (xhr) {
                        alert("Error: " + (xhr.responseJSON?.message ?? "Something went wrong"));
                    }
                });
            });

            // ================== EDIT ASSIGNMENT ==================
            $(document).on("click", ".btn-edit[data-type='assignment']", function () {
                let id = $(this).data("id");
                $.get(updateAssignmentUrl + id + "/edit", function (data) {
                    $("#editAssignmentId").val(data.Id);
                    $("#editAssignmentVehicle").val(data.VehicleID);
                    $("#editAssignmentDate").val(data.AssignmentDate);
                    $("#editUnassignmentDate").val(data.UnassignmentDate);
                    $("#editAssignmentPurpose").val(data.Purpose);
                    $("#editAssignmentAssignedBy").val(data.AssignedBy);
                    $("#editAssignmentNotes").val(data.Notes);
                    $("#editAssignmentModal").modal("show");
                })
                    .fail(function () {
                        alert("Error fetching assignment details.");
                    });
            });

            $("#editAssignmentForm").submit(function (e) {
                e.preventDefault();
                let id = $("#editAssignmentId").val();
                let formData = $(this).serialize();
                $.post({
                    url: updateAssignmentUrl + id,
                    data: formData,
                    success: function () {
                        $("#editAssignmentModal").modal("hide");
                        showSuccess("✅ Assignment updated successfully!");
                        location.reload();
                    },
                    error: function (xhr) {
                        alert("Error: " + (xhr.responseJSON?.message ?? "Something went wrong"));
                    }
                });
            });

            // ================== DELETE LICENSE ==================
            let licenseToDeleteId;
            $(document).on("click", ".btn-delete[data-type='license']", function () {
                licenseToDeleteId = $(this).data("id");
                $("#deleteLicenseModal").modal("show");
            });

            $("#confirmDeleteLicenseBtn").click(function () {
                $.post({
                    url: deleteLicenseUrl + licenseToDeleteId,
                    data: {_method: 'DELETE', _token: '{{ csrf_token() }}'},
                    success: function () {
                        $("#deleteLicenseModal").modal("hide");
                        showSuccess("✅ License deleted successfully!");
                        location.reload();
                    },
                    error: function (xhr) {
                        alert("Error: " + (xhr.responseJSON?.message ?? "Something went wrong"));
                    }
                });
            });

            // ================== DELETE ASSIGNMENT ==================
            let assignmentToDeleteId;
            $(document).on("click", ".btn-delete[data-type='assignment']", function () {
                assignmentToDeleteId = $(this).data("id");
                $("#deleteAssignmentModal").modal("show");
            });

            $("#confirmDeleteAssignmentBtn").click(function () {
                $.post({
                    url: deleteAssignmentUrl + assignmentToDeleteId,
                    data: {_method: 'DELETE', _token: '{{ csrf_token() }}'},
                    success: function () {
                        $("#deleteAssignmentModal").modal("hide");
                        showSuccess("✅ Assignment deleted successfully!");
                        location.reload();
                    },
                    error: function (xhr) {
                        alert("Error: " + (xhr.responseJSON?.message ?? "Something went wrong"));
                    }
                });
            });
        });

        function confirmUnassign(e) {
            e.preventDefault(); // stop form submission

            if (confirm("Are you sure you want to unassign this vehicle?\n\n⚠️ Please first inspect the vehicle.")) {
                // redirect to inspection create page instead
                window.location.href = "{{ route('fleet.vehicle_inspection.create') }}";
            }

            return false; // always stop normal submit
        }
    </script>
@endsection

