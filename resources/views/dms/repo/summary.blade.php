@php use App\Enums\Core\VisibilityEnum; @endphp
<div class="d-flex flex-column" style="height: 85%; overflow-y: auto;">
    <p>{{ $repository->Description }}</p>
    <h4 class="mb-3">Visibility: <b>{!! $repository->Visibility->icon() !!}
            &nbsp; {{ $repository->Visibility->name }}</b>
        <a href="javascript:void(0)" class="float-end edit-permission-visibility"
           onclick="triggerUpdateRepositoryVisibility()"><i class="material-icons-two-tone"> edit</i></a></h4>
    <hr>
    <h4 class=" mb-3">Permissions
        <button class="btn btn-primary btn-sm float-end"><i class="fas fa-share"></i> share</button>
    </h4>
    <table id="repoPermissionsTable" class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
        <thead>
        <tr>
            <th>Party</th>
            <th>Role</th>
            <th>action</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<div class="m-auto">
    <div class="m-auto">
        @include('snippets.behind_scenes',['model'=>$repository])
        {{-- <p class="mb-0">actions</p>
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
         </div>--}}
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
                <div class="onboarding-content with-gradient d-none modal-item" id="updateRepositoryVisibilityModal">
                    <form action="{{ route('repo.visibility',[$repository->RepositoryId]) }}" method="post"
                          id="updateRepositoryVisibilityForm"> @csrf
                        @method('put')
                        <div class="mb-3">
                            <label class="form-label" for="repo_visibility">Visibility <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" name="visibility" id="repo_visibility" required>
                                @foreach(VisibilityEnum::cases() as $Visibility)
                                    <option
                                        value="{{ $Visibility->value }}" {{ ($Visibility->value===$repository->Visibility->value)?'selected':'' }}>{!! $Visibility->icon() !!} {{ $Visibility->description() }}</option>
                                @endforeach
                            </select>
                            <p id="visibility_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="updateRepositoryVisibilityBtn" type="submit">
                                <i
                                    class="fas fa-save"></i> update Visibility
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
        $.fn.dataTable.ext.errMode = 'none';
        fetchRepoPermissionsTableTable()

        $('form#updateRepositoryVisibilityForm').submit(async function (e) {
            e.preventDefault();
            const response = await saveForm($(this), $('#updateRepositoryVisibilityBtn'), false, true, true, true);
            if (response) {
                $("#repositoryActionModel").modal('hide');
                window.bsOffcanvas.hide();
                $('#' + response.data.id).remove();
                if (typeof _appendRepository === "function") {
                    _appendRepository(response.data);
                }
            }
        });
        /* $('form#trashRepositoryForm').submit(async function (e) {
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
         });*/
    });

    function fetchRepoPermissionsTableTable() {
        if (!$.fn.DataTable.isDataTable('#repoPermissionsTable')) {
            $('#repoPermissionsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                dom: '<"row"<"col-12 mb-2"tr><"col-12"p>>',
                "order": [[3, 'desc']],
                ajax: {
                    url: '{{ route('repo-permissions.index',[$repository->RepositoryId]) }}',
                    error: function (jqXHR) {
                        codeNotify(jqXHR.status);
                    }
                },
                columns: [
                    {data: 'party', name: 'party'},
                    {data: 'Role', name: 'Role'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ], "oLanguage": {
                    "sEmptyTable": "no permissions under this filter"
                }
            }).on('error', function () {
                nWarning("an issue occurred while loading permissions.");
                // console.log(er);
            });
        } else {
            $('#repoPermissionsTable').DataTable().ajax.reload();
        }
    }

    function triggerUpdateRepositoryVisibility() {
        $(".modal-item").addClass('d-none');
        $('#updateRepositoryVisibilityModal').removeClass('d-none');
        $('.modal-title').html('update repository visibility.');
        $("#repositoryActionModel").modal('show');
    }

    /*function triggerTrashRepository() {
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
    }*/
</script>
