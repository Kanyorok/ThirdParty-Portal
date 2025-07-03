@php use App\Enums\Core\VisibilityEnum; @endphp
<div>
    <form action="{{ route('file-tags.store') }}" method="post" id="createFileTagForm">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Name" name="Name" required
                   placeholder="Name">
            <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mb-3">
            <label class="form-label" for="Visibility">Visibility <span class="text-danger">*</span></label>
            <select class="form-control" name="Visibility" id="Visibility" required>
                @foreach(VisibilityEnum::cases() as $Visibility)
                    <option
                        value="{{ $Visibility->value }}" {{ ($Visibility->value===VisibilityEnum::Public->value)?'selected':'' }}>{!! $Visibility->icon() !!} {{ $Visibility->description() }}</option>
                @endforeach
            </select>
            <p id="Visibility_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="Description">Description </label>
            <textarea name="Description" id="Description" rows="3" class="form-control"
                      maxlength="1000"></textarea>
            <p id="Description_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    data-bs-dismiss="modal">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="createFileTagBtn" type="submit"><i
                    class="fas fa-save"></i> add a tag
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('form#createFileTagForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createFileTagBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchTagsTable === "function") {
                    fetchTagsTable();
                }
            }
        });
    });

</script>
