@php use App\Enums\Core\RoleEnum; use App\Enums\Core\VisibilityEnum; @endphp
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div class="d-flex flex-column" style="height: 85%; overflow-y: auto;">
    <h2 class="text-center">{{ $file->Name }}</h2>
    <div class="alert alert-primary d-flex align-items-center" role="alert">
        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:">
            <use xlink:href="#info-fill"/>
        </svg>
        <div>
            <b>NOTE</b> only tags you have access to are listed here
        </div>
    </div>
    <form action="{{ route('document-tags.store',[$file->DocumentId]) }}" method="post"
          id="updateFileTagsForm"> @csrf
        <div class="mb-3">
            <label class="form-label" for="DocumentTags">Tags <span
                    class="text-danger">*</span></label>
            <select class="form-control" name="DocumentTags[]" id="DocumentTags" required multiple>
                @foreach($tags as $tag)
                    <option
                        value="{{ $tag->TagID }}" {{ (in_array($tag->Id, $file_tags, true))?'selected':'' }}> {{ $tag->Name }}</option>
                @endforeach
            </select>

            <p id="DocumentTags_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    data-bs-dismiss="modal">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="updateFileTagsBtn" type="submit">
                <i
                    class="fas fa-save"></i> update tags
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#DocumentTags').select2();

        $('form#updateFileTagsForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateFileTagsBtn'), true, true, true)) {
                window.bsOffcanvas.hide();
            }
        });


    });

</script>
