<div>
    <form action="{{ route('users.store') }}" method="post" id="createUserForm"> @csrf
        <div class="mb-3">
            <label for="Employee" class="form-label">Employee <span class="text-danger">*</span></label>
            <select class="form-control  w-100" name="Employee" id="Employee" required>
                <option selected disabled>Select an Employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->EmployeeNo }}">{{ $employee->full_name }} ({{ $employee->EmployeeNo }}
                        )
                    </option>
                @endforeach
            </select>
            <p id="Employee_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="Role" class="form-label">Role <span class="text-danger">*</span></label>
            <select class="form-control  w-100" name="Role" id="Role" required>
                <option selected disabled>Select a Role</option>
                @foreach($Roles as $Role)
                    <option value="{{ $Role->id }}">{{ $Role->name }}</option>
                @endforeach
            </select>
            <p id="Role_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label>Branch</label>
            <select name="BranchId" class="form-control select2">
                <option disabled selected>Select Branch</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                @endforeach
            </select>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    onclick="window.bsOffcanvas.hide();">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="createUserBtn" type="submit"><i
                    class="fas fa-save"></i> add User
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#UserRole').select2();
        $('form#createUserForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createUserBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchUsersTable === "function") {
                    fetchUsersTable();
                }
            }
        });
    });
</script>
