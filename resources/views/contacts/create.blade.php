<div>
    <form method="post" id="createPartyContactForm" action="{{ $route }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="contact_label">Label <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="contact_label" name="contact_label" required >
            <p id="contact_label_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="contact_phone">Phone Number </label>
            <input type="text" class="form-control" id="contact_phone" name="contact_phone"
                   placeholder="phone Number" >
            <p id="contact_phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="contact_email">Email </label>
            <input type="text" class="form-control" id="contact_email" name="contact_email"
                   placeholder="contact email" value="{{ $email }}">
            <p id="contact_email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="contact_note">Notes </label>
            <textarea name="contact_note" id="contact_note" class="form-control" rows="4"
                      maxlength="5000" minlength="2"></textarea>
            <p id="contact_note_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <hr>
        <div class="mt-4 mb-3">
            <button type="button" class="btn btn-secondary float-start"
                   onclick="window.bsOffcanvas.hide();">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="createPartyContactBtn" type="submit"><i
                    class="fas fa-plus-circle"></i> add contact
            </button>
            <div class="clearfix"></div>
        </div>
    </form>
</div>
<script>
    $(function () {

        $('form#createPartyContactForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createPartyContactBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchContactsTable === "function") {
                    fetchContactsTable();
                }
                if (typeof emailPageRefresh === "function") {
                    emailPageRefresh();
                }
            }
        });
    });
</script>
