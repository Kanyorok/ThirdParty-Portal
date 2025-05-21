<div>
    <form action="{{ route('employeescommittee.store') }}" method="post" id="createUserForm"> @csrf
        
    <div class="mb-3">
            <label for="Employee" class="form-label">Employee <span class="text-danger">*</span></label>
            <select class="form-control select2" name="Employee" id="Employee" required>
                <option selected disabled>Select an Employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->Id }}">{{ $employee->full_name }} ({{ $employee->Id }})</option>
                @endforeach
            </select>
            @error('Employee')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="Committee" class="form-label">Committee <span class="text-danger">*</span></label>
            <select class="form-control select2" name="Committee" id="Committee" required>
                <option selected disabled>Select a Committee</option>
                @foreach($committees as $committee)
                    <option value="{{ $committee->Id }}">{{ $committee->Name }}</option>
                @endforeach
            </select>
            @error('Committee')
                <div class="text-danger">{{ $message }}</div>
            @enderror
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
