@php use App\Enums\TicketPriorityEnum; use App\Enums\TicketStatusEnum; @endphp
@extends('layouts.app')

@section('title','Tickets')
@section('styles')
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-4 col-12"><h4 class="h3">@yield('title')</h4></div>
        <div class="col-sm-2 col-12">
            <div class="mb-2">
                <button type="button"
                        class="btn btn-outline-primary text-center w-100 add-party-ticket-btn"
                        data-action="{{ route('tickets.store') }}">
                    <i class="align-middle" data-feather="check-square"></i> add a ticket
                </button>
            </div>
        </div>
        <div class="col-sm-2 col-12">
            <div class="mx-1 mb-2">
                <select class="form-control w-100 filter-field" name="TicketPriority" id="TicketPriority">
                    <option value="all" selected>Priority: Any & All</option>
                    @foreach(App\Enums\TicketPriorityEnum::cases() as $status)
                        <option value="{{ $status->value }}">Priority: {{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-2 col-6">
            <div class="mx-1 mb-2">
                <select class="form-control w-100 filter-field" name="TicketUser" id="TicketUser">
                    @can('viewAny', \App\Models\Ticket::class)
                        <option value="all">Assigned: Any</option>
                        <option value="none">Assigned: None</option>
                    @endcan
                    <option selected value="{{ auth()->user()->UserID }}">
                        Assigned: {{ auth()->user()->UserID }}</option>
                </select>
            </div>
        </div>
        <div class="col-sm-2 col-6">
            <div class="mx-1 mb-2">
                <select class="form-control w-100 filter-field" name="TicketStatus" id="TicketStatus">
                    <option value="all">Status: Any & All</option>
                    @foreach(App\Enums\TicketStatusEnum::class::cases() as $status)
                        <option value="{{ $status->value }}"
                            {{ (TicketStatusEnum::Active->value === $status->value)?'selected':'' }}>
                            Status: {{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="ticketsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Party</th>
                            <th>Category</th>
                            <th>Dated</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @include('snippets.actions.tickets')
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>
        let ticketsTable = null, ticketsTableRoute = '';
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchTicketsTable();

            $('#TicketStatus').on('change', async function () {
                await fetchTicketsTable();
            });

            $('#TicketPriority').on('change', async function () {
                await fetchTicketsTable();
            });
            $('#TicketUser').on('change', async function () {
                await fetchTicketsTable();
            });
        });

        async function fetchTicketsTable() {
            let filter_fields = $('.filter-field');
            filter_fields.addClass('disabled');

            let url = getDocumentUrl() + '?_status=' + $('#TicketStatus').val() + '&_priority=' + $("#TicketPriority").val() + '&_user=' + $("#TicketUser").val();

            /* if ($.fn.DataTable.isDataTable('#ticketsTable')) {
                 $("#ticketsTable").destroy();
             }*/
            if (url !== ticketsTableRoute) {
                if (ticketsTable != null) {
                    ticketsTable.destroy();
                    ticketsTable = null;
                }
                ticketsTableRoute = url;
            }
            await fetchTickets(url);
            filter_fields.removeClass('disabled');
        }

        function fetchTickets(url) {
            if (ticketsTable === null) {
                ticketsTable = $('#ticketsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[5, 'desc']],
                    ajax: {
                        url: url,
                        error: function (request) {
                            if (request.status === 400 && request.responseJSON.message) {
                                nWarning(request.responseJSON.message);
                            } else {
                                codeNotify(request.status);
                            }
                        }
                    },
                    columns: [
                        {data: 'TicketID', name: 'TicketID'},
                        {data: 'Title', name: 'Title'},
                        {data: 'Status', name: 'Status'},
                        {data: 'party', name: 'party'},
                        {data: 'category', name: 'category.Description'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "no tickets found here"
                    }
                });

                ticketsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading tickets.");
                    console.log(er);
                });
            } else {
                ticketsTable.ajax.reload();
            }
        }

    </script>
@endsection
