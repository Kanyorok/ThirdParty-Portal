<div>
    <h4 class="text-center">{{ $account->AccountID }}</h4>
    <ul class="list-group list-group-flush">
        <li class="list-group-item"><b>Branch </b><span class="float-end">{{ $account->OurBranchID }} -
                    @if($account->branch instanceof \App\Models\BR\Branch)
                    {{ $account->branch->BranchName }}
                @endif
                </span></li>
        <li class="list-group-item"><b>Product </b><span class="float-end"> {{ $account->ProductID }} -
                    @if($account->product instanceof \App\Models\BR\Product)
                    {{ $account->product?->Description }}
                @endif
                </span></li>
        <li class="list-group-item"><b>Clear Balance </b>
            <span style="cursor: pointer;" class="clear-balance float-end"
                  data-bal="{{ number_format($account->ClearBalance,4)}}">**********</span>
        </li>
    </ul>
    <hr class="m-0">
    <p class="my-1 h4">Client </p>
    <div class="row">
        <div class="col-5 align-content-center text-center">
            <div>
                {!! $client->getImage('class="img-fluid rounded-circle mb-2" width="128" height="128"') !!}
            </div>
        </div>
        <div class="col-7 align-content-center">
            <ul class="list-group list-group-flush">
                <li class="list-group-item p-1"><a href="{{ route('clients.show',[$client->ClientID]) }}"
                                                   class="h4 text-primary text-uppercase text-decoration-underline">{{ $client->ClientID }}</a>
                </li>
                <li class="list-group-item p-1">{{ $client->Name }}</li>
                <li class="list-group-item p-1">{{ $client->type?->Description }}</li>
            </ul>
        </div>
    </div>
    <hr class="m-0">
    <p class="mb-1 h4">Latest Transactions </p>
    <div class="row">
        <ul class="list-group list-group-flush">
            @forelse ($transactions as $transaction)
                <li class="list-group-item">
                    <details>
                        <summary><b>{{ $transaction->type?-> Description }}</b> : {{ $transaction->TrxDescription }}
                        </summary>
                        <p><b>Transaction Id</b> : {{ $transaction->TrxRowID }} </p>
                        <p><b>Amount</b> : {{ number_format($transaction->TrxAmount,2) }}</p>
                        <p><b>Dated</b> : {{ $transaction->ValueDate?->format('M d, Y h:iA') }}</p>
                    </details>
                </li>
            @empty
                <p class="text-center my-5 ">No transactions found <i class="fas fa-sad-tear text-warning"></i></p>
            @endforelse
        </ul>
    </div>
</div>
<script>

</script>
