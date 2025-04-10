@php use App\Enums\TicketPriorityEnum; use App\Enums\TicketStatusEnum; @endphp
@extends('layouts.app')

@section('title', 'My Tickets')

@section('styles')
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-6 col-12"><h4 class="h3">@yield('title')</h4></div>
        <div class="col-sm-2 col-12">
            <div class="mb-2">
                {{--  <button type="button" class="btn btn-primary w-100"><i class="fas fa-plus"></i> save</button> --}}
                &nbsp;
            </div>

        </div>

        <div class="col-sm-2 col-6">
            <div class="mx-1 mb-2">
                <select class="form-control w-100 filter-field" name="TicketPriority" id="TicketPriority">
                    <option value="all" selected>Any & All</option>
                    @foreach(App\Enums\TicketPriorityEnum::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-2 col-6">
            <div class="mx-1 mb-2">
                <select class="form-control w-100 filter-field" name="TicketStatus" id="TicketStatus">
                    <option value="all">Any & All</option>
                    @foreach(App\Enums\TicketStatusEnum::class::cases() as $status)
                        <option value="{{ $status->value }}"
                            {{ (TicketStatusEnum::Active->value === $status->value)?'selected':'' }}>{{ $status->name }}</option>
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
                            <th>Party</th>
                            <th>Category</th>
                            <th>Priority</th>
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
        });

        async function fetchTicketsTable() {
            let filter_fields = $('.filter-field');
            filter_fields.addClass('disabled');
            let url = document.documentURI + '?_status=' + $('#TicketStatus').val() + '&_priority=' + $("#TicketPriority").val();

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
                    "order": [[0, 'desc']],
                    /*  "columnDefs": [
                          {"className": "text-center", "targets": [2]}
                      ],*/
                    ajax: {
                        url: url,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'TicketID', name: 'TicketID'},
                        {data: 'Title', name: 'Title'},
                        {data: 'party', name: 'party'},
                        {data: 'category', name: 'category'},
                        {data: 'Priority', name: 'Priority'},
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
