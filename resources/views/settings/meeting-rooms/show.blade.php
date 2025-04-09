<div class="d-flex flex-column" style="height: 75%">
    <h2 class="text-center text-decoration-underline">{{ $MeetingRoom->RoomID }}</h2>
    <p class="text-center">{{ $MeetingRoom->Name }}</p>
    <ul class="list-group list-group-flush">
        <li class="list-group-item">Capacity : <b class="float-end">{{ $MeetingRoom->Capacity }}</b></li>
        <li class="list-group-item">Branch : <b class="float-end">{{  $MeetingRoom->branch?->BranchName }}</b></li>
        <li class="list-group-item">Notes <br> {{ $MeetingRoom->Notes }}</li>
    </ul>

</div>
<div class="m-auto">
    <div class="m-auto">
        @include('snippets.behind_scenes',['model'=>$MeetingRoom])
        <p class="mb-0">actions</p>
        <hr class="mt-0">
        <div class="form-buttons- row">
            <div class="col-md-6">
                <button class="btn btn-primary w-100"
                        onclick="triggerUpdateMeetingRoom()"
                        type="button"><i
                        class="fas fa-edit"></i> update
                </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-danger w-100 "
                        onclick="triggerTrashMeetingRoom()"
                        type="button"><i
                        class="fas fa-trash-alt"></i> remove
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="MeetingRoomActionModel" tabindex="-1" role="dialog" aria-hidden="true"
     data-bs-backdrop="false" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="updateMeetingRoomModal">
                    <form action="{{ route('meeting-room.update',[$MeetingRoom->RoomID]) }}" method="post"
                          id="updateMeetingRoomForm"> @csrf
                        <div class="mb-3">@method('put')
                            <label class="form-label" for="RooMName">Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="RooMName" name="RooMName" required
                                   placeholder="Name" value="{{ $MeetingRoom->Name }}">
                            <p id="RooMName_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="RooMCapacity">Capacity <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="RooMCapacity" name="RooMCapacity"
                                   placeholder="Capacity" value="{{ $MeetingRoom->Capacity }}">
                            <p id="RooMCapacity_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label for="branches" class="form-label">Branches</label>
                            <select class="form-control" name="RooMBranch" id="RooMBranch">
                                <option value="">None</option>
                                @foreach($branches as $branch)
                                    <option {{ ($MeetingRoom->BranchId === $branch->OurBranchID)?'selected':'' }}
                                            value="{{ $branch->OurBranchID }}">{{ \Illuminate\Support\Str::title($branch->BranchName) }}</option>
                                @endforeach
                            </select>
                            <p id="RooMBranch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="RooMNotes">Notes </label>
                            <textarea name="RooMNotes" id="RooMNotes" rows="3"
                                      class="form-control">{{ $MeetingRoom->Notes }}</textarea>
                            <p id="RooMNotes_end_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="updateMeetingRoomBtn" type="submit"><i
                                    class="fas fa-plus-circle"></i> update Meeting Room
                            </button>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content text-center with-gradient d-none modal-item" id="trashMeetingRoomModal">
                    <p class="text-danger h4">
                        Trash Meeting Room <b>{{ $MeetingRoom->Name }}</b>
                    </p>
                    <div class="mt-2 mb-2">
                        Are you sure you want to trash this Meeting Room ?
                    </div>
                    <hr>
                    <form id="trashMeetingRoomForm"
                          action="{{ route('meeting-room.destroy',[$MeetingRoom->RoomID])  }}"
                          method="post"> @csrf
                        <div class="mt-4">@method('delete')
                            <button type="button" class="btn btn-success float-start"
                                    data-bs-dismiss="modal">
                                no, keep
                            </button>
                            <button class="btn btn-danger float-end" id="trashMeetingRoomBtn" type="submit"><i
                                    class="fas fa-trash"></i> yes, trash
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('form#trashMeetingRoomForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#trashMeetingRoomBtn'), false, true, true)) {
                $("#MeetingRoomActionModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchMeetingRoomsTable === "function") {
                    fetchMeetingRoomsTable();
                }
            }
        });

        $('form#updateMeetingRoomForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateMeetingRoomBtn'), false, true, true, true)) {
                $("#MeetingRoomActionModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchMeetingRoomsTable === "function") {
                    fetchMeetingRoomsTable();
                }
            }
        });
    });

    function triggerTrashMeetingRoom() {
        $(".modal-item").addClass('d-none');
        $('#trashMeetingRoomModal').removeClass('d-none');
        $('.modal-title').html('trash Meeting Room.');
        $("#MeetingRoomActionModel").modal('show');
    }

    function triggerCompleteMeetingRoom() {
        $('#completeMeetingRoomForm').submit()
    }

    function triggerUpdateMeetingRoom() {
        $(".modal-item").addClass('d-none');
        $('#updateMeetingRoomModal').removeClass('d-none');
        $('.modal-title').html('update a Meeting Room.');
        $("#MeetingRoomActionModel").modal('show');
    }
</script>
