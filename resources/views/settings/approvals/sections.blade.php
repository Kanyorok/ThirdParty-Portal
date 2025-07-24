@extends('layouts.app')
@section('title', 'Approval Configuration')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="mb-3">
    <button class="btn btn-primary float-end modal-create-approval" type="button">
        <i class="fas fa-plus-circle"></i> New Approval WorkFlow
    </button>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th>#</th>
                            <th>Section Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvalGroups as $group)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $group->Name }}</td>
                            <td>{{ $group->Description }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-edit"
                                    data-id="{{ $group->Id }}"
                                    data-name="{{ $group->Name }}"
                                    data-description="{{ $group->Description }}"
                                    data-doc="{{ $group->DocType }}">
                                    ✏️ Edit
                                </button>
                                <form method="POST" action="{{ route('approval-setup.destroy', $group->Id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Delete this approval group?')">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @if($approvalGroups->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center text-muted">No approval workflows configured yet.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="ApprovalModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" id="approvalGroupForm">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Approval WorkFlow</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="Name">Name <span class="text-danger">*</span></label>
                        <input type="text" name="Name" id="Name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="Description">Description <span class="text-danger">*</span></label>
                        <textarea name="Description" id="Description" rows="3" class="form-control" maxlength="1000" required></textarea>
                        <p id="Description_error" class="invalid-feedback d-none error" role="alert"></p>
                    </div>

                    <div class="mb-3">
                        <label for="DocType">Document Type <span class="text-danger">*</span></label>
                        <select name="DocType" id="DocType" class="form-control select2" required>
                            <option value="">-- Select --</option>
                            @foreach($sourceOptions as $alias => $class)
                            <option value="{{ $class }}">{{ class_basename($class) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit" id="modalSubmitBtn">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const updateRouteTemplate = "{{ route('approval-setup.update', ['id' => '__ID__']) }}";

    $(function() {
        const $modal = $('#ApprovalModal');
        const $form = $('#approvalGroupForm');
        const $submitBtn = $('#modalSubmitBtn');

        $('#DocType').select2({
            dropdownParent: $modal,
            width: '100%',
            placeholder: "Search Document Type...",
            allowClear: true
        });

        // Create
        $('.modal-create-approval').on('click', function() {
            $form.attr('action', "{{ route('settings.approval_stages.store') }}");
            $('#formMethod').val('POST');
            $('.modal-title').text('New Approval WorkFlow');
            $form[0].reset();
            $('#DocType').val('').trigger('change');
            $modal.modal('show');
        });

        // Edit
        $('.btn-edit').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const description = $(this).data('description');
            const docType = $(this).data('doc');

            const updateRoute = updateRouteTemplate.replace('__ID__', id);
            $form.attr('action', updateRoute);
            $('#formMethod').val('PUT');
            $('.modal-title').text('Edit Approval WorkFlow');

            $('#Name').val(name);
            $('#Description').val(description);
            $('#DocType').val(docType).trigger('change');

            $modal.modal('show');
        });
    });
</script>
@endsection