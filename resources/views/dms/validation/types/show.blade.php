@php use App\Enums\Core\VisibilityEnum; @endphp
@extends('dms.layout')

@section('title')
    {{ $type->ValidationTypeId}}
@endsection
@section('styles')

@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
    <li class="breadcrumb-item"><a href="#">Settings</a></li>
    <li class="breadcrumb-item"><a href="{{ route('document-validation-type.index') }}">Validation Types</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 ">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center h4">{!! $type->Visibility->icon() !!} {{ $type->Name }} </h2>
                    <p class="text-center">{{ $type->ValidationTypeId }}</p>
                    <p class="text-center">Validations : <b>{{ number_format($type->validations_count) }}</b></p>
                    <p class="text-center">{{ $type->Notes }}</p>

                    <div class="row">
                        <div class="col-sm-6 col-12">
                            @can('update', $type)
                                <button class="btn btn-primary ms-2 click-summary-data" type="button"
                                        {{--data-click_url="{{ route('document-signature.edit', [$type->ValidationTypeId]) }}"--}}
                                        data-summary_title='<i class="fas fa-signature"></i> create a signature'>
                                    <i class="fas fa-edit"></i> update
                                </button>
                            @endcan
                        </div>
                        <div class="col-sm-6 col-12">
                            @can('delete', $type)
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
                    @include('snippets.behind_scenes',['model'=>$type])
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h5>Approvers</h5></div>
                <div class="card-body  table-responsive">
                    <table id="typeApproversTable"
                           class="table table-striped no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Party</th>
                            <th>Dated</th>
                            <th>action</th>
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
                         id="trashValidationTypeModal">
                        <h4 class="text-danger">
                            Trash Validation Type: <b>{{ $type->Name }}</b> ?
                        </h4>
                        <form id="trashValidationTypeForm" method="post"
                              action="{{ route('document-validation-type.destroy',[$type->ValidationTypeId]) }}"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashValidationTypeBtn"
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
    <script>
        const $Modal = $('#SignatureActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchApproversTable();

            $(document).on('click', '.modal-trash-signature', function () {
                $(".modal-title").html('Trash Document Signature');
                $(".modal-item").addClass('d-none');
                $('#trashValidationTypeModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashValidationTypeForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashValidationTypeBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
        });

        function fetchApproversTable() {
            if (!$.fn.DataTable.isDataTable('#typeApproversTable')) {
                $('#typeApproversTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    ajax: {
                        url: '{{ route('doc-validation-type-approvers.index',[$type->ValidationTypeId]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'party', name: 'party'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no approvers under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading approvers.");
                });
            } else {
                $('#typeApproversTable').DataTable().ajax.reload();
            }
        }

    </script>
@endsection
