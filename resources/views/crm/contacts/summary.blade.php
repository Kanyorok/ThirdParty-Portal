<div class="d-flex flex-column" style="height: 80%">
    @if($party instanceof \App\Models\BR\Client)
        @include('snippets.client_summary', ['client'=>$party])
    @elseif($party instanceof \App\Models\Lead)
        @include('snippets.lead_summary', ['lead'=>$party])
    @else
        <h3>Unknown party</h3>
    @endif
    <hr class="mx-0 my-2">
    <p class="mb-1 h2 text-center text-decoration-underline">{{ $contact->Label }}</p>
    <ul class="list-group list-group-flush">
        <li class="list-group-item">Email: <span class="float-end">{{ $contact->Email }}</span></li>
        <li class="list-group-item">Phone Number: <span class="float-end">{{ $contact->Phone }}</span></li>
    </ul>
    <hr class="mx-0 my-2">
    <p class="mb-1">Notes</p>
    <p class="justify-content-around">
        {{ $contact->Notes }}
    </p>
</div>
<div class="m-auto">
    @include('snippets.behind_scenes',['model'=>$contact])
    <p class="mb-0">actions</p>
    <hr class="mt-0">
    <div class="form-buttons- row">
        <div class="col-md-6">
            <button class="btn btn-primary w-100"
                    onclick="triggerUpdateContact()"
                    type="button"><i
                    class="fas fa-edit"></i> update
            </button>
        </div>
        <div class="col-md-6">
            <button class="btn btn-danger w-100 "
                    onclick="triggerTrashContact()"
                    type="button"><i
                    class="fas fa-trash-alt"></i> remove
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="contactActionsModel" tabindex="-1" role="dialog" aria-hidden="true"
     data-bs-backdrop="false" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="updateContactModal">
                    <form method="post" id="updateContactForm"
                          action="{{ route('contacts.update',[$contact->ContactID]) }}">
                        @csrf
                        <div class="mb-3">@method('put')
                            <label class="form-label" for="e_contact_label">Label <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="e_contact_label" name="contact_label" required
                                   value="{{ $contact->Label }}">
                            <p id="e_contact_label_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="e_contact_phone">Phone Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="e_contact_phone" name="contact_phone"
                                   placeholder="phone Number" required value="{{ $contact->Phone }}">
                            <p id="e_contact_phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="e_contact_email">Email </label>
                            <input type="text" class="form-control" id="e_contact_email" name="contact_email"
                                   placeholder="contact email" required value="{{ $contact->Email }}">
                            <p id="e_contact_email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="e_contact_note">Notes </label>
                            <textarea name="contact_note" id="e_contact_note" class="form-control" rows="4"
                                      maxlength="5000" minlength="2">{{ $contact->Notes }}</textarea>
                            <p id="e_contact_note_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="updateContactBtn" type="submit"><i
                                    class="fas fa-plus-circle"></i> update contact
                            </button>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content text-center with-gradient d-none modal-item" id="cancelContactModal">
                    <p class="text-danger h4">
                        Trash Contact <b>{{ $contact->Label }}</b>
                    </p>
                    <div class="mt-2 mb-2">
                        Are you sure you want to trash this contact ?
                    </div>
                    <hr>
                    <form id="cancelContactForm"
                          action="{{ route('contacts.destroy',[$contact->ContactID])  }}"
                          method="post"> @csrf
                        <div class="mt-4">@method('delete')
                            <button type="button" class="btn btn-success float-start"
                                    data-bs-dismiss="modal">
                                no, keep
                            </button>
                            <button class="btn btn-danger float-end" id="cancelContactBtn" type="submit"><i
                                    class="fas fa-trash"></i> yes, trash
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
        $('form#cancelContactForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#cancelContactBtn'), false, true, true)) {
                $("#contactActionsModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchContactsTable === "function") {
                    fetchContactsTable();
                }
            }
        });

        $('form#updateContactForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateContactBtn'), false, true, true, true)) {
                $("#contactActionsModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof fetchContactsTable === "function") {
                    fetchContactsTable();
                }
            }
        });
    });

    function triggerTrashContact() {
        $(".modal-item").addClass('d-none');
        $('#cancelContactModal').removeClass('d-none');
        $('.modal-title').html('trash contact.');
        $("#contactActionsModel").modal('show');
    }

    function triggerCompleteContact() {
        $('#completeContactForm').submit()
    }

    function triggerUpdateContact() {
        $(".modal-item").addClass('d-none');
        $('#updateContactModal').removeClass('d-none');
        $('.modal-title').html('update a contact.');
        $("#contactActionsModel").modal('show');
    }
</script>
