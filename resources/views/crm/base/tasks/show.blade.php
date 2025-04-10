<style>
    /* common */
    .ribbon {
        width: 150px;
        height: 150px;
        overflow: hidden;
        position: absolute;
    }

    .ribbon::before,
    .ribbon::after {
        position: absolute;
        z-index: -1;
        content: '';
        display: block;
        border: 5px solid #2980b9;
    }

    .ribbon-info span {
        background-color: rgba(var(--bs-info-rgb));
        color: #fff;
    }

    .ribbon-danger span {
        background-color: rgba(var(--bs-danger-rgb));
        color: #fff;
    }


    .ribbon span {
        position: absolute;
        display: block;
        width: 225px;
        padding: 15px 0;
        box-shadow: 0 5px 10px rgba(0, 0, 0, .1);
        font: 700 18px/1 'Lato', sans-serif;
        text-shadow: 0 1px 1px rgba(0, 0, 0, .2);
        text-transform: uppercase;
        text-align: center;
    }

    /* top left*/
    .ribbon-top-left {
        top: -10px;
        left: -10px;
    }

    .ribbon-top-left::before,
    .ribbon-top-left::after {
        border-top-color: transparent;
        border-left-color: transparent;
    }

    .ribbon-top-left::before {
        top: 0;
        right: 0;
    }

    .ribbon-top-left::after {
        bottom: 0;
        left: 0;
    }

    .ribbon-top-left span {
        right: -25px;
        top: 30px;
        transform: rotate(-45deg);
    }
</style>
<div class="d-flex flex-column" style="height: 80%">
    @if(!is_null($task->CompletedOn))
        <div class="ribbon ribbon-top-left ribbon-info"><span>completed</span></div>
    @elseif($service->isOverdue())
        <div class="ribbon ribbon-top-left ribbon-danger"><span>Overdue</span></div>
    @endif

    @if($party instanceof \App\Models\BR\Client)
        @include('snippets.client_summary', ['client'=>$party])
    @elseif($party instanceof \App\Models\Lead)
        @include('snippets.lead_summary', ['lead'=>$party])
    @else
        <h3>Unknown party</h3>
    @endif
    <ul class="list-group list-group-flush">
        <li class="list-group-item">Owner: <span
                class="float-end fw-bold">{{ $task->user?->Name }} ({{ $task->user?->UserID }})</span></li>
        <li class="list-group-item">Due On: <span class="float-end">{{ $task->Dated->format('M d, Y') }}</span></li>
        <li class="list-group-item">Source : <span
                class="float-end">{{ (new \App\Services\TaskService($task))->source() }}</span></li>
    </ul>
    <hr class="mx-0 my-2">
    <p class="mb-1 h4">Details</p>
    <p class="justify-content-around">
        {{ $task->Notes }}
    </p>
</div>
<div class="m-auto">
    @include('snippets.behind_scenes',['model'=>$task])
    <p class="mb-0">actions <small class="text-muted">Double click to complete or restore</small></p>
    <hr class="mt-0">
    <div class="form-buttons- row">
        <div class="col-md-4">
            <button class="btn btn-success w-100" id="completeTaskBtn"
                    @if($service->canClose(auth()->user()))
                        ondblclick="triggerCompleteTask()"
                    @else
                        disabled
                    @endif
                    type="button">
                @if(is_null($task->CompletedOn))
                    <i class="fas fa-check"></i> done
                @else
                    <i class="fas fa-history"></i> restore
                @endif
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100"
                    @if(!is_null($service->updateUrl()) && $service->canClose(auth()->user()))
                        onclick="triggerUpdateTask()"
                    @else
                        disabled
                    @endif
                    type="button"><i
                    class="fas fa-edit"></i> update
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-danger w-100 "
                    @if($service->canClose(auth()->user()))
                        onclick="triggerTrashTask()"
                    @else
                        disabled
                    @endif
                    type="button"><i
                    class="fas fa-trash-alt"></i> remove
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="taskActionsModel" tabindex="-1" role="dialog" aria-hidden="true"
     data-bs-backdrop="false" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">..</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content with-gradient d-none modal-item" id="updateTaskModal">
                    <form method="post" id="updateTaskForm" action="{{ $service->updateUrl() }}">
                        @csrf
                        <div class="mb-3">@method('put')
                            <label class="form-label" for="e_task_notes">Task <span class="text-danger">*</span>
                            </label>
                            <textarea name="task_notes" id="e_task_notes" class="form-control" required rows="4"
                                      maxlength="1000" minlength="2">{{ $task->Notes }}</textarea>
                            <p id="e_task_notes_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="e_task_date">Due Date <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control flatpickr-datetime" id="e_task_date"
                                   name="task_date" placeholder="Select start.">
                            <p id="e_task_date_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary float-start"
                                    data-bs-dismiss="modal">
                                cancel
                            </button>
                            <button class="btn btn-primary float-end" id="updateTaskBtn" type="submit"><i
                                    class="fas fa-plus-circle"></i> update task
                            </button>
                        </div>
                    </form>
                </div>
                <div class="onboarding-content text-center with-gradient d-none modal-item" id="cancelTaskModal">
                    <p class="text-danger">
                        {{ $task->Notes }}
                    </p>
                    <div class="mt-2 mb-2">
                        Are you sure you want to cancel this task ?
                    </div>
                    <hr>
                    <form id="cancelTaskForm"
                          action="{{ route('tasks.destroy',[$task->TaskID])  }}"
                          method="post"> @csrf
                        <div class="mt-4">@method('delete')
                            <button type="button" class="btn btn-success float-start"
                                    data-bs-dismiss="modal">
                                no, keep
                            </button>
                            <button class="btn btn-danger float-end" id="cancelTaskBtn" type="submit"><i
                                    class="fas fa-trash"></i> yes, trash
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
<form id="completeTaskForm" action="{{ route('tasks.update',[$task->TaskID])  }}"
      method="post"> @csrf @method('put')
</form>
<script>
    e_schedule_task_date = null;
    $(function () {
        e_schedule_task_date = flatpickr("#e_task_date", {
            enableTime: false,
            altInput: true,
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
        });
        $('form#cancelTaskForm').submit(async function (e) {
            e.preventDefault();
            const response = await saveForm($(this), $('#cancelTaskBtn'), false, true, true);
            if (response) {
                $("#taskActionsModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof response.activity === "object" && typeof appendActivity === "function") {
                    appendActivity(response.activity);
                }
                if (typeof fetchTasksTable === "function") {
                    fetchTasksTable();
                }
            }
        });

        $('form#updateTaskForm').submit(async function (e) {
            e.preventDefault();
            const response = await saveForm($(this), $('#updateTaskBtn'), false, true, true, true);
            if (response) {
                $("#taskActionsModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof response.activity === "object" && typeof appendActivity === "function") {
                    appendActivity(response.activity);
                }
                if (typeof fetchTasksTable === "function") {
                    fetchTasksTable();
                }
            }
        });

        $('form#completeTaskForm').submit(async function (e) {
            e.preventDefault();
            const response = await saveForm($(this), $('#completeTaskBtn'), false, true, true, true);
            if (response) {
                $("#taskActionsModel").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof response.activity === "object" && typeof appendActivity === "function") {
                    appendActivity(response.activity);
                }
                if (typeof fetchTasksTable === "function") {
                    fetchTasksTable();
                }
            }
        });

    });

    function triggerTrashTask() {
        $(".modal-item").addClass('d-none');
        $('#cancelTaskModal').removeClass('d-none');
        $('.modal-title').html('cancel a task.');
        $("#taskActionsModel").modal('show');
    }

    function triggerCompleteTask() {
        $('#completeTaskForm').submit()
    }

    function triggerUpdateTask() {
        e_schedule_task_date.setDate(new Date('{{ $task->Dated->format('Y-m-d') }}'));
        $(".modal-item").addClass('d-none');
        $('#updateTaskModal').removeClass('d-none');
        $('.modal-title').html('update a task.');
        $("#taskActionsModel").modal('show');
    }
</script>
