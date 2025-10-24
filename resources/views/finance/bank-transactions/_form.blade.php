@php
    $isEdit = isset($row);
    $defaults = $defaults ?? [];
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Fix the following:</strong>
        <ul class="mb-0">@foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach</ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Bank Account <span class="text-danger">*</span></label>
        <select name="BankAccountID" id="BankAccountID" class="form-select" required>
            <option value="">-- select --</option>
            @foreach($bankAccounts as $ba)
                <option
                    value="{{ $ba->AccountID }}" @selected(old('BankAccountID', $row->BankAccountID ?? '')==$ba->AccountID)>
                    {{ optional($ba->bank)->BankName }} — {{ $ba->AccountNumber }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
        @if(($txnTypes ?? collect())->count())
            <select name="TransactionTypeID" id="TransactionTypeID" class="form-select" required>
                <option value="">—</option>
                @foreach($txnTypes as $t)
                    <option value="{{ $t->Id }}" data-desc="{{ $t->Description ?? '' }}"
                        @selected(old('TransactionTypeID', $row->TransactionTypeID ?? '')==$t->Id)>
                        {{ $t->Code }} — {{ $t->Name }}
                    </option>
                @endforeach
            </select>
            <small id="txnTypeHint" class="text-muted d-block"></small>
        @else
            <div class="alert alert-info small mb-0">No mapped transaction types found. Add mappings in Finance GL
                Transactions Mapping.
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" name="DocDate" class="form-control"
               value="{{ old('DocDate', $row->DocDate ?? ($defaults['DocDate'] ?? now()->toDateString())) }}" required>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-3">
        <label class="form-label">Currency <span class="text-danger">*</span></label>
        <select name="CurrencyID" id="CurrencyID" class="form-select" required>
            <option value="">-- select --</option>
            @foreach($currencies as $c)
                <option value="{{ $c->Id }}"
                        data-code="{{ $c->Code }}" @selected(old('CurrencyID', $row->CurrencyID ?? '')==$c->Id)>
                    {{ $c->Code }} — {{ $c->Name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Exch. Rate</label>
        <input type="number" step="0.000001" name="ExchangeRate" id="ExchangeRate" class="form-control"
               value="{{ old('ExchangeRate', $row->ExchangeRate ?? ($defaults['ExchangeRate'] ?? 1)) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="Amount" id="Amount" class="form-control"
               value="{{ old('Amount', $row->Amount ?? '') }}" required>
        <small id="baseCalc" class="text-muted d-block"></small>
    </div>

    <div class="col-md-2">
        <label class="form-label">Reference</label>
        <input name="Reference" class="form-control" value="{{ old('Reference', $row->Reference ?? '') }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">Narration</label>
        <input name="Narration" class="form-control" value="{{ old('Narration', $row->Narration ?? '') }}">
    </div>
</div>

<hr class="my-4">

<div class="d-flex gap-2">
    <button class="btn btn-success">{{ $isEdit ? 'Save' : 'Save Draft' }}</button>
    <a href="{{ $isEdit ? route('finance.banktransactions.show',$row->BankTxnID) : route('finance.banktransactions.index') }}"
       class="btn btn-secondary">Cancel</a>
    @if($isEdit)
        <form action="{{ route('finance.banktransactions.post',$row->BankTxnID) }}" method="POST" class="ms-auto">
            @csrf
            <button type="submit" class="btn btn-primary" {{ $row->Status!=='Draft' ? 'disabled' : '' }}>Post</button>
        </form>
    @endif
</div>

<script>
    (function () {
        const amt = document.getElementById('Amount');
        const rt = document.getElementById('ExchangeRate');
        const base = document.getElementById('baseCalc');
        const tSel = document.getElementById('TransactionTypeID');
        const hint = document.getElementById('txnTypeHint');

        function calc() {
            const a = parseFloat(amt.value || 0), r = parseFloat(rt.value || 1);
            base.textContent = (!isNaN(a) && !isNaN(r)) ? ('Base: ' + (a * r).toFixed(2)) : '';
        }

        function showHint() {
            if (!tSel || !hint) return;
            const opt = tSel.selectedOptions[0];
            hint.textContent = opt?.dataset?.desc || '';
        }

        amt?.addEventListener('input', calc);
        rt?.addEventListener('input', calc);
        tSel?.addEventListener('change', showHint);
        calc();
        showHint();
    })();
</script>
