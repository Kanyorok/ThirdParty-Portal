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
