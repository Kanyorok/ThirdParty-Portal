<div class="d-flex flex-column" style="height: 75%; overflow-y: auto;">
    <p>{{ $repository->Description }}</p>
</div>
<div class="m-auto">
    <div class="m-auto">
        @include('snippets.behind_scenes',['model'=>$repository])
        <p class="mb-0">actions</p>
        <hr class="mt-0">
        <div class="form-buttons- row">
            <div class="col-md-6">
                <button class="btn btn-primary w-100"
                        onclick="triggerUpdateRepository()"
                        type="button"><i
                        class="fas fa-edit"></i> update
                </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-danger w-100 "
                        onclick="triggerTrashRepository()"
                        type="button"><i
                        class="fas fa-trash-alt"></i> remove
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="repositoryActionModel" tabindex="-1" role="dialog" aria-hidden="true"
     data-bs-backdrop="false" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="updateRepositoryModal">
                    <form action="{{ route('board.update',[$board->RepositoryID]) }}" method="post"
                          id="updateRepositoryForm"> @csrf
                        @method('put')

                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="updateRepositoryBtn" type="submit"><i
                                    class="fas fa-plus-circle"></i> update Board Member
                            </button>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content text-center with-gradient d-none modal-item" id="trashRepositoryModal">
                    <p class="text-danger h4">
                        Trash Board Member <b>{{ $board->Name }}</b>
                    </p>
                    <div class="mt-2 mb-2">
                        Are you sure you want to trash this Board Member ?
                    </div>
                    <hr>
                    <form id="trashRepositoryForm"
                          action="{{ route('board.destroy',[$board->RepositoryID])  }}"
                          method="post"> @csrf
                        <div class="mt-4">@method('delete')
                            <button type="button" class="btn btn-success float-start"
                                    data-bs-dismiss="modal">
                                no, keep
                            </button>
                            <button class="btn btn-danger float-end" id="trashRepositoryBtn" type="submit"><i
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

        $('form#trashRepositoryForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#trashRepositoryBtn'), false, true, true)) {
                $("#repositoryActionModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchRepositorysTable === "function") {
                    fetchRepositorysTable();
                }
            }
        });

        $('form#updateRepositoryForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateRepositoryBtn'), false, true, true, true)) {
                $("#repositoryActionModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchRepositorysTable === "function") {
                    fetchRepositorysTable();
                }
            }
        });
    });

    function triggerTrashRepository() {
        $(".modal-item").addClass('d-none');
        $('#trashRepositoryModal').removeClass('d-none');
        $('.modal-title').html('Trash repository.');
        $("#repositoryActionModel").modal('show');
    }

    function triggerUpdateRepository() {
        $(".modal-item").addClass('d-none');
        $('#updateRepositoryModal').removeClass('d-none');
        $('.modal-title').html('update repository.');
        $("#repositoryActionModel").modal('show');
    }
</script>
