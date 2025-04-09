<div>
    <div class="alert alert-info " role="alert">
        <div class="alert-message">
            <strong>NB!</strong> Onboard using DFA, Use phone number here to match!
        </div>
    </div>
    <form action="{{ route('leads.onboarding', [$lead->LeadID]) }}" method="post" id="leadOnBoardingForm"> @csrf
        {{--<div class="mb-2">
            <label for="Name" class="form-label">Name. <span class="text-danger">*</span></label>
            <input type="text" id="Name" class="form-control w-100" readonly
                   value="{{ $lead->Name }} {{ ($lead->OtherNames)??'' }}">
        </div>
        <div class="mb-2">
            <label for="MemberClass" class="form-label">Member Class <span class="text-danger">*</span></label>
            <select class="form-control select2-fields" name="MemberClass" id="MemberClass" required>
                <option disabled selected>Select a Member Class</option>
                @foreach($memberClasses as $memberClass)
                    <option value="{{ $memberClass->value }}">{{ $memberClass->name }}</option>
                @endforeach
            </select>
            <p id="MemberClass_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-2">
            <label for="Branch" class="form-label">Branch <span class="text-danger">*</span></label>
            <select class="form-control select2-fields" name="Branch" id="Branch" required>
                <option disabled selected>Select a Branch</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->value }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            <p id="Branch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-2">
            <label for="GovernmentID" class="form-label">Government ID <span class="text-danger">*</span></label>
            <input type="text" name="GovernmentID" id="GovernmentID" class="form-control" required>
            <p id="GovernmentID_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-2">
            <label for="TaxNo" class="form-label">Tax No. <span class="text-danger">*</span></label>
            <input type="text" name="TaxNo" id="TaxNo" class="form-control" required>
            <p id="TaxNo_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-2">
            <label for="DoB" class="form-label">Date of Birth. <span class="text-danger">*</span></label>
            <input type="text" name="DoB" id="DoB" class="form-control w-100" required>
            <p id="DoB_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-2">
            <label for="Address1" class="form-label">Address 1 <span class="text-danger">*</span></label>
            <input type="text" name="Address1" id="Address1" class="form-control w-100" required>
            <p id="Address1_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-2">
            <label for="Address2" class="form-label">Address 2 <span class="text-danger">*</span></label>
            <input type="text" name="Address2" id="Address2" class="form-control w-100" required>
            <p id="Address2_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-2">
            <label for="County" class="form-label">County <span class="text-danger">*</span></label>
            <select class="form-control select2-fields" name="County" id="County" required>
                <option disabled selected>Select a Country</option>
                @foreach($countries as $country)
                    <option value="{{ $country->CountryID }}">{{ $country->CountryName }}</option>
                @endforeach
            </select>
            <p id="County_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>--}}
        <hr>@method('put')
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    data-bs-dismiss="modal">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="leadOnBoardingBtn" type="submit"><i
                    class="fas fa-save"></i> lead won
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        /* flatpickr("#DoB", {
             enableTime: false,
             dateFormat: "Y-m-d",
             static: true,
             allowInput: true,
         });
         $("#DoB").parent('div').addClass('w-100');

         $('.select2-fields').select2({
             /!*    theme: "bootstrap-5",*!/
             dropdownParent: $("#offcanvasMain"),
         });*/
        $('form#leadOnBoardingForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#leadOnBoardingBtn'), true, true, true)) {
                window.bsOffcanvas.hide();
            }
        });
    });

</script>
