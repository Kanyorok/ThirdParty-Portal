<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div class="d-flex flex-column" style="height: 85%; overflow-y: auto;">
    <h4 class="text-center">{{ $file->Name }}</h4>
    <form action="{{ route('file-validation.store', [$file->DocumentId])  }}" method="post"
          id="requestDocumentValidationForm"> @csrf
        <div class="mb-3">
            <label class="form-label" for="ValidationType">Validation Type <span
                    class="text-danger">*</span></label>
            <select name="ValidationType" id="ValidationType" class="form-control" required>
                <option selected disabled>-- Select a type --</option>
                @foreach($types as $type)
                    <option value="{{ $type->ValidationTypeId }}">
                        {{ $type->Name }}
                    </option>
                @endforeach
            </select>
            <p id="ValidationType_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start" onclick="window.bsOffcanvas.hide();">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="requestDocumentValidationBtn" type="submit">
                <i
                    class="fas fa-save"></i> submit request
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#ValidationType').select2({
            placeholder: "Select Validation Types",
            dropdownParent: $("#offcanvasMain"),
        });
        $('form#requestDocumentValidationForm').submit(async function (e) {
            e.preventDefault();
            const response = await saveForm($(this), $('#requestDocumentValidationBtn'), false, true, true);
            if (response) {
                window.bsOffcanvas.hide();
            }
        });

    });

</script>
