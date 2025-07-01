@php use App\Enums\Core\ExtensionsEnum; @endphp
@php use App\Enums\Core\VisibilityEnum; @endphp
@extends('layouts.app')

@section('title','Bulk Upload')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}">
@endsection
@section('content')
    <div class="row">
        <div class="col-12 file-manger-wrapper">
            <div class="card" id="uploadCard">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-0">Bulk Upload</h5>
                        </div>
                        <div class="col-md-6">
                            <select name="repo" id="repo" class="form-control">
                                @foreach($repositories as $repository)
                                    <option
                                        {{ ($repository->Id === $root->Id)?'selected':'' }} value="{{ $repository->RepositoryId }}"
                                        data-route="{{ route('files.store', [$repository->RepositoryId]) }}">{{ $repository->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0 border border-top">
                    <form action="{{ route('files.store', [$root->RepositoryId]) }}"
                          id="upload-form" class="dropzone">@csrf
                    </form>
                </div>
            </div>
            <div class="table-responsive card bg-transparent border-0 shadow-none">
                <table class="table table-borderless file-card">
                    <tbody id="fileContents"></tbody>
                </table>
                <div class="d-grid text-center" id="filesMessage"></div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script src="{{ asset('assets/libs/dropzone/dropzone.min.js') }}"></script>
    <script> let dropzoneInstance;
        Dropzone.options.uploadForm = {
            maxFilesize: 9,//Mb//todo filesize
            acceptedFiles: "{{ implode(", ",ExtensionsEnum::getAllMimeTypes()) }}",
            dictDefaultMessage: `
            <div class="dropzone-msg dz-message needsclick">
                <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #ccc; margin-bottom: 10px;"></i>
                <h3>Drop files here or click to upload.</h3>
                <span class="note needsclick">(files uploaded will be in repo selected above and take the repo permissions.)</span>
            </div>`,
            init: function () {
                dropzoneInstance = this;
            }, success: function (file, response) {
                file.previewElement.remove();
                nSuccess(response.message);
                appendFiles(response.data, true);
            }, error: function (file, message) {
                msg = (typeof message === 'string') ? message : message.message

                nWarning(msg + ' : ' + file.name);
                file.previewElement.remove();
            },
        };
        $(function () {
            $('#repo').on('change', function () {

                //const selectedRepo = $(this).val();
                const newAction = $(this).find(':selected').data('route');

                // Update the form action
                $('#upload-form').attr('action', newAction);

                // Update the dropzone options if needed
                if (dropzoneInstance && dropzoneInstance.options) {
                    dropzoneInstance.options.url = newAction;
                }
            });

        });

        function appendFiles(file, prepend = false) {
            let usersContent = '';
            file.users.data.data.forEach(function (user) {
                usersContent += user.image;
            });
            if (file.users.hasMorePages) {
                usersContent += ' <span class="avtar avtar-xs bg-light-primary text-primary">+' + file.users.total + '</span>';
            }
            let tagsContent = '';
            file.tags.data.data.forEach(function (tag) {
                if (tag.visibility.value === '{{ VisibilityEnum::Private->value }}') {
                    tagsContent += '<span class="badge rounded-pill text-bg-primary">' + tag.Name + '</span>'
                } else {
                    tagsContent += '<span class="badge rounded-pill text-bg-danger">' + tag.Name + '</span>'
                }
            });

            let content = '<tr id="' + file.id + '" class="dbl-click-redirect-data" data-dbl_click_url="' + file.links.detail + '"> <td> <div class="d-flex align-items-center"><img src="' + file.type.img + '" alt="user-image" class="wid-35">' +
                '<h6 class="mb-0 ms-2 text-truncate">' + file.name + '</h6> </div> </td> <td>' + file.size.string + '</td> <td>' + file.dated.datetime + '</td>' +
                '<td> <div class="user-group p-1">' + usersContent + '  </div> </td>' +
                '<td> <div class="d-flex flex-wrap gap-2">' + tagsContent + ' </div> </td>' +
                '<td> <ul class="list-inline text-end"> <li class="list-inline-item mx-2"> ' + file.visibility.icon + ' </li>' +
                '<li class="list-inline-item"><div class="dropdown"><a class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="material-icons-two-tone f-18">more_vert</i></a><div class="dropdown-menu dropdown-menu-end" style="">' +
                '<a class="dropdown-item" href="' + file.links.detail + '">Details</a> ' +
                '<a class="dropdown-item click-summary-data" href="javascript:void(0)" data-summary_title=" ' + file.type.icon + ' ' + file.name + ' " data-click_url="' + file.links.summary + '"> share </a>' +
                '<a class="dropdown-item file-action-trash" data-title=" ' + file.type.icon + ' ' + file.name + ' " data-url="' + file.links.detail + '" href="#">Delete</a></div></div></li> </ul> </td> </tr>';
            if (prepend) {
                $('#fileContents').prepend(content);
            } else {
                $('#fileContents').append(content);
            }
        }
    </script>
@endsection
