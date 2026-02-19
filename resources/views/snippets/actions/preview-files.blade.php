<div class="modal fade" id="previewDocumentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient" id="previewDocumentContent"></div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $(document).on('click', '.modal-preview-document', function () {
            $('.modal-title').html('File: ' + $(this).attr('title'));
            $(".modal-item").addClass('d-none');
            $('#previewDocumentContent').removeClass('d-none')
                .html('<div class="text-center my-4"><div class="spinner-grow text-secondary me-2" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            $("#previewDocumentModal").modal('show');
            $.get($(this).data('url'), function (data) {
                $('#previewDocumentContent').html(data);
            }).fail(function (jqXHR) {
                nError(jqXHR.responseJSON.message);
                $("#previewDocumentModal").modal('hide');
            });
        });
    });
</script>
