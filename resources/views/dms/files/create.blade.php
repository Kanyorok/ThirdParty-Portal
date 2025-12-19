@php use App\Enums\Core\ExtensionsEnum; @endphp
@php use App\Enums\Core\VisibilityEnum; @endphp
@extends('dms.layout')

@section('title','Bulk Upload')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}">
    <style>
        /* Dropzone Styling */
        .dropzone {
            border: 2px dashed #ccc;
            border-radius: 8px;
            min-height: 200px;
            padding: 20px;
            background: #fafafa;
            transition: all 0.3s ease;
        }

        .dropzone.dz-drag-hover {
            border-color: #007bff;
            background: #f0f8ff;
        }

        .dz-preview {
            border-radius: 5px;
            margin: 10px;
            padding: 10px;
            border: 1px solid #e0e0e0;
            background: white;
        }

        .dz-preview.dz-error {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .dz-success-mark, .dz-error-mark {
            display: none; /* Hide default icons */
        }

        /* Upload Button Styling */
        #upload-all-btn {
            transition: all 0.3s ease;
        }

        #upload-all-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* File Preview Styling */
        .dz-filename {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .dz-size {
            color: #666;
            font-size: 12px;
        }

        /* Progress Bar */
        .dz-progress {
            width: 100%;
            height: 5px;
            background: #e0e0e0;
            border-radius: 3px;
            margin-top: 10px;
            overflow: hidden;
        }

        .dropzone .dz-preview .dz-progress {
            top: 60%;
        }

        .dz-upload {
            background: #007bff;
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
    </style>
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
                    <div class="upload-controls mt-3 mx-3">
                        <button id="cancel-all-btn" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-times"></i> Cancel All
                        </button>
                        <button id="remove-all-btn" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-trash"></i> Remove All
                        </button>
                        <span id="file-count" class="badge badge-info ml-2">0 files</span>
                    </div>
                </div>
            </div>
            <div class="table-responsive card bg-transparent border-0 shadow-none">
                <table class="table table-borderless file-card" style="min-height: 300px;">
                    <tbody id="fileContents"></tbody>
                </table>
                <div class="d-grid text-center" id="filesMessage"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="dmsActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="trashFileModal">
                        <h4 class="text-danger">
                            Trash Document <b class="rm-file-name"></b> ?
                        </h4>
                        <div class="alert alert-warning" role="alert">
                            <b>Note</b>This file will be deleted permanently
                        </div>
                        <form id="trashFileForm" method="post"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashFileBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, delete
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
    <script src="{{ asset('assets/libs/dropzone/dropzone.min.js') }}"></script>
    <script>const $Modal = $('#dmsActionsModal');
        let dropzoneInstance;
        Dropzone.options.uploadForm = {
            autoProcessQueue: false, // Disable auto upload
            maxFilesize: parseInt('{{ config('app.dms.file_size') }}'), // MB
            maxFiles: 30, // Optional: limit number of files
            parallelUploads: 3,
            uploadMultiple: false,
            addRemoveLinks: true, // Add remove file links
            dictRemoveFile: "Remove",
            dictCancelUpload: "Cancel",

            acceptedFiles: "{{ implode(", ",ExtensionsEnum::getAllMimeTypes()) }}",

            dictDefaultMessage: `
                        <div class="dropzone-msg dz-message needsclick">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #ccc; margin-bottom: 10px;"></i>
                            <h3>Max File Size: {{ config('app.dms.file_size') }} Mb</h3>
                            {!! $allowedFiles !!}
            <h3>Drop files here or click to upload.</h3>
            <span class="note needsclick">(files uploaded will be in repo selected above and take the repo permissions.)</span>
        </div>`,

            init: function () {
                dropzoneInstance = this;

                const submitButton = document.createElement("button");
                submitButton.id = "upload-all-btn";
                submitButton.className = "btn btn-primary float-end";
                submitButton.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload All Files';
                submitButton.disabled = true; // Disable initially

                // Insert button after dropzone
                $('.upload-controls').append(submitButton);

                // Store button reference
                this.submitButton = submitButton;

                // Event listeners
                submitButton.addEventListener("click", () => {
                    this.processQueue();
                });

                // Listen to events to update button state
                this.on("addedfile", (file) => {
                    // Enable button when files are added
                    if (this.files.length > 0) {
                        this.submitButton.disabled = false;
                        this.submitButton.innerHTML = `<i class="fas fa-cloud-upload-alt"></i> Upload ${this.files.length} File(s)`;
                    }
                });

                this.on("removedfile", (file) => {
                    // Update button text or disable if no files
                    if (this.files.length === 0) {
                        this.submitButton.disabled = true;
                        this.submitButton.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload All Files';
                    } else {
                        this.submitButton.innerHTML = `<i class="fas fa-cloud-upload-alt"></i> Upload ${this.files.length} File(s)`;
                    }
                });

                this.on("sendingmultiple", () => {
                    // Called when all files are being sent
                    $('.upload-controls').addClass('d-none');
                    this.submitButton.disabled = true;
                    this.submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                });

                this.on("successmultiple", (files, response) => {
                    // Called when all files uploaded successfully
                    nSuccess(response.message || 'All files uploaded successfully!');
                    appendFiles(response.data, true);

                    // Reset button
                    this.submitButton.disabled = true;
                    this.submitButton.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload All Files';

                    // Clear all files from preview
                    this.removeAllFiles(true);
                });

                this.on("errormultiple", (files, response) => {
                    // Handle multiple file errors
                    this.submitButton.disabled = false;
                    this.submitButton.innerHTML = `<i class="fas fa-cloud-upload-alt"></i> Upload ${this.files.length} File(s)`;
                });

                // Individual file success/error
                this.on("success", (file, response) => {
                    // Optional: handle individual file success
                    file.previewElement.classList.add("dz-success");
                });

                this.on("error", (file, message) => {
                    const msg = (typeof message === 'string') ? message : message.message;
                    nWarning(msg + ' : ' + file.name);

                    // Keep error files in the list so user can retry
                    file.previewElement.classList.add("dz-error");

                    // Add retry button to errored files
                    const retryButton = Dropzone.createElement(
                        '<button class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-redo"></i> Retry</button>'
                    );

                    retryButton.addEventListener("click", (e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        // Remove old error message
                        const errorMessage = file.previewElement.querySelector(".dz-error-message");
                        if (errorMessage) errorMessage.remove();

                        // Reset file status
                        file.status = Dropzone.ADDED;
                        file.accepted = true;
                        file.previewElement.classList.remove("dz-error");

                        // Remove retry button
                        retryButton.remove();

                        // Process this single file
                        this.processFile(file);
                    });

                    // Add retry button if not already present
                    if (!file.previewElement.querySelector(".retry-button")) {
                        retryButton.classList.add("retry-button");
                        file.previewElement.querySelector(".dz-details").appendChild(retryButton);
                    }
                });

                this.on("complete", (file) => {
                    // Optional: do something when individual file upload completes
                    if (!file.previewElement.classList.contains("dz-error")) {
                        // Remove successful files after delay
                        setTimeout(() => {
                            this.removeFile(file);
                        }, 2000);
                    }
                    if (dropzoneInstance.getQueuedFiles().length > 0) {
                        // Small delay to ensure proper state transition
                        setTimeout(() => {
                            dropzoneInstance.processQueue();
                        }, 100);
                    }
                });

                this.on("queuecomplete", () => {
                    // Re-enable button when queue completes
                    $('.upload-controls').removeClass('d-none');
                    this.submitButton.disabled = false;
                    this.submitButton.innerHTML = `<i class="fas fa-cloud-upload-alt"></i> Upload ${this.files.length} File(s)`;
                });
            },
            success: function (file, response) {
                file.previewElement.remove();
                // nSuccess(response.message);
                appendFiles(response.data, true);
            }, error: function (file, message) {
                msg = (typeof message === 'string') ? message : message.message
                nWarning(msg + ' : ' + file.name);
                file.previewElement.remove();
            }
        };
        $(function () {
            document.getElementById('cancel-all-btn')?.addEventListener('click', function () {
                if (dropzoneInstance && confirm('Cancel all uploads?')) {
                    dropzoneInstance.removeAllFiles(true); // true = cancel current uploads
                }
            });

            // Remove All Files button
            document.getElementById('remove-all-btn')?.addEventListener('click', function () {
                if (dropzoneInstance && confirm('Remove all files?')) {
                    dropzoneInstance.removeAllFiles();
                }
            });

            // Update file count badge
            if (dropzoneInstance) {
                dropzoneInstance.on("addedfile", updateFileCount);
                dropzoneInstance.on("removedfile", updateFileCount);
                dropzoneInstance.on("queuecomplete", updateFileCount);

                function updateFileCount() {
                    const fileCount = dropzoneInstance.files.length;
                    const badge = document.getElementById('file-count');
                    if (badge) {
                        badge.textContent = fileCount + ' file' + (fileCount !== 1 ? 's' : '');
                    }
                }
            }

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

            $(document).on('click', '.file-action-trash', function () {
                $(".modal-title").html('<b class="text-danger">Trash</b>  : ' + $(this).data('title'));
                $("#trashFileForm").attr('action', $(this).data('url'));
                $(".rm-repo-name").html($(this).data('title'));
                $(".modal-item").addClass('d-none');
                $('#trashFileModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#trashFileForm').submit(async function (e) {
                e.preventDefault();
                const response = await saveForm($(this), $('#trashFileBtn'), false, true, true);
                if (response) {
                    $('#' + response.data.id).remove();
                    $Modal.modal('hide');
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
