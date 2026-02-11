<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div>
    <form action="{{ route('departments.store') }}" method="post" id="createDepartmentForm"> @csrf
        <div class="col-12 mb-3">
            <label class="form-label" for="Name">Department Name <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Name" name="Name" required
                   placeholder="Department Name">
            <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="col-12 mb-3">
            <label class="form-label" for="Description">Description</label>
            <textarea class="form-control" id="Description" name="Description"
                      placeholder="Department Description"></textarea>
            <p id="Description_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="col-12 mb-3">
            <label class="form-label text-info" for="createHeadId">Head of Department (HOD)</label>
            <select class="form-control" id="createHeadId" name="HeadId">
                <option value="">Select HOD</option>
                @foreach($users as $user)
                    <option value="{{ $user->Id }}">{{ $user->Name }}</option>
                @endforeach
            </select>
            <small class="text-muted">Select the employee who will head this department</small>
            <p id="HeadId_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="col-12 mb-3">
            <label class="form-label" for="createDeputyHeadId">Deputy Head of Department</label>
            <select class="form-control" id="createDeputyHeadId" name="DeputyHeadId">
                <option value="">Select Deputy HOD</option>
                @foreach($users as $user)
                    <option value="{{ $user->Id }}">{{ $user->Name }}</option>
                @endforeach
            </select>
            <small class="text-muted">Optional: Select the deputy head for this department</small>
            <p id="DeputyHeadId_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mt-2">
            <button type="button" class="btn btn-secondary float-start"
                    onclick="window.bsOffcanvas.hide();">
                Cancel
            </button>
            <button class="btn btn-primary float-end" id="createDepartmentBtn" type="submit">
                <i class="fas fa-save"></i> Add Department
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#createHeadId').select2({ placeholder: 'Select HOD', allowClear: true });
        $('#createDeputyHeadId').select2({ placeholder: 'Select Deputy HOD', allowClear: true });

        $('form#createDepartmentForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createDepartmentBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchDepartmentsTable === "function") {
                    fetchDepartmentsTable();
                }
            }
        });
    });
</script>
