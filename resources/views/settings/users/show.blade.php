@extends('layouts.app')

@section('title')
    User details
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-xl-3">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Details</h5>
                </div>
                <div class="card-body text-center">
                    {!! $user->getImage('id="image_upload_preview" alt=".." class="img-fluid avatar-1 rounded-circle mb-2" width="128" height="128"') !!}
                    <h5 class="card-title mb-1">{{ $user->Name }} <a href="#" class="ml-2 click-summary-data"
                                                                     data-click_url="{{ route('users.edit',$user->UserID) }}"
                                                                     data-summary_title="Update {{ $user->Name }} details."><i
                                class="fas fa-edit"></i></a></h5>
                </div>
                <div class="card-body border-top">
                    <h5 class="h6 card-title">Contacts</h5>
                    <div class="text center">

                        @if(is_string($user->Phone) && strlen($user->Phone)>9)
                            <div class="btn-group">
                                <button type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" class="btn btn-link dropdown-toggle">
                                    {{ $user->Phone }}
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item disabled text-decoration-line-through"
                                       href="javascript:void(0)"><i class="fas fa-phone-alt"></i> Call</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item send-message-to-action" href="javascript:void(0)"
                                       data-info="{{ route('user-sms.store', [$user->UserID]) }}~{{ $user->Name }}~{{ $user->Phone }}">
                                        <i class="fas fa-message"></i> Message</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body border-top">
                    <h5 class="h6 card-title">Teams</h5>
                    @foreach($user->teams as $team)
                        <a href="{{ route('teams.show',$team->TeamID) }}"
                           class="badge bg-primary me-1 my-1">{{ $team->Name }}</a>
                    @endforeach
                </div>
                <div class="card-footer pt-0 border border-top">
                    <p>Notes</p>
                    <p>{{ $user->Notes }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-xl-9">
            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">Activities</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-2" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">Credentials</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-3" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">Attrition</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-4" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">Branch Role</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active m-2" id="tab-1" role="tabpanel">
                        <table id="activitiesTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead class="d-none">
                            <tr>
                                <th>Action</th>
                                <th>Dated</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="tab-2" role="tabpanel">
                        <div class="m-3 text-center">
                            @if($user->Linked)
                                <h4 class="my-2">Account linked with CBS !</h4>
                                <p class="lead">Kindly reset from there and then click below to sync password
                                    manually.</p>
                                <form id="passwordSyncUserForm" method="post"
                                      action="{{ route('users.password.sync',$user->UserID) }}"> @csrf
                                    <button class="btn btn-outline-primary w-50 my-3" id="passwordSyncUserBtn"
                                            type="submit"><i class="fas fa-sync"></i> sync password
                                    </button>
                                </form>
                            @else
                                <h4 class="my-2"> Password Recovery!</h4>
                                <p class="lead">Send password reset to user, using email in file. Click on the below
                                    button
                                    to send.</p>
                                <form id="passwordResetUserForm" method="post"
                                      action="{{ route('users.password.reset',$user->UserID) }}"> @csrf
                                    <button class="btn btn-outline-primary w-50 my-3" id="passwordResetUserBtn"
                                            type="submit"><i class="fas fa-history"></i> send reset link
                                    </button>
                                </form>
                            @endif

                        </div>
                    </div>
                    <div class="tab-pane" id="tab-3" role="tabpanel">
                        <div class="m-3 text-center">
                            <h4 class="my-2">Attrition</h4>
                            <p class="lead">You are about to delete this user account. Are you sure you wish to proceed
                                with this?</p>

                            <form id="trashUserForm" method="post"
                                  action="{{ route('users.destroy',$user->UserID) }}"> @csrf
                                <button class="btn btn-danger w-25 my-3" id="trashUserBtn"
                                        type="submit"><i class="fas fa-trash"></i> Delete User Account
                                </button>@method('DELETE')
                            </form>
                        </div>
                    </div>
                    <div class="tab-pane m-2" id="tab-4" role="tabpanel">
                        <h5 class="mb-3">Branch Role Assignments</h5>

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Branch</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($user->branchRoles as $assignment)
                                <tr>
                                    <td>{{ $assignment->branch->Name ?? '—' }}</td>
                                    <td>{{ $assignment->role->name ?? '—' }}</td>
                                    <td>
                                        @php $assignmentKey = $assignment->getKey() ?? $assignment->ModelRoleId ?? null; @endphp
                                        @if($assignmentKey)
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-branch-role-btn"
                                                    data-id="{{ $assignmentKey }}"
                                                    data-update-url="{{ route('user_roles.update_branch', ['modelRole' => $assignmentKey]) }}"
                                                    data-branch="{{ $assignment->BranchId }}"
                                                    data-role="{{ $assignment->role_id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="{{ route('user_roles.delete_branch', ['modelRole' => $assignmentKey]) }}" style="display:inline-block;" class="branch-role-delete">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        @else
                                            {{-- Render fallback forms that post composite keys so actions work even without ModelRoleId --}}
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-branch-role-btn"
                                                    data-id=""
                                                    data-update-url=""
                                                    data-branch="{{ $assignment->BranchId }}"
                                                    data-role="{{ $assignment->role_id }}"
                                                    data-model_id="{{ $assignment->model_id }}"
                                                    data-model_type="{{ $assignment->model_type }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="{{ route('user_roles.delete_branch_by_keys') }}" style="display:inline-block;" class="branch-role-delete">
                                                    @csrf
                                                    <input type="hidden" name="model_id" value="{{ $assignment->model_id }}">
                                                    <input type="hidden" name="model_type" value="{{ $assignment->model_type }}">
                                                    <input type="hidden" name="BranchId" value="{{ $assignment->BranchId }}">
                                                    <input type="hidden" name="role_id" value="{{ $assignment->role_id }}">
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <hr>

                        <form method="POST" action="{{ route('user_roles.store',$user->UserID)}}">
                            @csrf
                            {{-- Use numeric Id as model_id and the canonical primary key name for model_type --}}
                            <input type="hidden" name="model_id" value="{{ $user->Id }}">
                            <input type="hidden" name="model_type" value="{{ $user::getPrimaryKey() }}">

                            <div class="row">
                                <div class="col-md-6">
                                    <label>Branch</label>
                                    @php $assigned = $user->branchRoles->pluck('BranchId')->filter()->values()->toArray(); @endphp
                                    <select name="BranchId" class="form-control select2" id="branchSelect">
                                        <option disabled selected>Select Branch</option>
                                        @foreach($branches as $branch)
                                            @if(!in_array($branch->Id, $assigned))
                                                <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label>Role</label>
                                    <select name="role_id" class="form-control select2">
                                        <option disabled selected>Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 text-end mt-3">
                                    <button class="btn btn-primary">Assign Role to Branch</button>
                                </div>
                            </div>
                        </form>
                        <!-- Edit Branch Role Modal -->
                        <div class="modal fade" id="editBranchRoleModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Branch Role</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form id="editBranchRoleForm" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Branch</label>
                                                <select id="editBranchSelect" name="BranchId" class="form-control select2">
                                                    @foreach($branches as $branch)
                                                        <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Role</label>
                                                <select id="editRoleSelect" name="role_id" class="form-control select2">
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @include('snippets.actions.sms')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>let activitiesTable = null;
        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchActivitiesTable();

            $('form#passwordResetUserForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), $('#passwordResetUserBtn'), false, true, true);
            });
            $('form#passwordSyncUserForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), $('#passwordSyncUserBtn'), false, true, true);
            });
            $('form#trashUserForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), $('#trashUserBtn'), true, true, true);
            });

            // Initialize Select2
            // Ensure selects inside modal render above it
            $('.select2').select2({width: '100%'});
            $('#editBranchSelect, #editRoleSelect').select2({width: '100%', dropdownParent: $('#editBranchRoleModal')});

            // Edit branch role button handler
            $(document).on('click', '.edit-branch-role-btn', function () {
                const id = $(this).data('id');
                const branch = $(this).data('branch');
                const role = $(this).data('role');
                let updateUrl = $(this).data('update-url');

                // If updateUrl not present (fallback case), use composite-keys endpoint and we'll populate hidden inputs
                if (!updateUrl || updateUrl.length === 0) {
                    updateUrl = '{{ route('user_roles.update_branch_by_keys') }}';
                    $('#editBranchRoleForm').attr('action', updateUrl);
                    // ensure hidden inputs exist
                    if ($('#editBranchRoleForm input[name="model_id"]').length === 0) {
                        $('#editBranchRoleForm').append('<input type="hidden" name="model_id" value="">');
                        $('#editBranchRoleForm').append('<input type="hidden" name="model_type" value="">');
                    }
                    // remove method override so Laravel treats this as a POST to the specific endpoint
                    $('#editBranchRoleForm input[name="_method"]').remove();
                    // set composite key values from data attributes if present on the button
                    $('#editBranchRoleForm input[name="model_id"]').val($(this).data('model_id') || '');
                    $('#editBranchRoleForm input[name="model_type"]').val($(this).data('model_type') || '');
                } else {
                    $('#editBranchRoleForm').attr('action', updateUrl);
                    // remove any composite hidden inputs (keep form clean)
                    $('#editBranchRoleForm input[name="model_id"]').remove();
                    $('#editBranchRoleForm input[name="model_type"]').remove();
                    // ensure method override exists for route-model update (PATCH)
                    if ($('#editBranchRoleForm input[name="_method"]').length === 0) {
                        $('#editBranchRoleForm').append('<input type="hidden" name="_method" value="PATCH">');
                    } else {
                        $('#editBranchRoleForm input[name="_method"]').val('PATCH');
                    }
                }
                $('#editBranchSelect').val(branch).trigger('change');
                $('#editRoleSelect').val(role).trigger('change');
                var modal = new bootstrap.Modal(document.getElementById('editBranchRoleModal'));
                modal.show();
            });

            // Handle edit form submission via AJAX so we can show messages and update the UI without full reload
            $('#editBranchRoleForm').submit(async function (e) {
                e.preventDefault();
                const form = $(this);
                const action = form.attr('action');
                const data = form.serialize();
                try {
                    const res = await $.ajax({url: action, method: 'POST', data: data, dataType: 'json'});
                    nSuccess(res.message || 'Updated');
                    // reload page to reflect changes (simple, safe)
                    setTimeout(function () { window.location.reload(); }, 700);
                } catch (err) {
                    console.error('Edit error', err);
                    const msg = (err && err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : (err && err.responseText) ? err.responseText : 'Server error';
                    nError(msg);
                    // also call formRequest for validation handling
                    formRequest(err, true, true);
                }
            });

            // Intercept delete forms (both modelRole route and composite keys) to run via AJAX and show notification
            $(document).on('submit', 'form.branch-role-delete', async function (e) {
                e.preventDefault();
                const form = $(this);
                if (!confirm('⚠️ Are you sure you want to delete this assignment?')) return;
                try {
                    const res = await $.ajax({url: form.attr('action'), method: 'POST', data: form.serialize(), dataType: 'json'});
                    nSuccess(res.message || 'Deleted');
                    // remove the row from the table
                    form.closest('tr').fadeOut(200, function () { $(this).remove(); });
                } catch (err) {
                    // show server message if present
                    console.error('Delete error', err);
                    const msg = (err && err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : (err && err.responseText) ? err.responseText : 'Server error';
                    nError(msg);
                    formRequest(err, true, false);
                }
            });
        });

        function fetchActivitiesTable() {
            if (activitiesTable === null) {
                activitiesTable = $('#activitiesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[1, 'desc']],
                    dom: 'rtip',
                    ajax: {
                        url: "{{ route('user.activities',[$user->UserID]) }}",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'description', name: 'description'},
                        {data: 'created_at', name: 'created_at'},
                    ], "oLanguage": {
                        "sEmptyTable": "User has no activities"
                    }
                });

                activitiesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading activities.");
                    console.log(er);
                });
            } else {
                activitiesTable.ajax.reload();
            }
        }
    </script>
@endsection
