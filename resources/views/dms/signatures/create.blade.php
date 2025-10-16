@php use App\Enums\Core\VisibilityEnum; @endphp
<script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
<div>
    <div class="progress mb-3 file-change">
        <div class="progress-bar progress-bar-striped progress-bar-animated"
             role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
             aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                Complete</small></div>
    </div>
    <form action="{{ route('document-signature.store') }}" method="post" id="createDocumentSignatureForm"
          enctype="multipart/form-data" class="row">
        @csrf
        <div class="mb-2 col-12">
            <label for="file" class="form-label">Select Image</label>
            <input type="file" class="form-control" id="file" name="file"
                   accept="image/png">
            <p id="file_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-2 col-12">
            <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Name" name="Name" required
                   placeholder="Name">
            <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Visibility">Visibility <span class="text-danger">*</span></label>
            <select class="form-control" name="Visibility" id="Visibility" required>
                @foreach(VisibilityEnum::cases() as $Visibility)
                    <option
                        value="{{ $Visibility->value }}" {{ ($Visibility->value===VisibilityEnum::Public->value)?'selected':'' }}>{!! $Visibility->icon() !!} {{ $Visibility->description() }}</option>
                @endforeach
            </select>
            <p id="Visibility_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Opacity"
                   title="A transparent object allows you to see clearly what is behind it.">Opacity <span
                    class="text-danger">*</span></label>
            <input type="number" class="form-control" id="Opacity" name="Opacity" required min="0" max="100" value="90"
                   placeholder="Opacity">
            <p id="Opacity_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Horizontal">Horizontal Start <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="Horizontal" name="Horizontal" required value="10"
                   placeholder="Horizontal Start">
            <p id="Horizontal_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Vertical">Vertical Start <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="Vertical" name="Vertical" required value="10"
                   placeholder="Vertical Start">
            <p id="Vertical_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Width">Width <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="Width" name="Width" required value="300"
                   placeholder="Width ">
            <p id="Width_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Height">Height <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="Height" name="Height" required value="500"
                   placeholder="Height ">
            <p id="Height_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="col-12 mb-2">
            <label class="form-label" for="Content" title="#name# #userid# #datetime# #date#">Content <span
                    class="text-danger">*</span> </label>
            <input type="text" class="form-control" id="Content" name="Content" required value="#userid# #datetime#"
                   placeholder="Content" maxlength="50">
            <p id="Content_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-12 mb-2">
            <label class="form-label" for="ContentPosition">Content Position <span class="text-danger">*</span> </label>
            <select class="form-control" name="ContentPosition" id="ContentPosition" required>
                @foreach(\App\Enums\DMS\ImageGravityEnum::cases() as $ContentPosition)
                    <option value="{{ $ContentPosition->value }}"> {{ $ContentPosition->description() }}</option>
                @endforeach
            </select>
            <p id="ContentPosition_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="ContentColour">Content Colour <span class="text-danger">*</span></label>
            <input type="color" class="form-control" id="ContentColour" name="ContentColour" required value="#ff6347"
                   placeholder="Content Colour ">
            <p id="ContentColour_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="ContentSize">Content Font Size <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="ContentSize" name="ContentSize" required value="28" min="10"
                   max="100"
                   placeholder="Font Size">
            <p id="ContentSize_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="ContentBorderColour">Content Border Colour <span class="text-danger">*</span></label>
            <input type="color" class="form-control" id="ContentBorderColour" name="ContentBorderColour" required
                   value="#1b1b1b"
                   placeholder="Content Border Colour ">
            <p id="ContentBorderColour" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="ContentBorderWeight">Content Border Weight<span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="ContentBorderWeight" name="ContentBorderWeight" required
                   value="1" min="1" max="10"
                   placeholder="Content Border Weight">
            <p id="ContentBorderWeight_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mb-3">
            <label class="form-label" for="Description">Description </label>
            <textarea name="Description" id="Description" rows="2" class="form-control"
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
            <button class="btn btn-primary float-end" id="createDocumentSignatureBtn" type="submit">
                <i class="fas fa-signature"></i> Add signature
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#createDocumentSignatureForm').on('submit', function (e) {
            e.preventDefault();
            const btn = $("#createDocumentSignatureBtn");
            $(this).ajaxSubmit({
                dataType: 'json', beforeSubmit: function () {
                    $("#progress-bar").width('0%');
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
                },
                uploadProgress: function (event, position, total, percentComplete) {
                    $("#progress-bar").width(percentComplete + '%').html('<small id="progress-status">' + percentComplete + ' % Complete</small>');
                },
                success: function (data) {
                    nSuccess(data.message);
                    window.bsOffcanvas.hide();
                    if (typeof fetchSignaturesTable === "function") {
                        fetchSignaturesTable();
                    }
                },
                error: function (request) {
                    formRequest(request, true)
                },
                complete: function () {
                    btn.prop('disabled', false).html(' <i class="fas fa-signature"></i> Add signature');
                }, resetForm: true
            });
        });
    });

</script>
