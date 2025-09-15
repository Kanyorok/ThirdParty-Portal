@php use App\Enums\LeadTypeEnum;use App\Enums\LocalityTypeEnum;use App\Models\CRM\Contact; @endphp
<script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
<script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div>
    <div class="progress mb-3 avatar-change d-none">
        <div class="progress-bar progress-bar-striped progress-bar-animated"
             role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
             aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                Complete</small></div>
    </div>
    <form action="{{ route('leads.store') }}" method="post" id="createCorporateForm"
          enctype="multipart/form-data"> @csrf
        <input type="hidden" name="Type" class="d-none" value="{{ LeadTypeEnum::Company->value }}">
        <input type="hidden" name="conversation" class="d-none" value="{{ $conversation }}">
        <input type="hidden" name="contact" class="d-none"
               value="{{ ($contact instanceof Contact)?$contact->ContactID:0 }}">
        <div class="mb-3 text-center">
            <input type="file" name="image" class="d-none" accept="image/*"
                   style="display: none;" id="Upload_image">
            <label for="Upload_image">
                <img src="https://placehold.co/200x200?font=roboto&text=Pick%20an%20Logo"
                     id="image_upload_preview" alt=".." class="img-fluid img-thumbnail mb-2"
                     width="200" height="200"/></label>
            <p id="image_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="Name">Company Name <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Name" name="Name" required
                   placeholder="Company Name" value="{{ ($contact instanceof Contact)?$contact->Label:'' }}">
            <p id="Name_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="Website">Website </label>
            <input type="url" class="form-control" id="Website" name="Website"
                   placeholder="https://craftsillicon.com">
            <p id="Website_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="Email">Email </label>
            <input type="text" class="form-control" id="Email" name="Email"
                   placeholder="Email" value="{{ $email }}">
            <p id="Email_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="Country" class="form-label">Country <span class="text-danger">*</span></label>
            <select class="form-control" name="Country" id="Country" required>
                <option disabled selected>Select Country</option>
                @foreach($Countries as $Country)
                    <option value="{{ $Country->CountryCode }}" data-phone="{{$Country->PhoneCode}}"
                            data-location="{{ route('locality.select2',['country'=>$Country->CountryCode]) }}">{{ $Country->Flag}} {{ $Country->Name}}</option>
                @endforeach
            </select>
            <p id="Country_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="Location" class="form-label">Location <span class="text-danger">*</span></label>
            <select class="form-control locations" name="Location" id="Location"
                    required disabled></select>
            <p id="Location_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="Phone">Phone Number <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="Phone" name="Phone"
                   placeholder="Phone Number" required value="{{ ($contact instanceof Contact)?$contact->Phone:'' }}">
            <p id="Phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="LastContact">Last Contact </label>
            <input type="text" class="form-control flatpickr-datetime last-contact"
                   id="LastContact" name="LastContact">
            <p id="LastContact_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="Industry" class="form-label">Industry <span
                    class="text-danger">*</span></label>
            <select class="form-control" name="Industry" id="Industry" required>
                <option selected disabled>select an Industry</option>
                @foreach($Industries as $Industry)
                    <option value="{{ $Industry->ID }}">{{ $Industry->Description }}</option>
                @endforeach
            </select>
            <p id="Industry_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="" class="form-label">Source <span
                    class="text-danger">*</span></label>
            <select class="form-control" name="Source" id="Source" required>
                <option selected disabled>select a Marketing Modes</option>
                @foreach($MarketingModes as $Source)
                    <option value="{{ $Source->ID }}">{{ $Source->Description }}</option>
                @endforeach
            </select>
            <p id="Source_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="CustomerType" class="form-label">Customer Type <span
                    class="text-danger">*</span></label>
            <select class="form-control" name="CustomerType" id="CustomerType" required>
                <option selected disabled>select a Customer Type</option>
                @foreach($CustomerTypes as $CustomerType)
                    <option
                        value="{{ $CustomerType->ID }}">{{ $CustomerType->Description }}</option>
                @endforeach
            </select>
            <p id="CustomerType_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="RelationshipManager" class="form-label">Relationship officer <span
                    class="text-danger">*</span></label>
            <select class="form-control relationship-manager" name="RelationshipManager"
                    id="RelationshipManager" required>
                <option value="{{ auth()->user()->UserID }}"
                        selected>{{  auth()->user()->Name }}
                    - {{  auth()->user()->UserID }}</option>
            </select>
            <p id="RelationshipManager_error"
               class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="col-12 mb-3">
            <label class="form-label" for="Notes">Notes </label>
            <textarea name="Notes" id="Notes" rows="3" class="form-control"></textarea>
            <p id="Notes_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <hr>
        <div class="mt-4 pb-2">
            <button type="button" class="btn btn-secondary float-start"
                    onclick="window.bsOffcanvas.hide();">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="createCorporateBtn" type="submit"><i
                    class="fas fa-save"></i> add
            </button>
        </div>
    </form>
</div>
<script>
    $(document).ready(function () {
        $('.relationship-manager').select2({
            placeholder: "Select assignee", minimumInputLength: 2,
            dropdownParent: $("#offcanvasMain"),
            ajax: {
                url: '{!! route('users.select2',[]) !!}',
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

        $('#Country').select2({
            placeholder: "Select a Country",
            dropdownParent: $("#offcanvasMain")
        }).on('change', function () {
            const option = $(this).find('option:selected');
            $('#Phone').val(option.data('phone'));
            $('#Location').prop('disabled', false).select2('destroy').val(null).select2({
                placeholder: "Search for the Location",
                minimumInputLength: 2,
                dropdownParent: $("#offcanvasMain"),
                ajax: {
                    url: option.data('location'),
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: $.trim(params.term)};
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {text: item.Name, id: item.ID}
                            })
                        };
                    },
                    cache: true
                }
            });
        });
        $('#Location').select2();

        /*$('.locations').select2({
            placeholder: "Select a Town/City", minimumInputLength: 2,
            dropdownParent: $("#offcanvasMain"),
            ajax: {
                url: "{ { route('locality.select2') }}?type={ {  LocalityTypeEnum::City->value }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {q: $.trim(params.term)};
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {text: item.Name, id: item.ID}
                        })
                    };
                },
                cache: true
            }
        });*/

        flatpickr(".last-contact", {
            enableTime: true,
            altInput: true,
            defaultDate: '{{ now()->subMinutes(10)->startOfHour()->format('Y-m-d H:i') }}',//moment().format('YYYY-MM-DD HH:mm'),
            altFormat: "F j, Y H:i",
            dateFormat: "Y-m-d H:i",
        });

        $("#Upload_image").change(function () {
            $('.avatar-change').removeClass('d-none');
            $('.avatar-changed').addClass('d-none');
            readURL(this);
        });

        $('form#createCorporateForm').submit(function (e) {
            e.preventDefault();
            const saveBtn = $('#createCorporateBtn');
            const btnContent = saveBtn.html();
            $(".form-control").removeClass('is-invalid');
            $('.error').addClass('d-none');
            saveBtn.prop('disable', true).addClass('disabled').prop('type', 'button').html('<i class="fas fa-spinner fa-spin"></i> please wait');
            $(this).ajaxSubmit({
                dataType: 'json', beforeSubmit: function () {
                    $("#progress-bar").width('0%');
                },
                uploadProgress: function (event, position, total, percentComplete) {
                    $("#progress-bar").width(percentComplete + '%').html('<small id="progress-status">' + percentComplete + ' % Complete</small>');
                },
                success: function (data) {
                    $('.avatar-change').addClass('d-none');
                    $('.avatar-changed').removeClass('d-none');
                    nSuccess(data.message);
                    $("#progress-bar").width('0%').html('0');
                    if (typeof fetchActiveLeadsTable === "function") {
                        fetchActiveLeadsTable();
                    }
                    if (typeof emailPageRefresh === "function") {
                        emailPageRefresh();
                    }
                    saveBtn.prop('disable', false).removeClass('disabled').prop('type', 'submit').html(btnContent);
                    @if($contact instanceof Contact)
                    setTimeout(() => {
                        window.location.replace(data.route);
                    }, 1000);
                    @endif
                    window.bsOffcanvas.hide();
                },
                error: function (request) {
                    formRequest(request, true);
                    saveBtn.prop('disable', false).removeClass('disabled').prop('type', 'submit').html(btnContent);
                }, resetForm: true
            });
            return false;
        });

    });

    function readURL(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#image_upload_preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
