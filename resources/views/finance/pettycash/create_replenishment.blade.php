@extends('layouts.app')
@section('content')
    <h4 class="mb-3">Petty Cash Replenishment</h4>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('finance.pettycash.store') }}">@csrf
        <input type="hidden" name="VoucherType" value="REPLENISHMENT">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Float</label>
                <select name="FloatID" class="form-select" required>
                    <option value="">-- select --</option>
                    @foreach($floats as $f)
                        <option value="{{ $f->FloatID }}">{{ $f->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Doc Date</label><input type="date" name="DocDate"
                                                                                   class="form-control"
                                                                                   value="{{ now()->toDateString() }}"
                                                                                   required></div>
            <div class="col-md-3">
                <label class="form-label">Currency</label>
                <select name="CurrencyID" class="form-select" required>
                    @foreach($currencies as $c)
                        <option value="{{ $c->Id }}">{{ $c->Code }} — {{ $c->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">Rate</label><input type="number" step="0.000001"
                                                                               name="ExchangeRate" class="form-control"
                                                                               value="1"></div>

            <div class="col-md-4">
                <label class="form-label">Bank Account (Payer)</label>
                <select name="BankAccountID" class="form-select" required>
                    <option value="">-- select --</option>
                    @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->AccountID }}">{{ optional($ba->bank)->BankName }}
                            — {{ $ba->AccountNumber }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4"><label class="form-label">Amount</label><input type="number" step="0.01" name="Amount"
                                                                                 class="form-control" required></div>
            <div class="col-md-2"><label class="form-label">Reference</label><input name="Reference"
                                                                                    class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Narration</label><input name="Narration"
                                                                                    class="form-control"></div>
        </div>

        <hr class="my-4">
        <button class="btn btn-success">Save</button>
        <a class="btn btn-secondary" href="{{ route('finance.pettycash.index') }}">Cancel</a>
    </form>
@endsection
