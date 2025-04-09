<script src='{{ asset('assets/plugins/moment/moment-with-locales.js') }}'></script>
<div class="modal fade" id="tasksActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="createTaskModal">
                    <form method="post" id="createTaskForm" class="row">
                        @csrf
                        <div class="mb-3 col-sm-6 col-12">
                            <label for="task_user" class="form-label">User <span
                                    class="text-danger">*</span></label>
                            <select class="form-control " name="task_user"
                                    id="task_user" required>
                                <option value="{{ auth()->user()->UserID }}"
                                        selected>{{  auth()->user()->Name }} - {{  auth()->user()->UserID }}</option>
                            </select>
                            <p id="task_user_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3 col-sm-6 col-12">
                            <label class="form-label" for="task_date">Due Date <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control flatpickr-datetime" id="task_date"
                                   name="task_date" placeholder="Select start.">
                            <p id="task_date_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3 col-12">
                            <label class="form-label" for="task_notes">Task <span class="text-danger">*</span>
                            </label>
                            <textarea name="task_notes" id="task_notes" class="form-control" required rows="4"
                                      maxlength="1000" minlength="2"></textarea>
                            <p id="task_notes_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>


                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="createTaskBtn" type="submit"><i
                                    class="fas fa-plus-circle"></i> add task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let schedule_task_date = null;
    $(function () {
        schedule_task_date = flatpickr("#task_date", {
            enableTime: false,
            altInput: true,
            minDate: moment().format('YYYY-MM-DD'),
            minuteIncrement: 1,
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
        });
        $('#task_user').select2({
            placeholder: "Select user to assign", minimumInputLength: 2,
            dropdownParent: $('#tasksActionsModal'),
            ajax: {
                url: '{{route('users.select2')}}',
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

        $(document).on('click', '.create-new-task', function () {
            $(".modal-item").addClass('d-none');
            $('#createTaskModal').removeClass('d-none');
            $('#createTaskForm').attr('action', $(this).data('action'));
            $('.modal-title').html('Add a task.');
            schedule_task_date.setDate(new Date(moment().format('YYYY-MM-DD')));
            $("#tasksActionsModal").modal('show');
        });

        $('form#createTaskForm').submit(async function (e) {
            e.preventDefault();
            let response = await saveForm($(this), $('#createTaskBtn'), false, true, true);
            if (response) {
                if (typeof response.activity === "object" && typeof appendActivity === "function") {
                    appendActivity(response.activity);
                }
                if (typeof response.activity === "object" && typeof appendAct === "function") {
                    appendAct($('#activitiesMain'), response.activity.html, true)
                }
                if (typeof fetchTasksTable === "function") {
                    fetchTasksTable();
                }
                $("#tasksActionsModal").modal('hide');
            }
        });
    });
</script>
