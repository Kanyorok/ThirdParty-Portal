@php use App\Models\BR\Client; @endphp
@extends('layouts.app')

@section('title')
    {{ $account->AccountID }}
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4 col-xl-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="mt-2 mb-0">{{  $account->AccountID }}</h2>
                    <h5 class="mt-2 mb-0">{{ $account->product->Description }}</h5>
                    {{--<h5 class="mt-2 mb-0">{!!  ($type === 'I')
                                   ?'<small>ID No. </small>&nbsp;'.$client->individual?->PassportNo
                                   : '<small>Cert. No. </small>&nbsp;'.$client->corporate?->CertificateNo  !!}
                   </h5>
                   <h5 class="mt-2 mb-0">{{ $client->type->Description }}</h5>--}}

                </div>
                <hr class="my-0">
                @if($account->client instanceof Client)
                    <a class="card-body d-flex align-items-start text-decoration-none"
                       href="{{ route('clients.show',$account->client->ClientID) }}">
                        {!! $account->client->getImage('width="72" height="72" class="rounded-circle me-2" alt=".."') !!}
                        <div class="flex-grow-1 h6">
                            <div style="margin: 10px;">
                                <strong>{{ $account->client->Name }}</strong><br>
                                {{ $account->client->ClientID }}
                            </div>
                        </div>
                    </a>
                @endif
            </div>
        </div>
        <div class="col-md-8 col-xl-9">
            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">Transactions</a></li>

                </ul>
                <div class="tab-content">
                    <div class="tab-pane active m-2" id="tab-1" role="tabpanel">
                        <table id="trxAccountTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Dated</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')

    <script>let trxAccountTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            $(document).on('click', '.clear-balance', function () {
                if ($(this).html() === '**********') {
                    $(this).html($(this).data('bal'));
                } else {
                    $(this).html('**********');
                }
            });
            fetchAccountsTable();
        });


        function fetchAccountsTable() {
            if (trxAccountTable === null) {
                trxAccountTable = $('#trxAccountTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[4, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: document.URL,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'TrxRowID', name: 'TrxRowID'},
                        {data: 'TrxDescription', name: 'TrxDescription'},
                        {data: 'TrxAmount', name: 'TrxAmount'},
                        {
                            data: 'type.Description',
                            "mRender": function (data, type, full) {
                                return full.type.Description;
                            }
                        },
                        {data: 'ValueDate', name: 'ValueDate'},
                    ], "oLanguage": {
                        "sEmptyTable": "<div class='text-center'><p>No Transactions found for account {{  $account->AccountID  }}</h3></div>"
                    }
                });

                trxAccountTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the transactions.");
                    console.log(er);
                });
            } else {
                trxAccountTable.ajax.reload();
            }
        }
    </script>
@endsection
