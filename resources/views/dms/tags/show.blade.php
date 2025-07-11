@php use App\Enums\Core\VisibilityEnum; @endphp
@extends('dms.layout')

@section('title')
    {{ Str::limit($tag->TagID,50) }}
@endsection
@section('styles')

@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
    <li class="breadcrumb-item"><a href="{{ route('file-tags.index') }}">Tags</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 ">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center">{!! $tag->Visibility->icon() !!} {{ $tag->Name }} </h2>
                    <p class="text-center">{{ $tag->TagID }}</p>
                    <p class="text-center">Documents : <b>{{ number_format($tag->documents_count) }}</b></p>
                    <p class="text-center">{{ $tag->Description }}</p>

                    <div class="row">
                        <div class="col-sm-6 col-12">
                            @can('update', $tag)
                                <button class="btn btn-primary btn-sm modal-update-tag w-100" type="button"><i
                                        class="fas fa-edit"></i> update
                                </button>
                            @endcan
                        </div>
                        <div class="col-sm-6 col-12">
                            @can('delete', $tag)
                                <button class="btn btn-danger btn-sm modal-trash-tag w-100" type="button"><i
                                        class="fas fa-trash"></i> delete
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    @include('snippets.behind_scenes',['model'=>$tag])
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body py-0">
                    <ul class="nav nav-tabs profile-tabs" id="TagsTab" role="tablist">

                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="document-tags-tab-2" href="#tab-1" data-bs-toggle="tab"
                               role="tab"
                               aria-selected="false" onclick="fetchDocumentsTable()">
                                <i class="fas fa-file-lines me-2"></i> Documents</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link " id="document-tags-tab-1" data-bs-toggle="tab" href="#tab-0"
                               role="tab"
                               aria-selected="false" tabindex="-1" onclick="fetchTaggingRulesTable()">
                                <i class="fas fa-gavel me-2"></i>Tagging Rules</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content">
                <div class="tab-pane" id="tab-0" role="tabpanel" aria-labelledby="document-tags-tab-1">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-actions float-end">
                                        <button type="button" class="btn btn-sm btn-primary click-summary-data"
                                                data-summary_title='New Tagging Rule'
                                                data-click_url='{{ route('tagging-rules.create',[$tag->TagID]) }}'
                                        ><i class="fas fa-plus-circle"></i> create a new rule
                                        </button>
                                    </div>
                                    <h5>Tagging Rules</h5></div>
                                <div class="card-body  table-responsive">
                                    <table id="TaggingRulesTable"
                                           class="table table-striped dataTable no-footer dtr-inline w-100">
                                        <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>What</th>
                                            <th>How</th>
                                            <th>Value</th>
                                            <th>actions</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane active show" id="tab-1" role="tabpanel" aria-labelledby="document-tags-tab-2">
                    <div class="row">
                        <div class="col-12">
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
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="TagActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateTagModal">
                        <form action="{{ route('file-tags.update',[$tag->TagID]) }}" method="post"
                              id="updateTagForm"> @csrf
                            <div class="mb-3">@method('put')
                                <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Name" name="Name" required
                                       placeholder="Name" value="{{ $tag->Name }}">
                                <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Visibility">Visibility <span class="text-danger">*</span></label>
                                <select class="form-control" name="Visibility" id="Visibility" required>
                                    @foreach(VisibilityEnum::cases() as $Visibility)
                                        <option
                                            value="{{ $Visibility->value }}" {{ ($Visibility->value===$tag->Visibility->value)?'selected':'' }}>{!! $Visibility->icon() !!} {{ $Visibility->description() }}</option>
                                    @endforeach
                                </select>
                                <p id="Visibility_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Description">Description </label>
                                <textarea name="Description" id="Description" rows="3" class="form-control"
                                          maxlength="1000">{{ $tag->Description }}</textarea>
                                <p id="Description_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateTagBtn" type="submit"><i
                                        class="fas fa-save"></i>
                                    update {{ \Illuminate\Support\Str::limit($tag->TagID ,20) }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center" id="trashTagModal">
                        <h4 class="text-danger">
                            Trash Document Tag: <b>{{ $tag->Name }}</b> ?
                        </h4>
                        <form id="trashTagForm" method="post"
                              action="{{ route('file-tags.destroy',[$tag->TagID]) }}"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashTagBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes,
                                    trash {{ \Illuminate\Support\Str::limit($tag->TagID ,20) }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="trashTaggingRuleModal">
                        <h4 class="text-danger">
                            Trash Document Tagging rule : <br> <b id="trashTaggingRule"></b> ?
                        </h4>
                        <form id="trashTaggingRuleForm" method="post"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashTaggingRuleBtn"
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
    <script>let DocumentsTable = null, TaggingRulesTable = null;
        const $Modal = $('#TagActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchDocumentsTable();

            $(document).on('click', '.modal-trash-rule', function () {
                $(".modal-title").html('<b class="text-danger">Remove </b> tagging rule');
                $(".modal-item").addClass('d-none');
                $("#trashTaggingRuleForm").attr('action', $(this).data('click_url'));
                $("#trashTaggingRule").html($(this).data('info'));
                $('#trashTaggingRuleModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashTaggingRuleForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashTaggingRuleBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchTaggingRulesTable();
                }
            });
            $(document).on('click', '.modal-trash-tag', function () {
                $(".modal-title").html('Trash Tag : {{ $tag->Name }}');
                $(".modal-item").addClass('d-none');
                $('#trashTagModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashTagForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashTagBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.modal-update-tag', function () {
                $(".modal-title").html('Update Tag : {{ $tag->Name }}');
                $(".modal-item").addClass('d-none');
                $('#updateTagModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#updateTagForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#updateTagBtn'), true, true, true)) {
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
                        url: '{{ route('file-tags.files', [$tag->TagID]) }}',
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
                    nWarning("an issue occurred while loading the list.");
                    console.log(er);
                });
            } else {
                DocumentsTable.ajax.reload();
            }
        }

        function fetchTaggingRulesTable() {
            if (TaggingRulesTable === null) {
                TaggingRulesTable = $('#TaggingRulesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '{{ route('tagging-rules.index', [$tag->TagID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Content', name: 'Content'},
                        {data: 'Comparison', name: 'Comparison'},
                        {data: 'Value', name: 'Value'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "No automated tagging rules found for this tag.",
                    }
                });

                TaggingRulesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading tagging rules.");
                    console.log(er);
                });
            } else {
                TaggingRulesTable.ajax.reload();
            }
        }
    </script>
@endsection
