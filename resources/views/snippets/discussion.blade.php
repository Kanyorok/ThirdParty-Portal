<form action="{{ route('call.reschedule',[$schedule->ScheduleID]) }}" method="post"
      id="CallRescheduleForm">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="schedule_start">Next Start </label>
        <input type="text" class="form-control flatpickr-datetime" value="{{ \Carbon\Carbon::now()->addDay()->setHour(8)->setMinute(0)->format('Y-m-d H:i') }}" id="schedule_start" name="schedule_start" placeholder="Select start..">
        <p id="schedule_start_error" class="invalid-feedback d-none error col-12" role="alert"></p>
    </div>
    <div class="mb-3">
        <label class="form-label" for="schedule_discussion">Discussion <span class="text-danger">*</span> </label>
        <textarea name="schedule_discussion" id="schedule_discussion" class="form-control" rows="2" maxlength="5000" minlength="5">Client requested to be called later.</textarea>
        <p id="schedule_discussion_error" class="invalid-feedback d-none error col-12" role="alert"></p>
    </div>
    <div class="mb-3">
        <label class="form-label" for="schedule_notes">Private Notes </label>
        <textarea name="schedule_notes" id="schedule_notes" class="form-control" rows="2" maxlength="5000"></textarea>
        <p id="schedule_notes_error" class="invalid-feedback d-none error col-12" role="alert"></p>
    </div>
    <hr>
    <div class="mt-4">
        <button type="button" class="btn btn-secondary float-start"
                data-bs-dismiss="modal">
            cancel
        </button>
        <button class="btn btn-info float-end" id="CallRescheduleBtn" type="submit"><i
                class="fas fa-refresh"></i> reschedule
        </button>
    </div>
</form>
<script>
    $(function () {
       alert('here');
    });
</script>
