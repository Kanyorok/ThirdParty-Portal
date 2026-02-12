@extends('dms.layout')

@section('title','Document Validation')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-center">{{ $documentValidation->Name }}</h3>
                    <h3 class="text-center">{{ $documentValidation->ValidationId }}</h3>
                    <ul class="list-group list-group-flush">
                        @foreach($documentValidation->properties as $property)
                            <li class="list-group-item ">{{ \Illuminate\Support\Str::of($property->Name)->replace('_',' ')->upper()->toString() }}
                                : <b
                                    class="float-end">{{ $property->formated_value }}</b></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    @include('snippets.behind_scenes',['model'=>$documentValidation])
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xxl-9">
            @if(is_null($document))
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Upload Document</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3 file-change d-none">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
                                 aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                                    Complete</small></div>
                        </div>
                        <form action="{{ route('dms.validation.update', $documentValidation->ValidationId) }}"
                              method="POST" enctype="multipart/form-data" id="documentUploadForm">@csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="file" class="form-label">Select Document</label>
                                <input type="file" class="form-control" id="file" name="file"
                                       accept="application/pdf" required>
                            </div>
                            <button type="submit" class="btn btn-primary float-end file-change d-none"
                                    id="documentUploadBtn">Upload Document
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card ">
                    <div class="card-body" style="min-height: 100px" id="FilePreviewPage">
                        <p class="text-center m-5"><i class="fas fa-spinner fa-spin fa-5x"></i><br>loading preview</p>
                    </div>
                    @if(is_null($documentValidation->ApprovedBy))
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm-6 col-12">{{-- dms.validation.approve--}}
                                    <button class="btn btn-primary w-100 " type="button"><i
                                            class="fas fa-check-double"></i> approve
                                    </button>
                                </div>

                                <div class="col-sm-6 col-12">
                                    <button class="btn btn-danger w-100 file-action-trash" type="button"><i
                                            class="fas fa-trash"></i> reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(function () {
            fetchFilePreview();

            $("#file").change(function () {
                $('.file-change').removeClass('d-none');
            });

            @if(is_null($document))
            $('#documentUploadForm').on('submit', function (e) {
                e.preventDefault();
                const btn = $("#documentUploadBtn");
                $(this).ajaxSubmit({
                    dataType: 'json', beforeSubmit: function () {
                        $("#progress-bar").width('0%');
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
                    },
                    uploadProgress: function (event, position, total, percentComplete) {
                        $("#progress-bar").width(percentComplete + '%').html('<small id="progress-status">' + percentComplete + ' % Complete</small>');

                    },
                    success: function (data) {
                        $('.avatar-change').addClass('d-none');
                        nSuccess(data.message);
                        if (data.route !== '') {
                            window.setTimeout(function () {
                                window.location.replace(data.route)
                            }, 3000)
                        }
                    },
                    error: function (request) {
                        formRequest(request, true)
                    },
                    complete: function () {
                        btn.prop('disabled', false).html('Upload Document');
                    }, resetForm: true
                });
            });
            @endif
        });

        function fetchFilePreview() {
            @if(!is_null($document))
            const previewContainer = document.getElementById('FilePreviewPage');
            $.ajax({
                url: "{{ route('file.preview', [$document->DocumentId]) }}",
                method: "GET",
                success: function (html) {
                    previewContainer.innerHTML = html;
                },
                error: function (jqXHR) {
                    previewContainer.innerHTML = `
                <div class="text-center m-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                    <p class="mt-2">Error loading preview: ${jqXHR.statusText}</p>
                </div>`;
                }
            });
            @endif
        }

    </script>
@endsection
