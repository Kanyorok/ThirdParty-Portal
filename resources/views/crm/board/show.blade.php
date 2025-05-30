<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div class="d-flex flex-column" style="height: 75%">
    @include('snippets.client_summary', ['client' => $board->client])
    <h2 class="text-center text-decoration-underline">{{ $board->BoardMemberID }}</h2>
    <ul class="list-group list-group-flush">
        <li class="list-group-item">Name : <b class="float-end">{{ $board->Name }}</b></li>
        <li class="list-group-item">Phone No :
            @if(!empty($board->Phone) && Str::of($board->Phone)->length()>9)
                <div class="btn-group float-end">
                    <button type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="btn btn-link dropdown-toggle">
                        {{ $board->Phone }}
                    </button>
                    <div class="dropdown-menu" style="">
                        <a class="dropdown-item disabled text-decoration-line-through"
                           href="javascript:void(0)"><i class="fas fa-phone-alt"></i> Call</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item send-message-to-action" href="javascript:void(0)"
                           data-info="{{ route('board-sms.store', [$board->BoardMemberID]) }}~<b> Board Member</b> : {{ $board->Name }}~{{  $board->Phone }}">
                            <i class="fas fa-message"></i> Message</a>
                    </div>
                </div>
            @else
                <b class="float-end"> ? ?</b>
            @endif
        </li>
        <li class="list-group-item">Email : <b class="float-end">{{ $board->Email }}</b></li>
        <li class="list-group-item">Role : <b class="float-end">{{  $board->Role }}</b></li>
        <li class="list-group-item">Committees {!! implode('&nbsp;',$board->committees->map(function ($committee) {
                    return '<span class="badge rounded-pill bg-info">'.$committee->Name.'</span>';
                })->toArray()) !!}</li>
        <li class="list-group-item">Notes <br> {{ $board->Notes }}</li>
    </ul>
</div>
<div class="m-auto">
    <div class="m-auto">
        @include('snippets.behind_scenes',['model'=>$board])
        <p class="mb-0">actions</p>
        <hr class="mt-0">
        <div class="form-buttons- row">
            <div class="col-md-6">
                <button class="btn btn-primary w-100"
                        onclick="triggerUpdateBoardMember()"
                        type="button"><i
                        class="fas fa-edit"></i> update
                </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-danger w-100 "
                        onclick="triggerTrashBoardMember()"
                        type="button"><i
                        class="fas fa-trash-alt"></i> remove
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="boardMemberActionModel" tabindex="-1" role="dialog" aria-hidden="true"
     data-bs-backdrop="false" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="updateBoardMemberModal">
                    <form action="{{ route('board.update',[$board->BoardMemberID]) }}" method="post"
                          id="updateBoardMemberForm"> @csrf
                        <div class="mb-3">@method('put')
                            <label class="form-label" for="e_BoardMemberName">Full Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="e_BoardMemberName" name="BoardMemberName"
                                   required
                                   value="{{ $board->Name }}" placeholder="Name">
                            <p id="e_BoardMemberName_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="e_BoardMemberPhone">Phone Number </label>
                            <input type="text" class="form-control" id="e_BoardMemberPhone" name="BoardMemberPhone"
                                   placeholder="Phone Number" value="{{ $board->Phone }}">
                            <p id="e_BoardMemberPhone_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="e_BoardMemberEmail">Email <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="e_BoardMemberEmail" name="BoardMemberEmail"
                                   placeholder="Email" value="{{ $board->Email }}">
                            <p id="e_BoardMemberEmail_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label for="BoardCommittees" class="form-label">Committees <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" name="BoardCommittees[]" id="BoardCommittees" multiple
                                    required>
                                @foreach($committees as $committee)
                                    <option value="{{ $committee->CommitteeID }}"
                                        {{ in_array($committee->Id, $board->committees()->select('t_Committees.Id')->pluck('Id')->toArray(), true)?'selected':"" }}
                                    >{{ $committee->Name }}</option>
                                @endforeach
                            </select>
                            <p id="BoardCommittees_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="e_BoardMemberRole">Role </label>
                            <input type="text" class="form-control" id="e_BoardMemberRole" name="BoardMemberRole"
                                   placeholder="Role" value="{{ $board->Role }}">
                            <p id="e_BoardMemberRole_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="e_BoardMemberNotes">Notes </label>
                            <textarea name="BoardMemberNotes" id="e_BoardMemberNotes" rows="3"
                                      class="form-control">{{ $board->Notes }}</textarea>
                            <p id="e_BoardMemberNotes_end_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="updateBoardMemberBtn" type="submit"><i
                                    class="fas fa-plus-circle"></i> update Board Member
                            </button>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content text-center with-gradient d-none modal-item" id="trashBoardMemberModal">
                    <p class="text-danger h4">
                        Trash Board Member <b>{{ $board->Name }}</b>
                    </p>
                    <div class="mt-2 mb-2">
                        Are you sure you want to trash this Board Member ?
                    </div>
                    <hr>
                    <form id="trashBoardMemberForm"
                          action="{{ route('board.destroy',[$board->BoardMemberID])  }}"
                          method="post"> @csrf
                        <div class="mt-4">@method('delete')
                            <button type="button" class="btn btn-success float-start"
                                    data-bs-dismiss="modal">
                                no, keep
                            </button>
                            <button class="btn btn-danger float-end" id="trashBoardMemberBtn" type="submit"><i
                                    class="fas fa-trash"></i> yes, trash
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@include('snippets.actions.sms',['isBsOffcanvas'=>true])
<script>
    $(function () {
        $('#BoardCommittees').select2({
            dropdownParent: $("#boardMemberActionModel"),
        });

        $('form#trashBoardMemberForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#trashBoardMemberBtn'), false, true, true)) {
                $("#boardMemberActionModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchBoardMembersTable === "function") {
                    fetchBoardMembersTable();
                }
            }
        });

        $('form#updateBoardMemberForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateBoardMemberBtn'), false, true, true, true)) {
                $("#boardMemberActionModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchBoardMembersTable === "function") {
                    fetchBoardMembersTable();
                }
            }
        });
    });

    function triggerTrashBoardMember() {
        $(".modal-item").addClass('d-none');
        $('#trashBoardMemberModal').removeClass('d-none');
        $('.modal-title').html('trash Board Member.');
        $("#boardMemberActionModel").modal('show');
    }

    function triggerCompleteBoardMember() {
        $('#completeBoardMemberForm').submit()
    }

    function triggerUpdateBoardMember() {
        $(".modal-item").addClass('d-none');
        $('#updateBoardMemberModal').removeClass('d-none');
        $('.modal-title').html('update a Board Member.');
        $("#boardMemberActionModel").modal('show');
    }
</script>
