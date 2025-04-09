<div class="modal fade" id="notesActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="createNoteModal">
                    <form method="post" id="createNoteForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="party_note">Notes <span class="text-danger">*</span>
                            </label>
                            <textarea name="party_note" id="party_note" class="form-control" required rows="4"
                                      maxlength="5000" minlength="2"></textarea>
                            <p id="party_note_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="createNoteBtn" type="submit"><i
                                    class="fas fa-plus-circle"></i> add note
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
        $(document).on('click', '.add-party-notes-btn', function () {
            $(".modal-item").addClass('d-none');
            $('#createNoteModal').removeClass('d-none');
            $('#createNoteForm').attr('action', $(this).data('action'));
            $('.modal-title').html('Add a note.');
            $("#notesActionsModal").modal('show');
        });
        $('form#createNoteForm').submit(async function (e) {
            e.preventDefault();

            let response = await saveForm($(this), $('#createNoteBtn'), false, true, true);
            if (response) {
                if (typeof response.activity === "object" && typeof appendActivity === "function") {
                    appendActivity(response.activity);
                }
                if (typeof fetchNotesTable === "function") {
                    fetchNotesTable();
                }
                $("#notesActionsModal").modal('hide');
            }
        });
    });
</script>
