@extends('layouts.app')
@section('title', 'Approval Configuration')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="mb-3">
    <button class="btn btn-primary float-end modal-create-approval" type="button">
        <i class="fas fa-plus-circle"></i> New Approval Group
    </button>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <table class="table table-bordered w-100 table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Document Type</th>
                        <th>Approval Type</th>
                        <th>Permission</th>
                        <th>Created By</th>
                        <th>Created On</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($approvalGroups as $group)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $group->DocType }}</td>
                            <td>{{ $group->ApprovalType }}</td>
                            <td>{{ $group->permission_name ?? 'N/A' }}</td>
                            <td>{{ $group->CreatedBy }}</td>
                            <td>{{ \Carbon\Carbon::parse($group->CreatedOn)->format('d-m-Y H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-info btn-edit" 
                                        data-id="{{ $group->Id }}" 
                                        data-doc="{{ $group->DocType }}"
                                        data-type="{{ $group->ApprovalType }}"
                                        data-permission="{{ $group->Permission }}">
                                    ✏️ Edit
                                </button>

                                <form method="POST" action="{{ route('approval-setup.destroy', $group->Id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this approval group?')">🗑️ Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
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
                    <h5 class="modal-title">New Approval Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Document Type</label>
                        <select name="DocType" id="DocType" class="form-control" required>
                            <option value="purchase_requisition">Purchase Requisition</option>
                            <option value="purchase_order">Purchase Order</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Approval Type</label>
                        <select name="ApprovalType" id="ApprovalType" class="form-control" required>
                            <option value="ANY">Any</option>
                            <option value="ALL">All</option>
                            <option value="MAJ">Majority</option>
                            <option value="AMT">Amount Based</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Permission</label>
                        <select name="Permission" id="PermissionSelect" class="form-control select2" required>
                            <option value="">Select Permission</option>
                            @foreach($permissions as $permission)
                                <option value="{{ $permission->id }}">{{ $permission->name }}</option>
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
    // Pass the route with a placeholder to JS
    const updateRouteTemplate = "{{ route('approval-setup.update', ['id' => '__ID__']) }}";

    $(function () {
        const $modal = $('#ApprovalModal');
        const $form = $('#approvalGroupForm');
        const $submitBtn = $('#modalSubmitBtn');

        $('#PermissionSelect').select2({
            dropdownParent: $modal,
            width: '100%',
            placeholder: "Search permission...",
            allowClear: true
        });

        // Create new approval group
        $('.modal-create-approval').on('click', function () {
            $form.attr('action', "{{ route('approval-settings.store') }}");
            $('#formMethod').val('POST');
            $('.modal-title').text('New Approval Group');
            $form[0].reset();
            $('#PermissionSelect').val('').trigger('change');
            $modal.modal('show');
        });

        // Edit approval group
        $('.btn-edit').on('click', function () {
            const id = $(this).data('id');
            const docType = $(this).data('doc');
            const approvalType = $(this).data('type');
            const permissionId = $(this).data('permission');

            // Replace __ID__ in the named route
            const updateRoute = updateRouteTemplate.replace('__ID__', id);

            $form.attr('action', updateRoute);
            $('#formMethod').val('PUT');
            $('.modal-title').text('Edit Approval Group');
            $('#DocType').val(docType);
            $('#ApprovalType').val(approvalType);
            $('#PermissionSelect').val(permissionId).trigger('change');

            $modal.modal('show');
        });
    });
</script>
@endsection
