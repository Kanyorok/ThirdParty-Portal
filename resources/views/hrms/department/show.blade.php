<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div class="d-flex flex-column" style="height: 75%">
    <h2 class="text-center text-decoration-underline">{{ \Illuminate\Support\Str::upper($department->DepartmentID) }}</h2>
    <ul class="list-group list-group-flush">
        <li class="list-group-item">Name : <b class="float-end">{{ $department->Name }}</b></li>
        <li class="list-group-item">Description : <b class="float-end">{{ $department->Description ?? 'No Description' }}</b></li>
    </ul>
</div>
<div class="m-auto">
    <div class="m-auto">
        @include('snippets.behind_scenes',['model'=>$department])
        <p class="mb-0">actions</p>
        <hr class="mt-0">
        <div class="form-buttons- row">
            <div class="col-md-6">
                <button class="btn btn-primary w-100"
                        onclick="triggerUpdateDepartment()"
                        type="button"><i
                        class="fas fa-edit"></i> update
                </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-danger w-100"
                        onclick="triggerTrashDepartment()"
                        type="button"><i
                        class="fas fa-trash-alt"></i> remove
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="departmentActionModel" tabindex="-1" role="dialog" aria-hidden="true"
     data-bs-backdrop="false" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="updateDepartmentModal">
                    <form action="{{ route('departments.update', [$department->DepartmentID]) }}" method="post"
                          id="updateDepartmentForm"> @csrf
                        <div class="mb-3">@method('put')
                            <label class="form-label" for="Name">Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="Name" name="Name"
                                   required
                                   value="{{ $department->Name }}" placeholder="Department Name">
                            <p id="Name_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="Description">Description </label>
                            <textarea name="Description" id="Description" rows="3"
                                      class="form-control">{{ $department->Description }}</textarea>
                            <p id="Description_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-info" for="updateHeadId">Head of Department (HOD)</label>
                            <select class="form-control" id="updateHeadId" name="HeadId">
                                <option value="">Select HOD</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->Id }}" {{ $department->HeadId == $user->Id ? 'selected' : '' }}>{{ $user->Name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select the employee who will head this department</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="updateDeputyHeadId">Deputy Head of Department</label>
                            <select class="form-control" id="updateDeputyHeadId" name="DeputyHeadId">
                                <option value="">Select Deputy HOD</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->Id }}" {{ $department->DeputyHeadId == $user->Id ? 'selected' : '' }}>{{ $user->Name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Optional: Select the deputy head for this department</small>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="updateDepartmentBtn" type="submit"><i
                                    class="fas fa-plus-circle"></i> update Department
                            </button>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content text-center with-gradient d-none modal-item" id="trashDepartmentModal">
                    <p class="text-danger h4">
                        Trash Department <b>{{ $department->Name }}</b>
                    </p>
                    <div class="mt-2 mb-2">
                        Are you sure you want to trash this Department?
                    </div>
                    <hr>
                    <form id="trashDepartmentForm"
                          action="{{ route('departments.destroy', [$department->DepartmentID]) }}"
                          method="post"> @csrf
                        <div class="mt-4">@method('delete')
                            <button type="button" class="btn btn-success float-start"
                                    data-bs-dismiss="modal">
                                no, keep
                            </button>
                            <button class="btn btn-danger float-end" id="trashDepartmentBtn" type="submit"><i
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
        $('#updateHeadId').select2({ dropdownParent: $('#departmentActionModel'), placeholder: 'Select HOD', allowClear: true });
        $('#updateDeputyHeadId').select2({ dropdownParent: $('#departmentActionModel'), placeholder: 'Select Deputy HOD', allowClear: true });

        $('form#trashDepartmentForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#trashDepartmentBtn'), false, true, true)) {
                $("#departmentActionModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchDepartmentsTable === "function") {
                    fetchDepartmentsTable();
                }
            }
        });

        $('form#updateDepartmentForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateDepartmentBtn'), false, true, true, true)) {
                $("#departmentActionModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchDepartmentsTable === "function") {
                    fetchDepartmentsTable();
                }
            }
        });
    });

    function triggerTrashDepartment() {
        $(".modal-item").addClass('d-none');
        $('#trashDepartmentModal').removeClass('d-none');
        $('.modal-title').html('trash Department');
        $("#departmentActionModel").modal('show');
    }

    function triggerUpdateDepartment() {
        $(".modal-item").addClass('d-none');
        $('#updateDepartmentModal').removeClass('d-none');
        $('.modal-title').html('update Department');
        $("#departmentActionModel").modal('show');
    }
</script>
