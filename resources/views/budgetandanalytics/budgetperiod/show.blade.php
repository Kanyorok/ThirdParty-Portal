@extends('layouts.app')
@section('title', 'View Budget')
@section('content')

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card p-4 shadow-sm border-0 rounded-3">
        <h3 class="fw-bold text-primary mb-4">Budget Details</h3>
        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-medium">Budget Name</label>
                <input type="text" class="form-control rounded-3" value="{{ $budget->Name }}" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-medium">Fiscal Year</label>
                <input type="text" class="form-control rounded-3" value="{{ $budget->FiscalYear }}" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-medium">From (Date)</label>
                <input type="text" class="form-control rounded-3" value="{{ \Carbon\Carbon::parse($budget->From)->format('Y-m-d') }}" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-medium">To (Date)</label>
                <input type="text" class="form-control rounded-3" value="{{ \Carbon\Carbon::parse($budget->To)->format('Y-m-d') }}" readonly>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label fw-medium">Notes</label>
                <textarea class="form-control rounded-3" rows="3" readonly>{{ $budget->Notes }}</textarea>
            </div>
        </div>

        <!-- GL Attachments Section -->
        <h5 class="mt-4 mb-3 fw-bold text-primary">Attached GL Accounts</h5>
        <div class="table-responsive">
            <table class="table table-hover table-bordered rounded-3" id="glAttachmentsTable">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Account ID</th>
                        <th scope="col">Description</th>
                        <th scope="col">Account Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="glAttachmentsBody">
                    @foreach ($glAttachments as $gl)
                        <tr data-gl-id="{{ $gl->Id }}">
                            <td>{{ $gl->AccountID }}</td>
                            <td class="description-cell">{{ $gl->Description ?? 'N/A' }}</td>
                            <td>
                                {{ $glAccountTypes->firstWhere('GLAccountTypeID', $gl->GLAccountTypeID)->Description ?? 'Unknown Type' }}
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary edit-gl-btn me-1" data-gl-id="{{ $gl->Id }}" data-description="{{ $gl->Description ?? '' }}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-gl-btn" data-gl-id="{{ $gl->Id }}">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <a href="{{ route('budgetperiod.index') }}" class="btn btn-outline-secondary rounded-3">Back to Budgets</a>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editGlModal" tabindex="-1" aria-labelledby="editGlModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editGlModalLabel">Edit GL Attachment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editGlForm" method="post" action="">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editDescription" class="form-label fw-medium">Description</label>
                            <input type="text" class="form-control rounded-3" id="editDescription" name="Description" placeholder="Enter description">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-3" id="saveGlButton">
                            <i class="bi bi-save me-1"></i>Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom CSS for Enhanced UI -->
    <style>
        .card {
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .form-control, .form-control[readonly] {
            border-color: #ced4da;
            background-color: #e9ecef;
            transition: border-color 0.2s ease;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 5px rgba(13, 110, 253, 0.3);
        }
        .btn-primary, .btn-danger {
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .table-hover tbody tr:hover {
            background-color: #e9ecef;
        }
        .alert {
            border-radius: 0.5rem;
        }
    </style>

    <!-- JavaScript for Edit/Delete Functionality -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            // Open edit modal
            $('.edit-gl-btn').click(function () {
                let glId = $(this).data('gl-id');
                let description = $(this).data('description');
                $('#editGlForm').attr('action', '{{ url("budget/edit-gl") }}/' + glId);
                $('#editDescription').val(description);
                $('#editGlModal').modal('show');
            });

            // Handle form submission
            $('#editGlForm').submit(function (e) {
                e.preventDefault();
                let form = $(this);
                let saveButton = $('#saveGlButton');
                saveButton.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        location.reload(); // Reload to show success message and updated table
                    },
                    error: function (xhr) {
                        saveButton.prop('disabled', false).text('Save');
                        let errorMessage = xhr.responseJSON?.errors?.Description || 'Failed to update GL attachment';
                        alert(errorMessage);
                    }
                });
            });

            // Handle delete with confirmation
            $('.delete-gl-btn').click(function () {
                let glId = $(this).data('gl-id');
                if (confirm('Are you sure you want to delete this GL attachment?')) {
                    $.ajax({
                        url: '{{ url("budget/delete-gl") }}/' + glId,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            location.reload(); // Reload to show success message and updated table
                        },
                        error: function (xhr) {
                            let errorMessage = xhr.responseJSON?.message || 'Failed to delete GL attachment';
                            alert(errorMessage);
                        }
                    });
                }
            });
        });
    </script>
@endsection