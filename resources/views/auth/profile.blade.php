@extends('layouts.app')

@section('title')
    Profile
@endsection
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css"
          integrity="sha512-ngQ4IGzHQ3s/Hh8kMyG4FC74wzitukRMIcTOoKT3EyzFZCILOPF0twiXOQn75eDINUfKBYmzYn2AA8DkAk8veQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-xl-3">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Details</h5>
                </div>
                <div class="card-body text-center">
                    <div class="progress mb-3 avatar-change d-none">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
                             aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                                Complete</small></div>
                    </div>
                    {!! $user->getImage('id="image_upload_preview" alt=".." class="img-fluid avatar-1 rounded-circle mb-2" width="128" height="128"') !!}
                    <h5 class="card-title mb-0">{{ $user->Name }}</h5>
                    <div class="text-muted mb-2">{{ $user->roles()->first()?->name }}</div>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <form action="{{ route('profile.avatar') }}" method="POST" id="new_avatar"
                              data-parsley-validate enctype="multipart/form-data">@csrf
                            <input type="file" name="image" class="d-none" accept="image/*"
                                   style="display: none;" id="Upload_image">
                            <label for="Upload_image" class="btn btn-primary avatar-changed"
                                   type="button"><i
                                        class="fas fa-image"></i><span> change photo</span></label>
                            <button type="submit"
                                    class="btn btn-success uploadBtn avatar-change d-none"
                                    id="uploadBtn"><i class="fa fa-upload"></i> Upload & Save
                            </button>
                        </form>
                        <small class="avatar-change d-none">For best results, use an image at least 128px by 128px in
                            .jpg
                            format</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-xl-9">
            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">Account</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-2" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">Credentials</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active m-2" id="tab-1" role="tabpanel">
                        <form id="userProfileFrom" method="post" action="{{ route('profile') }}"> @method('put') @csrf
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="Name">Name</label>
                                    <input type="text" class="form-control  profile-form" id="Name" disabled
                                           placeholder="Name" required
                                           value="{{ $user->Name }}" name="Name">
                                    <span id="Name_error" class="invalid-feedback d-none error" role="alert"></span>
                                </div>
                                <div class="mb-3 col-md-6 {{ ($user->Linked)?'d-none':'' }}">
                                    <label class="form-label" for="UserID">UserID</label>
                                    <input type="text" class="form-control  profile-form" id="UserID" disabled
                                           placeholder="UserID" required
                                           value="{{ $user->UserID }}" name="UserID">
                                    <span id="UserID_error" class="invalid-feedback d-none error" role="alert"></span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="Phone">Phone number</label>
                                    <input type="text" class="form-control  profile-form" id="Phone" disabled
                                           placeholder="Phone" required
                                           value="{{ $user->Phone }}" name="Phone">
                                    <span id="Phone_error" class="invalid-feedback d-none error" role="alert"></span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="ClientID">Client ID</label>
                                    <input type="text" class="form-control profile-form" id="ClientID" disabled
                                           placeholder="ClientID" required
                                           value="{{ $user->ClientID }}" name="ClientID">
                                    <span id="ClientID_error" class="invalid-feedback d-none error" role="alert"></span>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="Email">Email</label>
                                    <input type="Email" class="form-control  profile-form" id="Email" disabled
                                           placeholder="Email" required
                                           value="{{ $user->Email }}" name="Email">
                                    <span id="Email_error" class="invalid-feedback d-none error" role="alert"></span>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label" for="Signature">Email Signature </label>
                                    <p id="Signature_error" class="text-danger d-none error col-12" role="alert"></p>
                                    <div id="Email_Signature">{!! $user->Email_Signature !!}</div>
                                    <textarea name="Signature" id="Signature" rows="3"
                                              class="form-control d-none">{!! $user->Email_Signature !!}</textarea>
                                </div>
                            </div>
                            <hr class="mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <button type="button" class="btn btn-secondary d-none float-start"
                                            id="userProfileCancelBtn">
                                        cancel
                                    </button>
                                    <button type="button" class="btn btn-primary float-start" id="userProfileEditBtn">
                                        edit profile
                                    </button>
                                </div>
                                <div class="col-6">

                                    <button type="submit" class="btn btn-success d-none float-end" id="userProfileBtn">
                                        save
                                        changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="tab-2" role="tabpanel">
                        @if($user->Linked)
                            <p class="my-5 h3 text-center">Account link to Core banking, change password from there.</p>
                        @else
                            <form action="#" method="post" id="CredForm"> @csrf
                                <div class="mb-3">
                                    <label for="current_password">Current Password</label>
                                    <input class="form-control" id="current_password" placeholder="Current Password"
                                           name="current_password" required="required" type="password"
                                           autocomplete="password">
                                    <span id="current_password_error" class="invalid-feedback d-none error"
                                          role="alert"></span>
                                </div>
                                <div class="mb-3">
                                    <label for="password"> Password</label>
                                    <input id="password" type="password" placeholder="New password" class="form-control"
                                           name="password" required autocomplete="new-password">
                                    <span id="password_error" class="invalid-feedback d-none error" role="alert"></span>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation">Confirm Password</label>
                                    <input id="password_confirmation" type="password" placeholder="Confirm password"
                                           class="form-control" name="password_confirmation" required
                                           autocomplete="new-password">
                                    <span id="password_confirmation_error" class="invalid-feedback d-none error"
                                          role="alert"></span>
                                </div>

                                <button class="btn btn-primary float-end" type="submit" id="credBtn"> change Password
                                </button>
                                <div class="clearfix"></div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{asset('assets/plugins/jquery-form/jquery.form.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"
            integrity="sha512-6F1RVfnxCprKJmfulcxxym1Dar5FsT/V2jiEUvABiaEiFWoQ8yHvqRM/Slf0qJKiwin6IDQucjXuolCfCKnaJQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        const EditBtn = $("#userProfileEditBtn"), CancelBtn = $("#userProfileCancelBtn"),
            UpdateProfileBtn = $("#userProfileBtn");
        $(document).ready(function () {
            EditBtn.on('click', function () {
                CancelBtn.removeClass('d-none');
                $('#Email_Signature').addClass('d-none');
                $('#Signature').removeClass('d-none');
                UpdateProfileBtn.removeClass('d-none');
                EditBtn.addClass('d-none');
                $('.profile-form').prop('disabled', false);
                $('textarea#Signature').summernote({
                    placeholder: 'Email Signature',
                    dialogsInBody: true,
                    tabsize: 2,
                    height: 100,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture' /*,'video'*/]],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            });


            CancelBtn.on('click', function () {
                CancelBtn.addClass('d-none');
                UpdateProfileBtn.addClass('d-none');
                EditBtn.removeClass('d-none');
                $('#Email_Signature').removeClass('d-none');
                $('.profile-form').prop('disabled', true);
                $('#Signature').addClass('d-none').summernote('destroy')
            });

            $("#Upload_image").change(function () {
                $('.avatar-change').removeClass('d-none');
                $('.avatar-changed').addClass('d-none');
                readURL(this);
            });
            $('form#new_avatar').submit(function (e) {
                e.preventDefault();
                if ($('#Upload_image').val()) {
                    $('.uploadBtn').prop('disabled', true);
                    $(this).ajaxSubmit({
                        dataType: 'json', beforeSubmit: function () {
                            $("#progress-bar").width('0%');
                        },
                        uploadProgress: function (event, position, total, percentComplete) {
                            $("#progress-bar").width(percentComplete + '%').html('<small id="progress-status">' + percentComplete + ' % Complete</small>');
                        },
                        success: function (data) {
                            $('.uploadBtn').prop('disabled', false);
                            $('.avatar-change').addClass('d-none');
                            $('.avatar-changed').removeClass('d-none');
                            nSuccess('Avatar changed Successfully !');
                            $("#progress-bar").width('0%').html('0');
                            $('.avatar-1').attr('src', data.avatar).change();
                        },
                        error: function (request) {
                            formRequest(request, true)
                        }, resetForm: true
                    });
                    return false;
                } else {
                    nError('Upload a Valid Image less than 3Mb ' + status);
                }
            });

            $('form#CredForm').submit(function (e) {
                e.preventDefault();
                saveForm($(this), $('#credBtn'), false, true, true);
            });

            $('form#userProfileFrom').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), UpdateProfileBtn, true, false, true);
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
        });
    </script>
@endsection
