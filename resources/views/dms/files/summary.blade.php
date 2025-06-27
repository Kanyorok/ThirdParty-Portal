@php use App\Enums\Core\RoleEnum; use App\Enums\Core\VisibilityEnum; @endphp
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div class="d-flex flex-column" style="height: 85%; overflow-y: auto;">
    <p><i class='fas fa-folder'></i> {{ $file->repository->Name }}</p>
    <h4 class="mb-3">Visibility: <b>{!! $file->Visibility->icon() !!}
            &nbsp; {{ $file->Visibility->name }}</b>
        <a href="javascript:void(0)" class="float-end edit-permission-visibility"
           onclick="triggerUpdateFileVisibility()"><i class="material-icons-two-tone"> edit</i></a></h4>
    <hr>
    <h4 class=" mb-3">Permissions
        <button class="btn btn-primary btn-sm float-end" onclick="triggerAddFilePermission()" type="button"><i
                class="fas fa-share"></i> share
        </button>
    </h4>
    <table id="filePermissionsTable" class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
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
        @include('snippets.behind_scenes',['model'=>$file])
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
<div class="modal fade" id="fileActionModal" tabindex="-1" role="dialog" aria-hidden="true"
     data-bs-backdrop="false" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="updateFileVisibilityModal">
                    <form action="{{ route('file.visibility',[$file->DocumentId]) }}" method="post"
                          id="updateFileVisibilityForm"> @csrf
                        @method('put')
                        <div class="mb-3">
                            <label class="form-label" for="visibility">Visibility <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" name="visibility" id="visibility" required>
                                @foreach(VisibilityEnum::cases() as $Visibility)
                                    <option
                                        value="{{ $Visibility->value }}" {{ ($Visibility->value===$file->Visibility->value)?'selected':'' }}>{!! $Visibility->icon() !!} {{ $Visibility->description() }}</option>
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
                            <button class="btn btn-primary float-end" id="updateFileVisibilityBtn" type="submit">
                                <i
                                    class="fas fa-save"></i> update Visibility
                            </button>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content with-gradient d-none modal-item" id="addFilePermissionModal">
                    <form action="{{ route('file-permissions.store',[$file->DocumentId]) }}" method="post"
                          id="addFilePermissionForm">
                        @csrf
                        <div class="mb-3">
                            <label for="share_role" class="form-label">Role <span
                                    class="text-danger">*</span></label>
                            <select class="form-control " name="share_role" id="share_role" required>
                                <option selected disabled>select a role.</option>
                                @foreach(RoleEnum::getAll() as $role)
                                    <option value="{{ $role->value }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <p id="share_role_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label for="share_party" class="form-label">User/Team </label>
                            <select class="form-control" name="share_party" id="share_party" required>
                            </select>
                            <p id="share_party_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-info float-end" id="addFilePermissionBtn" type="submit"><i
                                    class="fas fa-share-alt"></i> share
                            </button>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content text-center with-gradient d-none modal-item"
                     id="trashFilePermissionModal">
                    <h3 class="h3 text-danger">Remove Permission for <b id="trashFilePermission"></b>
                        from {{ $file->Name }} File.
                    </h3>
                    <div class="mt-2 mb-2">
                        Are you sure you want to remove this share ?
                    </div>
                    <hr>
                    <form id="trashFilePermissionForm" method="post"> @csrf
                        <div class="mt-4">@method('delete')
                            <button type="button" class="btn btn-success float-start"
                                    data-bs-dismiss="modal">
                                no, keep
                            </button>
                            <button class="btn btn-danger float-end" id="trashFilePermissionBtn"
                                    type="submit"><i
                                    class="fas fa-trash"></i> yes, remove
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
        fetchFilePermissionsTableTable()

        $('#share_party').select2({
            placeholder: "Search a user or team (t:)", minimumInputLength: 2,
            dropdownParent: $("#fileActionModal"),
            ajax: {
                url: '{!! route('users.select2',['with_teams'=>'rzr.co.ke']) !!}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {q: $.trim(params.term)};
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {text: item.Name, id: item.UserID}
                        })
                    };
                },
                cache: true
            }
        });

        $(document).on('click', '.share-permission-trash', function () {
            const name = $(this).data('info');
            $(".modal-item").addClass('d-none');
            $("#trashFilePermissionForm").attr('action', $(this).data('click_url'));
            $('#trashFilePermission').html(name);
            $('#trashFilePermissionModal').removeClass('d-none');
            $('.modal-title').html('<b>Remove</b> share : ' + name);
            $("#fileActionModal").modal('show');
        });

        $('form#addFilePermissionForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#addFilePermissionBtn'), false, true, true)) {
                $("#fileActionModal").modal('hide');
                fetchFilePermissionsTableTable();
            }
        });

        $('form#trashFilePermissionForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#trashFilePermissionBtn'), false, true, true)) {
                $("#fileActionModal").modal('hide');
                fetchFilePermissionsTableTable();
            }
        });

        $('form#updateFileVisibilityForm').submit(async function (e) {
            e.preventDefault();
            const response = await saveForm($(this), $('#updateFileVisibilityBtn'), false, true, true, true);
            if (response) {
                $("#fileActionModal").modal('hide');
                window.bsOffcanvas.hide();
                $('#' + response.data.id).remove();
                if (typeof appendFiles === "function") {
                    appendFiles(response.data);
                }
            }
        });
    });

    function fetchFilePermissionsTableTable() {
        if (!$.fn.DataTable.isDataTable('#filePermissionsTable')) {
            $('#filePermissionsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                dom: '<"row"<"col-12 mb-2"tr><"col-12"p>>',
                "order": [[3, 'desc']],
                ajax: {
                    url: '{{ route('file-permissions.index',[$file->DocumentId]) }}',
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
            $('#filePermissionsTable').DataTable().ajax.reload();
        }
    }

    function triggerAddFilePermission() {
        $('#share_party').val(null).change();
        $(".modal-item").addClass('d-none');
        $('#addFilePermissionModal').removeClass('d-none');
        $('.modal-title').html('SHARE: {{ $file->Name }}.');
        $("#fileActionModal").modal('show');
    }

    function triggerUpdateFileVisibility() {
        $(".modal-item").addClass('d-none');
        $('#updateFileVisibilityModal').removeClass('d-none');
        $('.modal-title').html('change {{ $file->Name }} visibility.');
        $("#fileActionModal").modal('show');
    }
</script>
