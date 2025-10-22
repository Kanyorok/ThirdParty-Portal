@php use App\Enums\Core\VisibilityEnum; @endphp
@extends('dms.layout')

@section('title')
    {{ $signature->SignatureId}}
@endsection
@section('styles')

@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
    <li class="breadcrumb-item"><a href="{{ route('document-signature.index') }}">Signatures</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 ">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center h4">{!! $signature->Visibility->icon() !!} {{ $signature->Name }} </h2>
                    <p class="text-center">{{ $signature->SignatureId }}</p>
                    <p class="text-center">Documents : <b>{{ number_format($signature->documents_count) }}</b></p>
                    <p class="text-center">{{ $signature->Description }}</p>

                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header" id="filePropertiesHeader">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#flush-fileProperties" aria-expanded="false"
                                    aria-controls="flush-fileProperties">
                                <span class="h6">Sign Configuration</span>
                            </button>
                        </h2>
                        <div id="flush-fileProperties" class="accordion-collapse collapse"
                             aria-labelledby="filePropertiesHeader" data-bs-parent="#filePropertiesAccordion">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item" style="overflow-wrap: break-word;">Visibility
                                    <b class="text-end">{{ $signature->Visibility->description() }}</b></li>
                                <li class="list-group-item" style="overflow-wrap: break-word;"> Horizontal Start
                                    <b class="text-end">{{ $signature->SignatureHorizontalStart }}</b></li>
                                <li class="list-group-item" style="overflow-wrap: break-word;">Vertical Start
                                    <b class="text-end">{{ $signature->SignatureVerticalStart }}</b></li>
                                <li class="list-group-item" style="overflow-wrap: break-word;"> Opacity (Transparency)
                                    <b class="text-end">{{ $signature->SignatureOpacity }}</b></li>
                                <li class="list-group-item" style="overflow-wrap: break-word;"> Width
                                    <b class="text-end">{{ $signature->SignatureWidth }}</b></li>
                                <li class="list-group-item" style="overflow-wrap: break-word;"> Height
                                    <b class="text-end">{{ $signature->SignatureHeight }}</b></li>
                                <li class="list-group-item" style="overflow-wrap: break-word;">Content
                                    <br>
                                    <p class="{{ $signature->ContentPosition->class() }} h5" style="color: {{ $signature->ContentColour }}; font-family: {{  storage_path('fonts/signature.ttf') }};
                                     text-shadow: {{ $signature->ContentBorderWeight }}px {{ $signature->ContentBorderWeight }}px {{ $signature->ContentBorderWeight }}px {{ $signature->ContentBorderColour }};">
                                        {!! $SignatureContent !!} </p>
                                </li>
                                <li class="list-group-item" style="overflow-wrap: break-word;">Content Position
                                    <b class="text-end">{{ $signature->ContentPosition->description() }}</b></li>
                                <li class="list-group-item" style="overflow-wrap: break-word;">Content Font Size
                                    <b class="text-end">{{ $signature->ContentSize }}</b></li>

                            </ul>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 col-12">
                            @can('update', $signature)
                                <button class="btn btn-primary ms-2 click-summary-data" type="button"
                                        data-click_url="{{ route('document-signature.edit', [$signature->SignatureId]) }}"
                                        data-summary_title='<i class="fas fa-signature"></i> create a signature'>
                                    <i class="fas fa-edit"></i> update
                                </button>
                            @endcan
                        </div>
                        <div class="col-sm-6 col-12">
                            @can('delete', $signature)
                                <button class="btn btn-danger btn-sm modal-trash-signature w-100" type="button"><i
                                        class="fas fa-trash"></i> delete
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    @include('snippets.behind_scenes',['model'=>$signature])
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h5>Documents</h5></div>
                <div class="card-body  table-responsive">
                    <table id="DocumentsTable"
                           class="table dataTable no-footer dtr-inline w-100">
                        <thead>
                        <tr class="d-none">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="SignatureActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="trashSignatureModal">
                        <h4 class="text-danger">
                            Trash Document Signature: <b>{{ $signature->Name }}</b> ?
                        </h4>
                        <form id="trashSignatureForm" method="post"
                              action="{{ route('document-signature.destroy',[$signature->SignatureId]) }}"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashSignatureBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, trash
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
    <script>let DocumentsTable = null;
        const $Modal = $('#SignatureActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchDocumentsTable();

            $(document).on('click', '.modal-trash-signature', function () {
                $(".modal-title").html('Trash Document Signature');
                $(".modal-item").addClass('d-none');
                $('#trashSignatureModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashSignatureForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashSignatureBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
        });

        function fetchDocumentsTable() {
            if (DocumentsTable === null) {
                DocumentsTable = $('#DocumentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '{{ route('document-signature.documents', [$signature->SignatureId]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    dom: '<"row"<"col-12"r><"col-12 w-100 my-3"t><"col-6"i><"col-6"p>>',
                    columnDefs: [
                        //{"className": "text-center", "targets": [3]},
                        {
                            "render": function (data, type, row) {
                                return '<div class="d-flex align-items-center"><img src="' + data + '" alt="file-icon" class="wid-35"><h6 class="mb-0 ms-2 text-truncate">' + row.Visibility + ' ' + row.Name + '</h6> </div></div>';
                            },
                            "targets": 0
                        },
                        /*{
                            "render": function (data, type, row) {
                                return '<div class="d-flex align-items-center"><img src="'+data+'" alt="file-icon" class="wid-35"></div>';
                            },
                            "targets": 1
                        },*/
                        {"visible": false, "targets": [1, 2]}
                    ],
                    columns: [
                        {data: 'Icon', name: 'Icon', searchable: false, orderable: false},
                        {data: 'Name', name: 'Name'},
                        {data: 'Visibility', name: 'Visibility'},
                        {data: 'current.Size', name: 'current.Size'},
                        {data: 'Repository', name: 'Repository'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                    ]
                });

                DocumentsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the documents.");
                    console.log(er);
                });
            } else {
                DocumentsTable.ajax.reload();
            }
        }

    </script>
@endsection
