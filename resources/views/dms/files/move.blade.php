<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div class="d-flex flex-column" style="height: 85%; overflow-y: auto;">
    <h2 class="text-center">{{ $file->Name }}</h2>
    <h3>Current Repository : {{ $parent->Name }}</h3>
    <form action="{{ request()->url()  }}" method="post"
          id="changeRepositoryForm"> @csrf
        <div class="mb-3">
            <label class="form-label" for="MoveRepository">Destination Repo <span
                    class="text-danger">*</span></label>
            <select name="MoveRepository" id="MoveRepository" class="form-control" required>
                <option selected disabled>-- Select destination repo --</option>
                @foreach($repositories as $repository)
                    @if($repository->Id !== $parent->Id)
                        <option value="{{ $repository->RepositoryId }}">{{ $repository->Name }}</option>
                    @endif
                @endforeach
            </select>
            <p id="MoveRepository_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start" onclick="window.bsOffcanvas.hide();">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="changeRepositoryBtn" type="submit">
                <i
                    class="fas fa-save"></i> change repo
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#DocumentTags').select2();

        $('form#changeRepositoryForm').submit(async function (e) {
            e.preventDefault();
            const response = await saveForm($(this), $('#changeRepositoryBtn'), false, true, true);
            if (response) {
                $('#' + response.data.id).remove();
                window.bsOffcanvas.hide();
            }
        });

    });

</script>
