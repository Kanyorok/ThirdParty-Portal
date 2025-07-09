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
                                        type="submit"><i class="fas fa-trash"></i> trash
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
                                        <form method="POST" action="#">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <hr>

                    <form method="POST" action="{{ route('user_roles.store',$user->UserID)}}">
                        @csrf
                        <input type="hidden" name="model_id" value="{{ $user->UserID }}">
                        <input type="hidden" name="model_type" value="App\Models\User">

                        <div class="row">
                            <div class="col-md-6">
                                <label>Branch</label>
                                <select name="BranchId" class="form-control select2">
                                    <option disabled selected>Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
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
