@extends('layouts.app')
@section('title', 'Approval Configuration')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Approval Configuration</h4>
    <button class="btn btn-primary modal-create-approval" type="button">
        <i class="fas fa-plus-circle"></i> New Approval WorkFlow
    </button>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-secondary">
                            <tr>
                                <th>#</th>
                                <th>Section Name</th>
                                <th>Description</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workFlowGroups as $group)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $group->Name }}</td>
                                <td>{{ $group->Description }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('settings.workflows.show', $group->Id) }}" class="btn btn-outline-secondary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-primary btn-edit"
                                            data-id="{{ $group->Id }}"
                                            data-name="{{ $group->Name }}"
                                            data-description="{{ $group->Description }}"
                                            data-doc="{{ $tableToAlias[$group->Source] ?? '' }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('settings.workflows.destroy', $group->Id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete"
                                                onclick="return confirm('Delete this approval group?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @if($workFlowGroups->isEmpty())
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
                            @foreach($labeledSourceOptions as $option)
                            <option value="{{ $option['alias'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="ModuleID">Module (Required for new sources)</label>
                        <select name="ModuleID" id="ModuleID" class="form-control select2">
                            <option value="">-- Select Module --</option>
                            @foreach($modules as $module)
                            <option value="{{ $module->ModuleID }}">{!! $module->indentation !!}{{ $module->Name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select the module this document belongs to. Required if not already configured.</small>
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
    const updateRouteTemplate = "{{ route('settings.workflows.update', ['id' => '__ID__']) }}";

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

        $('#ModuleID').select2({
            dropdownParent: $modal,
            width: '100%',
            placeholder: "Select Module...",
            allowClear: true
        });

        // Create
        $('.modal-create-approval').on('click', function() {
            $form.attr('action', "{{ route('settings.workflows.store') }}");
            $('#formMethod').val('POST');
            $('.modal-title').text('New Approval WorkFlow');
            $form[0].reset();
            $('#DocType').val('').trigger('change');
            $('#ModuleID').val('').trigger('change');
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