@extends('layouts.app')

@section('title','Schedule a Post')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        /* .select2-container {
             width: 100% !important;
         }*/
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header pb-0 border-1 border-bottom">
                    <h3 class="card-title">@yield('title')</h3>
                    <div class="progress mb-3 image-change d-none">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
                             aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                                Complete</small></div>
                    </div>
                </div>
                <form action="{{ route('socials.store') }}" method="post" class="card-body row" id="createSocialForm"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="col-sm-6 col-12">
                        <div class="mb-3">
                            <label for="Destination" class="form-label">Destination(s) <span
                                    class="text-danger">*</span></label>
                            <select class="form-control " name="Destination[]" id="Destination" multiple required>
                                @foreach(\App\Enums\Core\IntegrationsEnum::socials() as $social)
                                    <option selected
                                            value="{{ $social->value }}">{!! $social->getIcon() !!} {{ $social->name }}</option>
                                @endforeach
                            </select>
                            <p id="Destination_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="Publish_On">Publish On <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control flatpickr-datetime" id="Publish_On"
                                   name="Publish_On" placeholder="Select Time to Publish.">
                            <p id="Publish_On_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="Content">Content </label>
                            <textarea name="Content" id="Content" class="form-control"
                                      rows="5" minlength="2" maxlength="500"></textarea>
                            <p id="Content_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-12 text-center">
                        <img src="https://placehold.co/600x300?font=roboto&text=No+Image+Select+One"
                             id="image_upload_preview" alt=".."
                             class="img-fluid  mb-2" style="max-height: 200px;"/>
                        <input type="file" name="image" class="d-none" accept="image/*"
                               style="display: none;" id="Upload_image">
                        <div class="clearfix"></div>
                        <label for="Upload_image" class="btn btn-primary" type="button">
                            <i class="fas fa-image"></i><span> change photo</span>
                        </label>
                        <p class="text-muted d-none">maximum of 3mb with .png or .jpg formats</p>
                    </div>
                    <div class="col-12 mt-2">
                        <hr>
                        <a type="button" class="btn btn-secondary float-start" href="{{ route('socials.index') }}">
                            cancel
                        </a>
                        <button type="submit" class="btn btn-success uploadBtn image-change d-none float-end"
                                id="createSocialBtn"><i class="fa fa-save"></i>schedule post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script>
        $(function () {
            $("#Upload_image").change(function () {
                $('.image-change').removeClass('d-none');
                $('.image-changed').addClass('d-none');
                window.isDirty = true;
                readURL(this);
            });

            $('form#createSocialForm').submit(function (e) {
                e.preventDefault();
                $('#createSocialBtn').prop('disabled', true);
                $(this).ajaxSubmit({
                    dataType: 'json', beforeSubmit: function () {
                        $("#progress-bar").width('0%');
                    },
                    uploadProgress: function (event, position, total, percentComplete) {
                        $("#progress-bar").width(percentComplete + '%').html('<small id="progress-status">' + percentComplete + ' % Complete</small>');
                    },
                    success: function (data) {
                        $("#progress-bar").width('0%').html('0');
                        $('.image-change').addClass('d-none');
                        $('.image-changed').removeClass('d-none');
                        nSuccess(data.message);
                        window.isDirty = false;
                        window.setTimeout(function () {
                            window.location.replace(data.route);
                        }, 3000)
                    },
                    error: function (request) {
                        formRequest(request, true);
                        $('#createSocialBtn').prop('disabled', false);
                    }, resetForm: true
                });
                return false;
            });

            $('#Destination').select2({
                templateResult: formatSocial,
                templateSelection: formatSocial,
                selectOnClose: true,
                escapeMarkup: function (markup) {
                    return markup;
                }
            });

            flatpickr("#Publish_On", {
                altInput: true,
                enableTime: true,
                minuteIncrement: 1,
                altFormat: "F j, Y h:i K",
                dateFormat: "Y-m-d H:i",
                allowInput: true,
                minDate: moment().format('YYYY-MM-DD HH:mm'),
                defaultDate: '{{ now()->format('Y-m-d H:i') }}'
            });

        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#image_upload_preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function formatSocial(state) {
            if (!state.id) {
                return state.text;
            }
            if (state.id === '{{ \App\Enums\Core\IntegrationsEnum::Twitter->value }}') {
                return '{!!  \App\Enums\Core\IntegrationsEnum::Twitter->getIcon() !!} {{  \App\Enums\Core\IntegrationsEnum::Twitter->name }}';
            } else if (state.id === '{{ \App\Enums\Core\IntegrationsEnum::Facebook->value }}') {
                return '{!!  \App\Enums\Core\IntegrationsEnum::Facebook->getIcon() !!} {{  \App\Enums\Core\IntegrationsEnum::Facebook->name }}';
            }
            return null;
        }
    </script>
@endsection
