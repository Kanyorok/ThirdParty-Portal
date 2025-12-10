@php use App\Enums\Core\VisibilityEnum; @endphp
<div>
    <form action="{{ route('legal-hold.store') }}" method="post" id="createLegalHoldForm">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="Ref">Ref <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Ref" name="Ref" required
                   placeholder="Reference No. ">
            <p id="Ref_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Name" name="Name" required
                   placeholder="Name">
            <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mb-3">
            <label class="form-label" for="Tags">Tags <span class="text-danger">*</span></label>
            <select class="form-control" name="Tags[]" id="Tags" required multiple>
                @foreach($tags as $tag)
                    <option value="{{ $tag->TagID }}">{{ $tag->Name }}</option>
                @endforeach
            </select>
            <p id="Tags_error" class="invalid-feedback d-none error col-12" role="alert"></p>
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
            <button class="btn btn-primary float-end" id="createLegalHoldBtn" type="submit"><i
                    class="fas fa-save"></i> add a legal hold
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#Tags').select2();
        $('form#createLegalHoldForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createLegalHoldBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchLegalHoldsTable === "function") {
                    fetchLegalHoldsTable();
                }
            }
        });
    });

</script>
