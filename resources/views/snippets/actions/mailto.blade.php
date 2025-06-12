<link rel="stylesheet" href="{{ asset('assets/libs/summernote/summernote-bs5.min.css') }}">
<script src="{{ asset('assets/libs/summernote/summernote-bs5.min.js') }}"></script>
<div class="modal fade" id="mailToActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="mailToContactModal">
                    <form method="post" id="mailToContactForm" class="row">
                        @csrf
                        <input type="hidden" class="form-control" id="mail_reply_to" name="mail_reply_to" required>
                        <p id="mail_reply_to_error" class="d-none error col-12" role="alert"></p>
                        <div class="mb-3 col-12">
                            <label class="form-label" for="mail_to">To <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mail_to" name="mail_to" required>
                            <p id="mail_to_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-12">
                            <label class="form-label" for="mail_cc">CC <small>Search users or type email</small></label>
                            <select class="form-control" id="mail_cc" name="mail_cc[]" multiple></select>
                            <p id="mail_cc_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-12">
                            <label class="form-label" for="mail_subject">Subject <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mail_subject" name="mail_subject" required>
                            <p id="mail_subject_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-12 col-12">
                            <label class="form-label" for="mail_content">Content <span
                                    class="text-danger">*</span></label> &nbsp;
                            <span id="mail_content_error" class="invalid-feedback d-none error col-12"
                                  role="alert"></span>
                            <textarea name="mail_content" id="mail_content" class="form-control" rows="4"
                                      maxlength="5000" minlength="2"></textarea>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="mailToContactBtn" type="submit"><i
                                    class="fas fa-plane-departure"></i> send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('textarea#mail_content').summernote({
            placeholder: '',
            dialogsInBody: true,
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

        $(document).on('click', '.send-mail-to-action', function () {
            clearMailToForm();
            $(".modal-item").addClass('d-none');
            const stuff = $(this).data('info').split('~');
            $('.modal-title').html('Send an email to ' + stuff[1]);
            $("#deleteItem").html(stuff[1]);
            $('#mailToContactForm').attr('action', stuff[0]);
            $('#mail_to').val(stuff[2]).attr('readonly', 'readonly');
            $('#mailToContactModal').removeClass('d-none');
            $("#mailToActionsModal").modal('show');
        });

        $(document).on('click', '.reply-mail-to-action', function () {
            clearMailToForm();
            $(".modal-item").addClass('d-none');
            const stuff = $(this).data('info').split('~');
            $('.modal-title').html('Reply email to ' + stuff[0]);
            $('#mailToContactForm').attr('action', '{{ route('emails.store') }}');
            $('#mail_to').val(stuff[0]).attr('readonly', 'readonly');
            $('#mail_reply_to').val(stuff[3]);
            $('#mail_subject').val('Re: ' + stuff[1]);
            $('#mail_content').summernote('code', '<br><br><hr>' + stuff[2]);
            $('#mailToContactModal').removeClass('d-none');
            $("#mailToActionsModal").modal('show');
        });

        $('form#mailToContactForm').submit(async function (e) {
            e.preventDefault();
            let response = await saveForm($(this), $('#mailToContactBtn'), false, true, true);
            if (response) {
                $("#mailToActionsModal").modal('hide');
                if (typeof response.activity === "object" && typeof appendActivity === "function") {
                    appendActivity(response.activity);
                }
                if (typeof fetchMailsTable === "function") {
                    fetchMailsTable();
                }
                if (typeof response.summary_url === "string" && typeof getConversationDetails === "function") {
                    getConversationDetails(response.summary_url);
                }
            }
        });

        $('#mail_cc').select2({
            placeholder: "Select users or type Emails", minimumInputLength: 2,
            dropdownParent: $("#mailToActionsModal"),
            tags: true,
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
    });

    function clearMailToForm() {
        $('#mail_to').val('');
        $('#mail_cc').val([]).trigger('change');
        $('#mail_subject').val('');
        $('#mail_content').summernote('code', '');
    }
</script>
