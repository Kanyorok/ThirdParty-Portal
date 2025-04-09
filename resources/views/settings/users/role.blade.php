<div>
    @include('snippets.user_summary', ['user'=>$user])
    <hr class="mx-0 my-2">
    <form action="{{ route('user_roles.store',$user->UserID) }}" method="post" id="updateUserRoleForm"> @csrf
        <div class="mb-3">
            <label for="Role" class="form-label">Role </label>
            <select class="form-control form-control-lg w-100" name="Role" id="Role">
                @foreach($Roles as $Role)
                    @if(!$user->role() instanceof \Spatie\Permission\Models\Role)
                        <option disabled selected> select user role</option>
                    @endif
                    <option value="{{ $Role->id }}"
                        {{ ($Role->id ===  $user->role()?->id)?'selected':'' }}
                    >{{ $Role->name }}</option>
                @endforeach
            </select>
            <p id="Role_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button class="btn btn-primary float-end" id="updateUserRoleBtn" type="submit"><i
                    class="fas fa-save"></i>
                update Role
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#UserRole').select2();
        $('form#updateUserRoleForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateUserRoleBtn'), true, true, true, true)) {
                window.bsOffcanvas.hide();
            }
        });
    });

</script>
