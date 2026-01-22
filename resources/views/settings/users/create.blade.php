<div>
    <form action="{{ route('users.store') }}" method="post" id="createUserForm"> @csrf
        <div class="mb-3">
            <label for="Employee" class="form-label">Employee <span class="text-danger">*</span></label>
            <select class="form-control w-100" name="Employee" id="Employee" required>
                <option selected disabled>Select an Employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->EmployeeNo }}" 
                            data-branch="{{ $employee->branch?->Name ?? 'No Branch' }}"
                            data-branch-id="{{ $employee->BranchID }}">
                        {{ $employee->full_name }} ({{ $employee->EmployeeNo }})
                    </option>
                @endforeach
            </select>
            <p id="Employee_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        
        <div class="mb-3" id="branchDisplay" style="display: none;">
            <label class="form-label">Branch</label>
            <input type="text" class="form-control bg-light" id="displayBranch" readonly disabled>
            <small class="form-text text-muted">Branch is automatically set from employee record</small>
        </div>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> The user account will be created using the employee's existing details (email, branch, etc.).
        </div>
        
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    onclick="window.bsOffcanvas.hide();">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="createUserBtn" type="submit">
                <i class="fas fa-save"></i> add User
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        // Show branch when employee is selected
        $('#Employee').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const branch = selectedOption.data('branch');
            
            if (branch) {
                $('#displayBranch').val(branch);
                $('#branchDisplay').show();
            } else {
                $('#branchDisplay').hide();
            }
        });
        
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
