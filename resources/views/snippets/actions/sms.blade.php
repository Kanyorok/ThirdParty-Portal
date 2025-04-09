<div class="modal fade" id="messageToActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="messageToContactModal">
                    @if(isset($help))
                        {!! $help !!}
                    @endif
                    <form method="post" id="messageToContactForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="message_to">To <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="message_to" name="message_to" required>
                            <p id="message_to_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-12">
                            <label class="form-label" for="message_content">Content <span
                                    class="text-danger">*</span></label> &nbsp; <b class="float-end text-info"
                                                                                   id="msgCounter"></b>
                            <textarea name="message_content" id="message_content" class="form-control" rows="4"
                                      maxlength="5000" minlength="2"></textarea>
                            <p id="message_content_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="messageToContactBtn" type="submit"><i
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
    const maxLimitSMS = 168;
    let smsCount = 1;
    $(function () {
        $('#message_content').keyup(function () {
            const lengthCount = this.value.length;

            smsCount = parseInt((lengthCount / maxLimitSMS) + 1);

            {{--  if (lengthCount > maxLimit) {
                this.value = this.value.substring(0, maxLimit);
                smsCount = maxLimit - lengthCount + 1;
            } else {
                smsCount = maxLimit - lengthCount;
            }--}}


            $('#msgCounter').html(smsCount + " sms's");
        });

        $(document).on('click', '.send-message-to-action', function () {
            $(".modal-item").addClass('d-none');
            const stuff = $(this).data('info').split('~');
            $('.modal-title').html('Send an SMS to ' + stuff[1]);
            $('#messageToContactForm').attr('action', stuff[0]);
            $('#message_to').val(stuff[2]).attr('readonly', 'readonly');
            $('#messageToContactModal').removeClass('d-none');
            @if(isset($isBsOffcanvas) && $isBsOffcanvas===true)
            $("#messageToActionsModal").modal({
                backdrop: false,
                keyboard: false
            }).modal('show');
            @else
            $("#messageToActionsModal").modal('show');
            @endif

        });

        $('form#messageToContactForm').submit(async function (e) {
            e.preventDefault();
            let response = await saveForm($(this), $('#messageToContactBtn'), false, true, true);
            if (response) {
                if (typeof response.activity === "object" && typeof appendActivity === "function") {
                    appendActivity(response.activity);
                }
                if (typeof response.activity === "object" && typeof appendAct === "function") {
                    appendAct($('#activitiesMain'), response.activity.html, true)
                }
                if (typeof fetchSMSTable === "function") {
                    fetchSMSTable();
                }
                $("#messageToActionsModal").modal('hide');
            }
        });
    });
</script>
