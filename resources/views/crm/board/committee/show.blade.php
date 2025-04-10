<div class="d-flex flex-column" style="height: 75%">
    <h2 class="text-center text-decoration-underline">{{ $committee->CommitteeID }}</h2>
    <ul class="list-group list-group-flush">
        <li class="list-group-item">Name : <b class="float-end">{{ $committee->Name }}</b></li>
        <li class="list-group-item">Members : <b class="float-end">{{  number_format($committee->members()->count()) }}</b></li>
        <li class="list-group-item">Notes <br> {{ $committee->Notes }}</li>
    </ul>
</div>
<div class="m-auto">
    <div class="m-auto">
        @include('snippets.behind_scenes',['model'=>$committee])
        <p class="mb-0">actions</p>
        <hr class="mt-0">
        <div class="form-buttons- row">
            <div class="col-md-6">
                <button class="btn btn-primary w-100"
                        onclick="triggerUpdateCommittee()"
                        type="button"><i
                        class="fas fa-edit"></i> update
                </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-danger w-100 "
                        onclick="triggerTrashCommittee()"
                        type="button"><i
                        class="fas fa-trash-alt"></i> remove
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="committeeActionModel" tabindex="-1" role="dialog" aria-hidden="true"
     data-bs-backdrop="false" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="updateCommitteeModal">
                    <form action="{{ route('committee.update',[$committee->CommitteeID]) }}" method="post"
                          id="updateCommitteeForm"> @csrf
                        <div class="mb-3">@method('put')
                            <label class="form-label" for="CommitteeName">Committee Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="CommitteeName" name="CommitteeName" required
                                   placeholder="Name" value="{{ $committee->Name }}">
                            <p id="CommitteeName_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="CommitteeNotes">Notes </label>
                            <textarea name="CommitteeNotes" id="CommitteeNotes" rows="3" class="form-control">{{ $committee->Notes }}</textarea>
                            <p id="CommitteeNotes_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="updateCommitteeBtn" type="submit"><i
                                    class="fas fa-plus-circle"></i> update Committee
                            </button>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content text-center with-gradient d-none modal-item" id="trashCommitteeModal">
                    <p class="text-danger h4">
                        Trash Committee <b>{{ $committee->Name }}</b>
                    </p>
                    <div class="mt-2 mb-2">
                        Are you sure you want to trash this Committee ?
                    </div>
                    <hr>
                    <form id="trashCommitteeForm"
                          action="{{ route('committee.destroy',[$committee->CommitteeID])  }}"
                          method="post"> @csrf
                        <div class="mt-4">@method('delete')
                            <button type="button" class="btn btn-success float-start"
                                    data-bs-dismiss="modal">
                                no, keep
                            </button>
                            <button class="btn btn-danger float-end" id="trashCommitteeBtn" type="submit"><i
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
        $('form#trashCommitteeForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#trashCommitteeBtn'), false, true, true)) {
                $("#committeeActionModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchCommitteesTable === "function") {
                    fetchCommitteesTable();
                }
            }
        });

        $('form#updateCommitteeForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateCommitteeBtn'), false, true, true, true)) {
                $("#committeeActionModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchCommitteesTable === "function") {
                    fetchCommitteesTable();
                }
            }
        });
    });

    function triggerTrashCommittee() {
        $(".modal-item").addClass('d-none');
        $('#trashCommitteeModal').removeClass('d-none');
        $('.modal-title').html('trash Committee.');
        $("#committeeActionModel").modal('show');
    }

    function triggerCompleteCommittee() {
        $('#completeCommitteeForm').submit()
    }

    function triggerUpdateCommittee() {
        $(".modal-item").addClass('d-none');
        $('#updateCommitteeModal').removeClass('d-none');
        $('.modal-title').html('update a Committee.');
        $("#committeeActionModel").modal('show');
    }
</script>
