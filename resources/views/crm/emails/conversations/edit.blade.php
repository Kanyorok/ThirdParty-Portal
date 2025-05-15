<script src="{{ asset('assets/plugins/dropzone/dropzone.min.js') }}"></script>
<script src="{{ asset('assets/plugins/summernote/summernote-bs5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>

<form method="post" id="mailReplyContactForm" class="row" action="{{ route('emails.send-draft',[$email->EmailID]) }}">
    @csrf
    <input type="hidden" class="form-control" id="mail_subject" name="mail_subject" value="{{ $email->Subject }}">
    <p id="mail_subject_error" class="invalid-feedback d-none error col-12"
       role="alert"></p>
    <div class="mb-3 col-12">
        <label class="form-label" for="mail_cc">CC <small>Type Email or search users</small></label>
        <select class="form-control" id="mail_cc" name="mail_cc[]" multiple>
            @if(is_array($email->CC))
                @foreach($email->CC as $index=>$mail)
                    <option value="{{ $mail }}" selected>{{ $mail }}</option>
                @endforeach
            @endif
        </select>
        <p id="mail_cc_error" class="invalid-feedback d-none error col-12" role="alert"></p>
    </div>
    <div class="mb-2 col-12">
        <label class="form-label" for="mail_content">Content <span
                class="text-danger">*</span></label> &nbsp;
        <span id="mail_content_error" class="invalid-feedback d-none error col-12"
              role="alert"></span>
        <textarea name="mail_content" id="mail_content" class="form-control" rows="4"
                  maxlength="5000" minlength="2">{!! $email->Body !!}</textarea>
    </div>
    <div id="emailAttachmentsContent">
        @foreach($email->attachments()->get(['t_Images.ImageID','MIMEType','Name']) as $document)
            <span class="btn btn-outline-info modal-preview-document"
                  title="{{ $document->Name }}"
                  data-url="{{ route('documents.show',[$document->ImageID]) }}"
                  id="document-{{ $document->ImageID }}">
                  {!! $document->ext()?->getIcon() !!} {{ \Illuminate\Support\Str::limit(explode(".",$document->Name)[0],10,'...') }} {!! $document->ext()?->value !!}
            </span>
        @endforeach
    </div>
    <div class="mx-2">
        <button type="button" class="btn btn-danger float-end mx-1 trash-email" id="cancel-mail-reply-btn"
                data-route="{{ route('emails.destroy',$email->EmailID) }}"
        ><i class="fas fa-trash-alt"></i>
            trash
        </button>
        <button class="btn btn-primary float-end mx-1" id="mailReplyContactBtn" type="submit"><i
                class="fas fa-plane-departure"></i> send
        </button>
        <button type="button" class="btn btn-secondary mx-2 float-end" id="action-file-upload"><i
                class="fas fa-paperclip"></i>&nbsp;
        </button>
    </div>
    <div class="clearfix"></div>

</form>
<div class="d-none" id="uploadCard">
    <div class="card-header pb-1 ">
        <h3 class="card-title">Attach files
            <span class="float-end" style="cursor: pointer;" id="uploadCardClose"><i
                    class="fas fa-times"></i></span>
        </h3>
    </div>
    <div class="p-0 border border-top">
        <form action="{{ route('email.attachment',[$email->EmailID]) }}" class="dropzone" id="upload-form">@csrf</form>
    </div>
</div>

<script>
    const AttachmentDropZone = new Dropzone("#upload-form", {
        maxFilesize: 9,//Mb
        acceptedFiles: "{{ implode(", ",\App\Enums\Core\ExtensionsEnum::getAllMimeTypes()) }}",
        success: function (file, response) {
            file.previewElement.remove();
            $('#emailAttachmentsContent').append(response.html);
        }
    });

    $(function () {
        $(document).on('click', '#action-file-upload', function () {
            $(this).addClass('disabled');
            $("#uploadCard").removeClass('d-none');
        });

        $(document).on('click', '#uploadCardClose', function () {
            $("#uploadCard").addClass('d-none');
            $("#action-file-upload").removeClass('disabled');
        });

        $('textarea#mail_content').summernote({
            placeholder: '',
            // dialogsInBody: true,
            tabsize: 2,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture' /*,'video'*/]],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        $('#mail_cc').select2({
            placeholder: "Select users or type Emails", minimumInputLength: 2,
            // dropdownParent: $("#mailToActionsModal"),
            tags: true,
            //  allowClear: true,
            ajax: {
                url: '{!! route('users.select2') !!}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {q: $.trim(params.term)};
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {text: item.Name, id: item.UserID}
                        })
                    };
                },
                cache: true
            }
        });

        $('form#mailReplyContactForm').submit(async function (e) {
            e.preventDefault();
            let response = await saveForm($(this), $('#mailReplyContactBtn'), true, true, true);
            if (response) {
                console.log(response);
            }
        });
    });

</script>
