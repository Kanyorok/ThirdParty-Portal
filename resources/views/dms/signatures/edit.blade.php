@php use App\Enums\Core\VisibilityEnum; @endphp
<script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
<div>
    <div class="progress mb-3 file-change">
        <div class="progress-bar progress-bar-striped progress-bar-animated"
             role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
             aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                Complete</small></div>
    </div>
    <form action="{{ route('document-signature.update',[$signature->SignatureId]) }}" method="post"
          id="updateDocumentSignatureForm"
          enctype="multipart/form-data" class="row">
        @csrf
        <div class="mb-2 col-12 text-center"> @method('PUT')
            @if($signature->image instanceof \App\Models\DMS\Document)
                {!! (new \App\Services\DMS\DocumentService($signature->image))->preview('id="image_upload_preview" alt=".." class="img-fluid" width="128" height="128"') !!}
            @else
                <img src="https://placehold.co/200x200?font=roboto&text=No+Image" id="image_upload_preview" alt=".."
                     class="img-fluid " width="128" height="128">
            @endif
            <input type="file" id="file" name="file" class="d-none"
                   accept="image/png">
            <label for="file" class="btn btn-primary"
                   type="button"><i
                    class="fas fa-image"></i><span> change image</span></label>
            <p id="file_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-2 col-12">
            <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" value="{{ $signature->Name }}" id="Name" name="Name" required
                   placeholder="Name">
            <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Visibility">Visibility <span class="text-danger">*</span></label>
            <select class="form-control" name="Visibility" id="Visibility" required>
                @foreach(VisibilityEnum::cases() as $Visibility)
                    <option
                        value="{{ $Visibility->value }}" {{ ($Visibility === $signature->Visibility)?'selected':'' }}>{!! $Visibility->icon() !!} {{ $Visibility->description() }}</option>
                @endforeach
            </select>
            <p id="Visibility_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Opacity"
                   title="A transparent object allows you to see clearly what is behind it.">Opacity <span
                    class="text-danger">*</span></label>
            <input type="number" class="form-control" id="Opacity" name="Opacity" required min="0" max="100"
                   value="{{ $signature->SignatureOpacity }}"
                   placeholder="Opacity">
            <p id="Opacity_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Horizontal">Horizontal Start <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="Horizontal" name="Horizontal" required
                   value="{{ $signature->SignatureHorizontalStart }}"
                   placeholder="Horizontal Start">
            <p id="Horizontal_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Vertical">Vertical Start <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="Vertical" name="Vertical" required
                   value="{{ $signature->SignatureVerticalStart }}"
                   placeholder="Vertical Start">
            <p id="Vertical_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Width">Width <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="Width" name="Width" required
                   value="{{ $signature->SignatureWidth }}"
                   placeholder="Width ">
            <p id="Width_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="Height">Height <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="Height" name="Height" required
                   value="{{ $signature->SignatureHeight }}"
                   placeholder="Height ">
            <p id="Height_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="col-12 mb-2">
            <label class="form-label" for="Content" title="#name# #userid# #datetime# #date#">Content
                <span class="text-danger">*</span> <code>#name#</code> <code>#userid#</code> <code>#datetime#</code>
                <code>#date#</code> </label>
            <input type="text" class="form-control" id="Content" name="Content" required value="#userid# #datetime#"
                   placeholder="Content" maxlength="50">
            <p id="Content_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-12 mb-2">
            <label class="form-label" for="ContentPosition">Content Position <span class="text-danger">*</span> </label>
            <select class="form-control" name="ContentPosition" id="ContentPosition" required>
                @foreach(\App\Enums\DMS\ImageGravityEnum::cases() as $ContentPosition)
                    <option
                        value="{{ $ContentPosition->value }}" {{ ($ContentPosition === $signature->ContentPosition)?'selected':'' }}> {{ $ContentPosition->description() }}</option>
                @endforeach
            </select>
            <p id="ContentPosition_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="ContentColour">Content Colour <span class="text-danger">*</span></label>
            <input type="color" class="form-control" id="ContentColour" name="ContentColour" required
                   value="{{ $signature->ContentColour }}"
                   placeholder="Content Colour ">
            <p id="ContentColour_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="ContentSize">Content Font Size <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="ContentSize" name="ContentSize" required
                   value="{{ $signature->ContentSize }}"
                   min="10" max="100" placeholder="Font Size">
            <p id="ContentSize_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="ContentBorderColour">Content Border Colour <span class="text-danger">*</span></label>
            <input type="color" class="form-control" id="ContentBorderColour" name="ContentBorderColour" required
                   value="{{ $signature->ContentBorderColour }}"
                   placeholder="Content Border Colour ">
            <p id="ContentBorderColour" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-6 mb-2">
            <label class="form-label" for="ContentBorderWeight">Content Border Weight<span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="ContentBorderWeight" name="ContentBorderWeight" required
                   value="{{ $signature->ContentBorderWeight }}" min="1" max="10"
                   placeholder="Content Border Weight">
            <p id="ContentBorderWeight_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>

        <div class="mb-3">
            <label class="form-label" for="Description">Description </label>
            <textarea name="Description" id="Description" rows="2" class="form-control"
                      maxlength="1000">{{ $signature->Description }}</textarea>
            <p id="Description_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start" onclick="window.bsOffcanvas.hide();">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="updateDocumentSignatureBtn" type="submit">
                <i class="fas fa-signature"></i> update signature
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#updateDocumentSignatureForm').on('submit', function (e) {
            e.preventDefault();
            const btn = $("#updateDocumentSignatureBtn");
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
                    window.setTimeout(function () {
                        window.location.replace(data.route);
                    }, 2000);
                },
                error: function (request) {
                    $("#progress-bar").width('0%');
                    formRequest(request, true)
                },
                complete: function () {
                    btn.prop('disabled', false).html(' <i class="fas fa-signature"></i> Update signature');
                }, resetForm: true
            });
        });
    });

</script>
