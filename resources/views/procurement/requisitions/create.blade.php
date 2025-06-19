@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Requisitions')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="mb-3">
        <h1 class="h3 d-inline align-middle">@yield('title')</h1>
        <button class="btn btn-primary float-end ms-2 modal-create-item" type="button"><i
                class="fas fa-plus-circle"></i> New
            Requisition
        </button>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="requsitionTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Requisition No</th>
                            <th>Requisition Date</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Remarks</th>
                            <th>Total Items</th>
                            <th>Total Cost</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($details as $item)
                            <tr>
                                {{--                                <td>{{$item->Id}}</td>--}}
                                <td>{{$loop->iteration }}</td>
                                <td>{{ $item->RequisitionNo }}</td>
                                <td>{{ Carbon::parse($item->CreatedOn)->format('d-m-Y') }}</td>
                                <td>{{ $item->BranchID }}</td>
                                <td>{{ $item->DepartmentID }}</td>
                                <td>{{ $item->Remarks }}</td>
                                <td>{{ $item->itemcount }}</td>
                                <td>{{ number_format($item->ExpectedPrice, 2) }}</td>
                                <td>{{ $item->Status }}</td>
                                <td><a href="{{ route('requisition.show',[ $item->Id]) }}" class="btn btn-info btn-sm">View</a>
                                    <a href="{{ route('requisition.approval',[ $item->Id]) }}" class="btn btn-success btn-sm">Approve</a>
                                </td>


                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center">No requisition items found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="RequisitionItemModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createRequisition">
                        <form action="{{ route('requisition.store') }}" method="post" id="createRequisitionForm">
                            @csrf

                            {{--                            <div class="mb-3">--}}
                            {{--                                <label class="form-label" for="Category">Category <span class="text-danger">*</span></label>--}}
                            {{--                                <select class="form-control" name="Category" id="Category" required>--}}
                            {{--                                    <option selected disabled>Select Category</option>--}}
                            {{--                                    <option>Purchase Requisition</option>--}}
                            {{--                                    <option>Tender</option>--}}

                            {{--                                    --}}{{-- @foreach ($MarketingLists as $MarketingList)--}}
                            {{--                                        <option value="{{ $MarketingList->slug }}">{{ $MarketingList->Label }}</option>--}}
                            {{--                                    @endforeach --}}
                            {{--                                </select>--}}

                            {{--                                <p id="Category_error" class="invalid-feedback d-none error col-12" role="alert"></p>--}}
                            {{--                            </div>--}}


                            <div class="mb-3">
                                <label class="form-label" for="Branch">Branch <span class="text-danger">*</span></label>
                                <select class="form-control" name="Branch" id="Branch" required>
                                    <option selected disabled>Select Branch</option>
                                    @foreach ($branches as $Branch)
                                        <option value="{{ $Branch->Id }}">{{ $Branch->Name }}</option>
                                    @endforeach
                                </select>

                                <p id="Branch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="Department">Department <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="Department" id="Department" required>
                                    <option selected disabled>Select Department</option>
                                    @foreach ($departments as $Department)
                                        <option value="{{ $Department->Id }}">{{ $Department->Name }}</option>
                                    @endforeach

                                </select>

                                <p id="Department_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="ProcurementPlan">Procurement Plan</label>
                                <select class="form-control" name="ProcurementPlan" id="ProcurementPlan">
                                    <option selected value="">Select Procurement Plan</option>
                                    @foreach ($procurementPlans  as $procurementPlan )
                                        <option
                                            value="{{ $procurementPlan->PlanID }}">{{ $procurementPlan->ReferenceNumber }}</option>
                                    @endforeach
                                </select>

                                <p id="ProcurementPlan_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>

                            <div class="mb-3">
                                <div>
                                    <label class="form-label" for="Remarks">Remarks <span
                                    class="text-danger">*</span</label>
                                </div>
                                <textarea name="Remarks" id="Remarks" rows="3" class="form-control" maxlength="1000" required></textarea>
                                <p id="Remarks_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start" data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createRequisitionBtn" type="submit"><i
                                        class="fas fa-save"></i> Add Requisition
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
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>
        const $Modal = $('#RequisitionItemModal');
        $(function () {
            // $.fn.dataTable.ext.errMode = 'none';
            // fetchCampaignsTable();

            $(document).on('click', '.modal-create-item', function () {
                $(".modal-title").html('Add Requisition');
                $(".modal-item").addClass('d-none');
                $('#createRequisition').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createRequisitionForm').submit(async function (e) {
                // alert('hello');
                e.preventDefault();
                if (await saveForm($(this), $('#createRequisitionBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }

            });


            $("#MarketingList").select2({
                dropdownParent: $Modal,
            });

        });

        // function fetchCampaignsTable() {
        //     if (!$.fn.DataTable.isDataTable('#requsitionTable')) {
        //         $('#requsitionTable').DataTable({
        //             processing: true,
        //             serverSide: true,
        //             responsive: true,
        //             // "order": [[3, 'asc']],
        //             "columnDefs": [
        //                 {"className": "text-center", "targets": [2]}
        //             ],
        //             ajax: {
        //                 url: getDocumentUrl(),
        //                 error: function (request) {
        //                     if (request.status === 400 && request.responseJSON.message) {
        //                         nWarning(request.responseJSON.message);
        //                     } else {
        //                         codeNotify(request.status);
        //                     }
        //                 }
        //             },
        //             columns: [
        //                 {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
        //                 {data: 'Label', name: 'Label'},
        //                 {data: 'Status', name: 'Status'},
        //                 {data: 'contacts_count', name: 'contacts_count'},
        //                 {data: 'CreatedOn', name: 'CreatedOn'},
        //                 {data: 'action', name: 'action', orderable: false, searchable: false},
        //             ], "oLanguage": {
        //                 "sEmptyTable": "no campaigns under this filter"
        //             }
        //         }).on('error', function () {
        //             nWarning("an issue occurred while loading campaigns.");
        //         });
        //     } else {
        //         $('#requsitionTable').DataTable().ajax.reload();
        //     }
        // }
    </script>
@endsection
