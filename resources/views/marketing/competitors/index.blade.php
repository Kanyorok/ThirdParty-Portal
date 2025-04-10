@extends('layouts.app')

@section('title','Competitors')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="mb-3">
        <h1 class="h3 d-inline align-middle">@yield('title')</h1>
        <button class="btn btn-primary float-end ms-2 modal-create-competitor" type="button"><i
                class="fas fa-plus-circle"></i> Add Competitor
        </button>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="competitorsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Clients</th>
                            <th>Core Business</th>
                            <th>Market Share</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="CompetitorActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createCompetitorModal">
                        <div class="progress mb-3 avatar-change d-none">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
                                 aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                                    Complete</small></div>
                        </div>
                        <form action="{{ route('competitors.store') }}" method="post" id="createCompetitorForm"
                              enctype="multipart/form-data"> @csrf
                            <div class="mb-3 text-center">
                                <input type="file" name="image" class="d-none" accept="image/*"
                                       style="display: none;" id="Upload_image">
                                <label for="Upload_image">
                                    <img src="https://placehold.co/200x200?font=roboto&text=Pick%20an%20Logo"
                                         id="image_upload_preview" alt=".." class="img-fluid img-thumbnail mb-2"
                                         width="200" height="200"/></label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Name" name="Name" required
                                       placeholder="Name">
                                <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="Location" class="form-label">Location <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="Location" id="Location" required></select>
                                <p id="Location_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Website">Website </label>
                                <input type="url" class="form-control" id="Website" name="Website"
                                       placeholder="https://craftsillicon.com">
                                <p id="Website_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="Phone">Phone Number </label>
                                <input type="text" class="form-control" id="Phone" name="Phone"
                                       placeholder="Phone Number">
                                <p id="Phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Email">Email </label>
                                <input type="text" class="form-control" id="Email" name="Email"
                                       placeholder="Email">
                                <p id="Email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Notes">Notes </label>
                                <textarea name="Notes" id="Notes" rows="3" class="form-control"></textarea>
                                <p id="Notes_end_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createCompetitorBtn" type="submit"><i
                                        class="fas fa-save"></i> add
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script src="{{asset('assets/plugins/jquery-form/jquery.form.min.js')}}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script> const $Modal = $('#CompetitorActionsModal');
        let competitorsTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchCompetitorsTable();

            $('#Location').select2({
                placeholder: "Select a Town/City", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: "{{ route('locality.select2') }}?type={{  \App\Enums\LocalityTypeEnum::City->value }}",
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

            $("#Upload_image").change(function () {
                $('.avatar-change').removeClass('d-none');
                $('.avatar-changed').addClass('d-none');
                readURL(this);
            });

            $(document).on('click', '.modal-create-competitor', function () {
                $(".modal-title").html('Add a Competitor');
                $(".modal-item").addClass('d-none');
                $('#createCompetitorModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createCompetitorForm').submit(function (e) {
                e.preventDefault();
                const saveBtn = $('#createCompetitorBtn');
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
                        fetchCompetitorsTable();
                        saveBtn.prop('disable', false).removeClass('disabled').prop('type', 'submit').html(btnContent);
                        $Modal.modal('hide');
                    },
                    error: function (request) {
                        saveBtn.prop('disable', false).removeClass('disabled').prop('type', 'submit').html(btnContent);
                        formRequest(request, true)
                    }, resetForm: true
                });
                return false;
            });

        });

        function fetchCompetitorsTable() {
            if (competitorsTable === null) {
                competitorsTable = $('#competitorsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    /*"order": [[4, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],*/
                    ajax: {
                        url: document.url,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {
                            data: {
                                _: "photo",
                                sort: "CompetitorID",
                            }, name: 'CompetitorID', searchable: false
                        },
                        {data: 'CompetitorName', name: 'CompetitorName'},
                        {data: 'location.Name', name: 'location.Name'},
                        {data: 'Clients', name: 'Clients'},
                        {data: 'CoreBusiness', name: 'CoreBusiness'},
                        {data: 'MarketShare', name: 'MarketShare'},
                    ], "oLanguage": {
                        "sEmptyTable": "no competitors found here"
                    }
                });

                competitorsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading competitors.");
                    console.log(er);
                });
            } else {
                competitorsTable.ajax.reload();
            }
        }

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
@endsection
