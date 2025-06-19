@php use App\Enums\Marketing\PlannerTypeEnum; @endphp
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
<script src="{{ asset('assets/libs/rangePlugin.js') }}"></script>
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<div>
    <form action="{{ route('planner-activities.update',[$planner->PlannerID, $activity->PlannerActivityID]) }}"
          method="post" class="row"
          id="updateActivityPlannerForm"> @csrf
        <div class="mb-3 ">@method('put')
            <label class="form-label" for="e_activity_name">Name <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="e_activity_name" name="activity_name"
                   placeholder="Name" value="{{ $activity->Name }}">
            <p id="e_activity_name_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3 ">
            <label class="form-label" for="e_activity_budget">Budget <span
                    class="text-danger">*</span></label>
            <input type="number" class="form-control" id="e_activity_budget" name="activity_budget"
                   placeholder="Budget" value="{{ $activity->Budget }}">
            <p id="e_activity_budget_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3 ">
            <label class="form-label" for="e_activity_location">Location <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="e_activity_location" name="activity_location"
                   placeholder="Location" value="{{ $activity->Location }}">
            <p id="e_activity_location_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        @if($planner->Type->value === PlannerTypeEnum::MasterPlanner->value)
            <div class="mb-3 ">
                <label for="e_Branch" class="form-label">Branch <span class="text-danger">*</span> </label>
                <select class="form-control" name="Branch" id="e_Branch" required>
                    @foreach($Branches as $Branch)
                        <option
                            {{ ($Branch->OurBranchID===\Illuminate\Support\Str::squish($activity->BranchId))?'selected':'' }} value="{{ $Branch->OurBranchID }}">{{ $Branch->BranchName }}</option>
                    @endforeach
                </select>
                <p id="e_Branch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
            </div>
        @endif
        <div class="mb-3 ">
            <label class="form-label" for="e_activity_start">Start <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control flatpickr-datetime" id="e_activity_start"
                   name="activity_start" placeholder="Select start." value="{{ $activity->StartOn->format('Y-m-d') }}">
            <p id="e_activity_start_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3 ">
            <label class="form-label" for="e_activity_end">End <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control flatpickr-datetime" readonly id="e_activity_end"
                   name="activity_end" placeholder="Select end." value="{{ $activity->EndOn->format('Y-m-d') }}">
            <p id="e_activity_end_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3 ">
            <label for="e_activity_users" class="form-label">User(s)</label>
            <select class="form-control " name="activity_users[]" id="e_activity_users" multiple required>
                @foreach($users as $user)
                    <option value="{{ $user->UserID }}" class="selected-users" selected>{{ $user->Name }}
                        - {{ $user->UserID }}</option>
                @endforeach
            </select>
            <p id="e_activity_users_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="e_activity_materials">Materials </label>
            <textarea name="activity_materials" id="e_activity_materials" rows="2" class="form-control"
                      minlength="2">{{ $activity->Materials }}</textarea>
            <p id="e_activity_materials_end_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3 ">
            <label class="form-label" for="e_activity_notes">Notes </label>
            <textarea name="activity_notes" id="e_activity_notes" rows="2" class="form-control"
                      minlength="2">{{ $activity->Notes }}</textarea>
            <p id="e_activity_notes_end_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    data-bs-dismiss="modal">cancel
            </button>
            <button class="btn btn-primary float-end" id="updateActivityPlannerBtn" type="submit">
                <i class="fas fa-save"></i>update activity
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        flatpickr("#e_activity_start", {
            minDate: moment().add(10, 'm').format('YYYY-MM-DD hh:mm'),
            mode: 'range',
            dateFormat: "Y-m-d",
            allowInput: true,
            "plugins": [new rangePlugin({input: "#e_activity_end"})]
        });
        $('#e_activity_users').select2({
            placeholder: "Choose users ...", minimumInputLength: 2,
            dropdownParent: $('#offcanvasMain'),
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

        $('form#updateActivityPlannerForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateActivityPlannerBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchActivitiesTable === "function") {
                    fetchActivitiesTable();
                }
            }
        });
    });
</script>
