@php use App\Enums\Core\ExtensionsEnum; use App\Enums\Core\VisibilityEnum; @endphp
@extends('layouts.app')

@section('title')
    Recent Files
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}">
    <style>
        .icon-size {
            height: 30px !important;
            width: 30px !important;
        }
    </style>
@endsection
@section('search-form')
    <style>
        .tt-menu {
            width: 100% !important;
            padding: .5rem 1.5rem !important;
            opacity: 0.98;
        }
    </style>
    <div class="form-search" action="" method="get"><i class="search-icon">
            <svg class="pc-icon">
                <use xlink:href="#custom-search-normal-1"></use>
            </svg>
        </i><input type="search" name="q" class="form-control typeahead" id="SearchInput" style="width: 50vw;"
                   placeholder="Search Files & Folders">
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12 file-manger-wrapper">
            {{--<div class="row my-3">
                <div class="col">
                    <div class="d-flex align-items-center"><h5 class="mb-0 me-2">Recent Files</h5></div>
                </div>--}}
            {{--  <div class="col-auto">
                  <button href="#" class="btn btn-primary btn-sm" id="action-file-upload"><i
                          class="fas fa-cloud-upload"></i>&nbsp; upload files
                  </button>
              </div>--}}
            {{-- <div class="col-auto">
                 <ul class="nav nav-pills nav-files" id="pills-tab" role="tablist">
                     <li class="nav-item" role="presentation">
                         <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                                 data-bs-target="#pills-home" role="tab" aria-controls="pills-home"
                                 aria-selected="true"><i class="ti ti-layout-grid"></i></button>
                     </li>
                     <li class="nav-item" role="presentation">
                         <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                                 data-bs-target="#pills-profile" role="tab" aria-controls="pills-profile"
                                 aria-selected="false" tabindex="-1"><i class="ti ti-layout-list"></i></button>
                     </li>
                 </ul>
             </div>--}}


            <div class="table-responsive card bg-transparent border-0 shadow-none">
                <table class="table table-borderless file-card">
                    <tbody id="fileContents" data-url="{{ request()->url() }}"></tbody>
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
    <script src="{{ asset('assets/libs/typeahead/typeahead.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/dropzone/dropzone.min.js') }}"></script>
    <script>const $Modal = $('#dmsActionsModal');
        Dropzone.options.uploadForm = {
            maxFilesize: 9,//Mb//todo filesize
            acceptedFiles: "{{ implode(", ",ExtensionsEnum::getAllMimeTypes()) }}",
            success: function (file, response) {
                file.previewElement.remove();
                nSuccess(response.message);
                appendFiles(response.data, true);
            },
            error: function (file, message) {
                msg = (typeof message === 'string') ? message : message.message

                nWarning(msg + ' : ' + file.name);
                file.previewElement.remove();
            },
        };
        $(function () {
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
            fetchFiles();

            const filesEngine = new Bloodhound({
                datumTokenizer: function (document) {
                    return Bloodhound.tokenizers.whitespace(document.name);
                },
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: "{{ route('dms.search') }}?type=files&q=%QUERY",
                    wildcard: '%QUERY',
                    filter: function (response) {
                        return response.data;
                    }
                }
            });

            const nhlTeams = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('team'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                prefetch: '{{ asset('nhl.json') }}'
            });

            filesEngine.initialize();

            $('#SearchInput').typeahead({
                    highlight: false
                },
                /* {
                     name: 'nba-teams',
                     display: 'team',
                     source: nbaTeams,
                     templates: {
                         header: '<h6>NBA Teams</h6>'
                     }
                 },*/
                {
                    name: 'files',
                    displayKey: 'value',
                    source: filesEngine.ttAdapter(),
                    limit: 5,
                    templates: {
                        header: '<h6 class="suggestions-header text-primary mb-0 mx-3 mt-3 pb-2">Files</h6>',
                        suggestion: function (document) {
                            return '<a href="' + document.detail + '"><div class="d-flex align-items-center"><img' +
                                ' class="rounded-circle me-3" src="' + document.type.img + '" alt="' + document.id + '" height="32"><div class="user-info"><h6 class="mb-0">' + document.name + '</h6><small class="text-muted">' + document.size.string + "</small></div></div></a>"
                        },
                        notFound: '<div class="not-found px-3 py-2"><h6 class="suggestions-header text-primary mb-2' +
                            '">Files</h6><p class="py-2 mb-0"><i class="bx bx-error-circle bx-xs me-2"></i>' +
                            ' No Results Found</p></div>'
                    }
                },
                {
                    name: 'nhl-teams',
                    display: 'team',
                    source: nhlTeams,
                    templates: {
                        header: '<h6>NHL Teams</h6>'
                    }
                });
        });


        async function fetchFiles() {
            const parent = $('#fileContents'), cmtMsg = $('#filesMessage');
            let url = parent.data('url');
            if (url === null) {
                cmtMsg.html('');
                return;
            }
            cmtMsg.html('<p class="mt-3"><i class="fas fa-spinner fa-spin fa-5x"></i> please wait</p>');
            await $.get(url, function (data) {
                $.map(data.data, function (document) {
                    appendFiles(document);
                });
                url = data.links.next;
                if (url === null) {
                    parent.data('url', null);
                    cmtMsg.html('');
                    return;
                }
                parent.data('url', url);
                cmtMsg.html('<button type="button" class="btn btn-primary" onclick="fetchFiles()">Load more</button>');
            }).fail(function (e) {
                formRequest(e)
            });
        }

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
