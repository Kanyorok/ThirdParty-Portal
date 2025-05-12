<div>
    <form action="{{ route('users.store') }}" method="post" id="createUserForm"> @csrf
        <div class="mb-3">
            <label class="form-label" for="UserID">UserID <span
                        class="text-danger">*</span></label>
            <input type="text" class="form-control" id="UserID" name="UserID" required
                   style="text-transform: uppercase;"
                   placeholder="UserID">
            <p id="UserID_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        {{--   <div class="mb-3">
               <div class="form-check form-switch mt-3">
                   <input class="form-control form-check-input" type="checkbox" id="SyncAccount"
                          name="SyncAccount">
                   <label class="form-check-label" for="SyncAccount">Sync Account with CBS</label>
               </div>
               <p id="SyncAccount_error" class="invalid-feedback d-none error col-12"
                  role="alert"></p>
           </div>--}}
        <div class="mb-3">
            <label class="form-label" for="Name">Full Name <span
                        class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Name" name="Name" required
                   placeholder="Name">
            <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="Gender" class="form-label">Gender
                <span class="text-danger">*</span></label>
            <select class="form-control" name="Gender" id="Gender" required>
                <option selected disabled>select gender</option>
                @foreach(App\Enums\Employee\GenderEnum::getAll() as $gender)
                    <option value="{{ $gender->value }}">{{ $gender->name }}</option>
                @endforeach
            </select>
            <p id="Gender_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="Branch" class="form-label">Branch <span
                        class="text-danger">*</span></label>
            <select class="form-control" name="Branch" id="Branch" required>
                <option selected disabled>Select user Branch</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->BranchID }}">{{ $branch->Name }}</option>
                @endforeach
            </select>
            <p id="Branch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
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
            <label class="form-label" for="Phone">Phone Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Phone" required name="Phone" placeholder="Phone Number">
            <p id="Phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="Email">Email <span
                        class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Email" name="Email"
                   placeholder="Email">
            <p id="Email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="Notes">Notes </label>
            <textarea name="Notes" id="Notes" rows="3" class="form-control"></textarea>
            <p id="Notes_end_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
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
