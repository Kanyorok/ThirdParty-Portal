@php use App\Enums\Core\ExtensionsEnum; use App\Enums\Core\VisibilityEnum; @endphp
@extends('dms.layout')

@section('title', 'Trashed Files')

@section('styles')

@endsection

@section('content')
    <div class="row">
        <div class="col-12 ">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless w-100" id="DocumentsTable">
                            <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Size</th>
                                <th scope="col">Deleted</th>
                                <th scope="col">Deleted By</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
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
                         id="restoreFileModal">
                        <h4>
                            Restore Document <b class="rm-file-name"></b> ?
                        </h4>
                        <form id="restoreFileForm" method="post"> @csrf
                            <div class="mt-4">@method('put')
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-primary float-end" id="restoreFileBtn"
                                        type="submit"><i
                                        class="fas fa-trash-restore"></i> yes, restore
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
    <script>const $Modal = $('#dmsActionsModal');
        let DocumentsTable = null;
        $(function () {
            $(document).on('click', '.file-actions-restore', function () {
                $(".modal-title").html('<b class="text-primary">Trash</b>  : ' + $(this).data('title'));
                $("#restoreFileForm").attr('action', $(this).data('url'));
                $(".rm-file-name").html($(this).data('title'));
                $(".modal-item").addClass('d-none');
                $('#restoreFileModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#restoreFileForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#restoreFileBtn'), false, true, true)) {
                    fetchDocumentsTable();
                    $Modal.modal('hide');
                }
            });

            fetchDocumentsTable();
        });

        function fetchDocumentsTable() {
            if (DocumentsTable === null) {
                DocumentsTable = $('#DocumentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: getDocumentUrl(),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    dom: '<"row"<"col-12"r><"col-12 w-100 my-3"t><"col-6"i><"col-6"p>>',
                    columnDefs: [
                        {
                            "render": function (data, type, row) {
                                return '<div class="d-flex align-items-center"><img src="' + row.Icon + '" alt="file-icon" class="wid-35"><h6 class="mb-0 ms-2 text-truncate">' + row.Visibility + ' ' + row.Name + '</h6> </div></div>';
                            },
                            "targets": 0
                        },
                        {
                            "render": function (data, type, row) {
                                return '<details><summary>' + data + '</summary><p>By : ' + row.deleter.Name + '</p></details> ';
                            },
                            "targets": 2
                        },
                        {
                            "render": function (data, type, row) {
                                return '<div class="btn-group" role="group" aria-label="Basic example"><button type="button" class="btn btn-danger btn-sm disabled"><i class="fas fa-trash-alt"></i> delete</button>' +
                                    '<button type="button" class="btn btn-primary  btn-sm file-actions-restore" data-icon="' + row.Icon + '" data-title=" ' + row.Name + ' " data-url="' + row.action.restore + '"><i class="fas fa-trash-restore-alt"></i> restore</button></div>';
                            },
                            "targets": 4
                        },
                        {"visible": false, "targets": [3]}
                    ],
                    "order": [[2, 'desc']],
                    columns: [
                        {data: 'Name', name: 'Name'},
                        {data: 'current.Size', name: 'current.Size'},
                        {data: 'DeletedOn', name: 'DeletedOn'},
                        {data: 'deleter.Name', name: 'deleter.Name'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "<div class='w-100 text-center my-4'><h4>Nothing to see here! Trash is clear.</h4></div>"
                    }
                });

                DocumentsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the files.");
                });
            } else {
                DocumentsTable.ajax.reload();
            }
        }

    </script>
@endsection
