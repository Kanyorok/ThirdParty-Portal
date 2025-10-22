@php
    $isEdit = isset($row);
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
        <label class="form-label">From Account <span class="text-danger">*</span></label>
        <select name="FromBankAccountID" id="FromBankAccountID" class="form-select" required>
            <option value="">-- select --</option>
            @foreach($bankAccounts as $ba)
                <option
                    value="{{ $ba->AccountID }}" @selected(old('FromBankAccountID', $row->FromBankAccountID ?? '')==$ba->AccountID)>
                    {{ optional($ba->bank)->BankName }} — {{ $ba->AccountNumber }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">To Account <span class="text-danger">*</span></label>
        <select name="ToBankAccountID" id="ToBankAccountID" class="form-select" required>
            <option value="">-- select --</option>
            @foreach($bankAccounts as $ba)
                <option
                    value="{{ $ba->AccountID }}" @selected(old('ToBankAccountID', $row->ToBankAccountID ?? '')==$ba->AccountID)>
                    {{ optional($ba->bank)->BankName }} — {{ $ba->AccountNumber }}
                </option>
            @endforeach
        </select>
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

    <div class="col-md-4">
        <label class="form-label">Clearing GL</label>
        <input type="number" name="ClearingGLAccountID" class="form-control"
               value="{{ old('ClearingGLAccountID', $row->ClearingGLAccountID ?? ($defaults['ClearingGLAccountID'] ?? '')) }}">
        <small class="text-muted">Used as counter-GL on both legs (DR on payment, CR on receipt).</small>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-3">
        <label class="form-label">Reference</label>
        <input name="Reference" class="form-control" value="{{ old('Reference', $row->Reference ?? '') }}">
    </div>
    <div class="col-md-9">
        <label class="form-label">Narration</label>
        <input name="Narration" class="form-control" value="{{ old('Narration', $row->Narration ?? '') }}">
    </div>
</div>

<hr class="my-4">

<div class="d-flex gap-2">
    <button class="btn btn-success">{{ $isEdit ? 'Save' : 'Save Draft' }}</button>
    <a href="{{ $isEdit ? route('finance.banktransfers.show',$row->TransferID) : route('finance.banktransfers.index') }}"
       class="btn btn-secondary">Cancel</a>
    @if($isEdit)
        <form action="{{ route('finance.banktransfers.post',$row->TransferID) }}" method="POST" class="ms-auto">
            @csrf
            <button type="submit" class="btn btn-primary" {{ $row->Status!=='Draft' ? 'disabled' : '' }}>Post Transfer
            </button>
        </form>
    @endif
</div>

<script>
    (function () {
        const amt = document.getElementById('Amount');
        const rt = document.getElementById('ExchangeRate');
        const base = document.getElementById('baseCalc');

        function calc() {
            const a = parseFloat(amt.value || 0), r = parseFloat(rt.value || 1);
            if (!isNaN(a) && !isNaN(r)) base.textContent = 'Base: ' + (a * r).toFixed(2); else base.textContent = '';
        }

        amt?.addEventListener('input', calc);
        rt?.addEventListener('input', calc);
        calc();
    })();
</script>
