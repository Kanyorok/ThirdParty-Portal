@php use App\Enums\Core\DataTypesEnum; @endphp
@php use App\Enums\Core\ComparisonOperatorsEnum; @endphp
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
{{--<style>
    .select2-container {
        width: 100% !important;
    }
</style>--}}
<div>
    <h3 class="text-center">{{ $filter->Name }}</h3>
    <form action="{{ route('marketing-list-filters.store',[$list->slug])}}" method="post" id="createFilterForm"> @csrf
        <input type="hidden" name="filter" value="{{ $filter->Id }}" class="d-none" id="filter" style="display:none;">
        <p id="filter_error" class="text-danger d-none error col-12" role="alert"></p>
        @if($filter->Operator->value === ComparisonOperatorsEnum::Between->value)
            <div class="mb-3">
                <label class="form-label" for="Start">Start <span class="text-danger">*</span></label>
                <input type="text" class="form-control filters-value" id="Start" name="Start" required>
                <p id="Start_error" class="invalid-feedback d-none error col-12" role="alert"></p>
            </div>
            <div class="mb-3">
                <label class="form-label" for="End">End <span class="text-danger">*</span></label>
                <input type="text" class="form-control filters-value" id="End" name="End" required>
                <p id="End_error" class="invalid-feedback d-none error col-12" role="alert"></p>
            </div>
        @elseif($filter->Operator->value === ComparisonOperatorsEnum::In->value)
            <div class="mb-3">
                <label for="Values" class="form-label">Values <span class="text-danger">*</span></label>
                <select class="form-control select2-fields" name="Values[]" id="Values" multiple required>
                    @foreach($sources as $source)
                        <option value="{{ $source->value }}">{{ $source->name }}</option>
                    @endforeach
                </select>
                <p id="Values_error" class="invalid-feedback d-none error col-12" role="alert"></p>
            </div>
        @elseif($filter->Operator->isBasic())
            <div class="mb-3">
                <label class="form-label" for="Value">Value <span class="text-danger">*</span></label>
                <input type="text" class="form-control filters-value" id="Value" name="Value" required>
                <p id="Value_error" class="invalid-feedback d-none error col-12" role="alert"></p>
            </div>
        @else
            <h3 class="text-danger my-3 text-center">Un implemented Type</h3>
        @endif
        <div class="mb-3">
            <label for="operation" class="form-label">operation
                <span class="text-danger">*</span></label>
            <select class="form-control" name="operation" id="operation" required>
                <option selected value="and">and</option>
                <option value="or">or</option>
            </select>
            <p id="operation_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>


        {{--
        <div class="mb-3">
            <label class="form-label" for="Name">Full Name <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Name" name="Name" required
                   placeholder="Name">
            <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
      --}}

        {{--<div class="mb-3">
            <label class="form-label" for="Phone">Phone Number </label>
            <input type="text" class="form-control" id="Phone" name="Phone"
                   placeholder="Phone Number">
            <p id="Phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="Email">Email <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Email" name="Email"
                   placeholder="Email">
            <p id="Email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="Notes">Notes </label>
            <textarea name="Notes" id="Notes" rows="3" class="form-control"></textarea>
            <p id="Notes_end_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>--}}
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    onclick="window.bsOffcanvas.hide()">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="createFilterBtn" type="submit"><i
                    class="fas fa-save"></i> add Filter
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        @if($filter->DataType->value === DataTypesEnum::DateTime->value)
        flatpickr(".filters-value", {
            enableTime: true,
            minuteIncrement: 1,
            dateFormat: "Y-m-d H:i",
            allowInput: true,
        });
        @endif
        @if($filter->Operator->value === ComparisonOperatorsEnum::In->value)
        $('.select2-fields').select2({
            /*    theme: "bootstrap-5",*/
            dropdownParent: $("#offcanvasMain"),
        });
        @endif
        $('form#createFilterForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createFilterBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchContacts === "function") {
                    fetchContacts();
                }
                if (typeof fetchFiltersTable === "function") {
                    fetchFiltersTable();
                }
            }
        });
    });

</script>
