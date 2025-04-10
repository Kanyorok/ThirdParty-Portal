<div>
    <form action="{{ route('roles.update',[$role->id]) }}" method="post" id="updateRoleForm"> @csrf
        <div class="col-12 mb-3">@method('put')
            <label class="form-label" for="RoleName">Role Name <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="RoleName" name="RoleName" required
                   placeholder="RoleName" value="{{ $role->name }}">
            <p id="RoleName_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <p id="permissions_error" class="text-danger d-none error col-12" role="alert"></p>
        @foreach(\App\Enums\Core\PermissionEnum::display() as $per)
            <div class="row border-bottom">
                @foreach($per as $permission)
                    @if($loop->first)
                        <h5> {{ $permission->title() }}</h5>
                    @endif
                    <div class="col-sm-6 col-md-4  mb-3">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" id="{{ $permission->value }}"
                                   name="{{ $permission->value }}"
                                {{ in_array($permission->value, $permissions, true)?'checked':'' }}>
                            <label class="form-check-label"
                                   for="{{ $permission->value }}">{{ $permission->subName() }}</label>
                        </div>
                        <p id="{{ $permission->value }}_error"
                           class="invalid-feedback d-none error col-12"
                           role="alert"></p>
                    </div>
                @endforeach
            </div>
        @endforeach
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    onclick="window.bsOffcanvas.hide();">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="updateRoleBtn" type="submit"><i
                    class="fas fa-save"></i> update role
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('form#updateRoleForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateRoleBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchRolesTable === "function") {
                    fetchRolesTable();
                }
            }
        });
    });

</script>
