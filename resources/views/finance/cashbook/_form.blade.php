@php
    $isEdit   = isset($entry);
    $etype    = old('EntryType', $entry->EntryType ?? ($presetType ?? 'RECEIPT')); // default receipt
    $bankId   = old('BankAccountID', $entry->BankAccountID ?? '');
    $curId    = old('CurrencyID', $entry->CurrencyID ?? '');
    $exRate   = old('ExchangeRate', $entry->ExchangeRate ?? 1);
    $amount   = old('Amount', $entry->Amount ?? '');
    $rows     = old('lines', isset($entry) ? $entry->lines->map(fn($l)=>$l->only(['GLAccountID','Description','AmountDr','AmountCr']))->toArray() : []);
    $txnId    = old('TransactionTypeID', $entry->TransactionTypeID ?? '');
    $useAuto  = old('UseAutoGL', $entry->UseAutoGL ?? 1);
@endphp

@if ($errors->any())
<div class="alert alert-danger">
    <strong>Fix the following:</strong>
    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="row g-3 align-items-end">
    <div class="col-md-3">
        <label class="form-label d-block">Type <span class="text-danger">*</span></label>
        <div class="btn-group" role="group" aria-label="Entry Type">
            <input type="radio" class="btn-check" name="EntryType" id="etypeR" value="RECEIPT" autocomplete="off"
                   @checked($etype==='RECEIPT') {{ $isEdit ? 'disabled' : '' }}>
            <label class="btn btn-outline-success" for="etypeR">Receipt</label>

            <input type="radio" class="btn-check" name="EntryType" id="etypeP" value="PAYMENT" autocomplete="off"
                   @checked($etype==='PAYMENT') {{ $isEdit ? 'disabled' : '' }}>
            <label class="btn btn-outline-danger" for="etypeP">Payment</label>
        </div>
        @if($isEdit)
            <input type="hidden" name="EntryType" value="{{ $etype }}">
        @endif
    </div>

    <div class="col-md-4">
        <label class="form-label">Bank Account <span class="text-danger">*</span></label>
        <select name="BankAccountID" id="BankAccountID" class="form-select" required>
            <option value="">-- select --</option>
            @foreach($bankAccounts as $ba)
                <option value="{{ $ba->AccountID }}" data-gl="{{ $ba->GLAccountID ?? '' }}" @selected($bankId==$ba->AccountID)>
                    {{ optional($ba->bank)->BankName }} — {{ $ba->AccountNumber }}
                    @if(!empty($ba->GLAccountID)) (GL {{ $ba->GLAccountID }}) @endif
                </option>
            @endforeach
        </select>
        <small class="text-muted">Bank GL will be posted from account setup; counter-GLs are below.</small>
    </div>

    <div class="col-md-2">
        <label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" name="DocDate" class="form-control"
               value="{{ old('DocDate', isset($entry)?$entry->DocDate:now()->toDateString()) }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Doc No.</label>
        <input name="DocNo" class="form-control" value="{{ old('DocNo', $entry->DocNo ?? '') }}">
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-3">
        <label class="form-label">Currency <span class="text-danger">*</span></label>
        <select name="CurrencyID" id="CurrencyID" class="form-select" required>
            <option value="">-- select --</option>
            @foreach($currencies as $cur)
                <option value="{{ $cur->Id }}" data-code="{{ $cur->Code }}" data-digits="{{ $cur->DecimalDigits }}"
                    @selected($curId==$cur->Id)>{{ $cur->Code }} — {{ $cur->Name }}</option>
            @endforeach
        </select>
        <small id="currencyHint" class="text-muted d-block"></small>
    </div>

    <div class="col-md-2">
        <label class="form-label">Exch. Rate</label>
        <input type="number" step="0.000001" name="ExchangeRate" id="ExchangeRate" class="form-control" value="{{ $exRate }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="Amount" id="Amount" class="form-control" value="{{ $amount }}" required>
        <small id="baseCalc" class="text-muted d-block"></small>
    </div>

    <div class="col-md-2">
        <label class="form-label">Reference</label>
        <input name="Reference" class="form-control" value="{{ old('Reference', $entry->Reference ?? '') }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">Transaction Type</label>
        <select name="TransactionTypeID" id="TransactionTypeID" class="form-select">
            <option value="">—</option>
            @foreach($txnTypes as $t)
                <option value="{{ $t->Id }}" data-code="{{ $t->Code }}" data-desc="{{ $t->Description ?? '' }}"
                        @selected($txnId==$t->Id)>
                    {{ $t->Code }} — {{ $t->Name }}
                </option>
            @endforeach
        </select>
        <small id="txnTypeHint" class="text-muted d-block"></small>
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="UseAutoGL" id="UseAutoGL" value="1"
                   {{ $useAuto ? 'checked' : '' }}>
            <label class="form-check-label" for="UseAutoGL">Auto GL (mapping)</label>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-3">
        <label class="form-label">Party Type</label>
        <select name="PartyType" id="PartyType" class="form-select">
            <option value="">—</option>
            <option value="CUSTOMER" @selected(old('PartyType', $entry->PartyType ?? '')==='CUSTOMER')>Customer</option>
            <option value="VENDOR"   @selected(old('PartyType', $entry->PartyType ?? '')==='VENDOR')>Vendor</option>
            <option value="OTHER"    @selected(old('PartyType', $entry->PartyType ?? '')==='OTHER')>Other</option>
        </select>
    </div>
    <div class="col-md-3 party-only d-none">
        <label class="form-label">Party ID</label>
        <input type="number" name="PartyID" class="form-control" value="{{ old('PartyID', $entry->PartyID ?? '') }}">
    </div>
    <div class="col-md-6 party-only d-none">
        <label class="form-label">Party Name</label>
        <input name="PartyName" class="form-control" value="{{ old('PartyName', $entry->PartyName ?? '') }}">
    </div>
    <div class="col-md-12">
        <label class="form-label">Narration</label>
        <input name="Narration" class="form-control" value="{{ old('Narration', $entry->Narration ?? '') }}">
    </div>
</div>

<hr class="my-4">

<h5 class="mt-0">Counter-GL Split</h5>
<div class="table-responsive">
<table class="table table-sm align-middle" id="glTable">
    <thead>
        <tr class="table-light">
            <th style="width:16%">GL Account ID</th>
            <th>Description</th>
            <th style="width:14%">Debit</th>
            <th style="width:14%">Credit</th>
            <th style="width:8%"></th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $idx => $ln)
            <tr>
                <td><input name="lines[{{ $idx }}][GLAccountID]" class="form-control" value="{{ $ln['GLAccountID'] ?? '' }}"></td>
                <td><input name="lines[{{ $idx }}][Description]" class="form-control" value="{{ $ln['Description'] ?? '' }}"></td>
                <td><input type="number" step="0.01" name="lines[{{ $idx }}][AmountDr]" class="form-control ln-dr" value="{{ $ln['AmountDr'] ?? '' }}"></td>
                <td><input type="number" step="0.01" name="lines[{{ $idx }}][AmountCr]" class="form-control ln-cr" value="{{ $ln['AmountCr'] ?? '' }}"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalcTotals();">Del</button></td>
            </tr>
        @empty
            <tr>
                <td><input name="lines[0][GLAccountID]" class="form-control"></td>
                <td><input name="lines[0][Description]" class="form-control"></td>
                <td><input type="number" step="0.01" name="lines[0][AmountDr]" class="form-control ln-dr"></td>
                <td><input type="number" step="0.01" name="lines[0][AmountCr]" class="form-control ln-cr"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalcTotals();">Del</button></td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2" class="text-end">Totals:</th>
            <th id="totDr">0.00</th>
            <th id="totCr">0.00</th>
            <th></th>
        </tr>
    </tfoot>
</table>
</div>

<div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" id="addLine">Add Line</button>
    <button type="button" class="btn btn-outline-warning btn-sm" id="clearLines">Clear Lines</button>
</div>

<hr class="my-4">

<div class="d-flex flex-wrap align-items-center gap-3">
    <div>
        <span class="fw-semibold">Counter-GL check:</span>
        <span id="matchState" class="badge bg-secondary">Pending</span>
        <small id="matchHint" class="text-muted ms-2"></small>
    </div>
    <div class="ms-auto">
        <input type="hidden" name="SourceModule" value="{{ old('SourceModule', $entry->SourceModule ?? 'MANUAL') }}">
        <input type="hidden" name="IsSystemGenerated" value="{{ old('IsSystemGenerated', $entry->IsSystemGenerated ?? 0) }}">
        <button class="btn btn-success" id="submitBtn">{{ $isEdit ? 'Save' : 'Save Draft' }}</button>
        <a href="{{ $isEdit ? route('cashbook.show',$entry->CashbookID) : route('cashbook.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</div>

{{-- ====== JS helpers ====== --}}
<script>
(function () {
    const curSel = document.getElementById('CurrencyID');
    const rateEl = document.getElementById('ExchangeRate');
    const amtEl  = document.getElementById('Amount');
    const baseEl = document.getElementById('baseCalc');
    const partyType = document.getElementById('PartyType');
    const partyOnly = document.querySelectorAll('.party-only');
    const submitBtn = document.getElementById('submitBtn');
    const matchState = document.getElementById('matchState');
    const matchHint  = document.getElementById('matchHint');
    const txnSel     = document.getElementById('TransactionTypeID');
    const txnHint    = document.getElementById('txnTypeHint');
    const bankSel    = document.getElementById('BankAccountID');

    function setCurrencyHint() {
        const opt = curSel?.selectedOptions[0];
        document.getElementById('currencyHint').textContent =
            (opt && opt.value) ? `Code: ${opt.dataset.code} · Decimals: ${opt.dataset.digits}` : '';
    }
    function setBaseCalc() {
        const amt = parseFloat(amtEl.value || 0);
        const rt  = parseFloat(rateEl.value || 1);
        baseEl.textContent = (!isNaN(amt) && !isNaN(rt)) ? `Base: ${(amt * rt).toFixed(2)}` : '';
        recalcTotals();
    }
    function toggleParty() {
        const show = !!partyType.value;
        partyOnly.forEach(e => e.classList.toggle('d-none', !show));
    }

    const tbody = document.querySelector('#glTable tbody');
    document.getElementById('addLine')?.addEventListener('click', () => addLine());
    document.getElementById('clearLines')?.addEventListener('click', () => { tbody.innerHTML = ''; recalcTotals(); });

    function addLine(line = {GLAccountID:'', Description:'', AmountDr:'', AmountCr:''}) {
        const idx = tbody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input name="lines[${idx}][GLAccountID]" class="form-control" value="${line.GLAccountID||''}"></td>
            <td><input name="lines[${idx}][Description]" class="form-control" value="${line.Description||''}"></td>
            <td><input type="number" step="0.01" name="lines[${idx}][AmountDr]" class="form-control ln-dr" value="${line.AmountDr||''}"></td>
            <td><input type="number" step="0.01" name="lines[${idx}][AmountCr]" class="form-control ln-cr" value="${line.AmountCr||''}"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalcTotals();">Del</button></td>
        `;
        tbody.appendChild(tr);
        tr.querySelectorAll('.ln-dr, .ln-cr').forEach(inp => inp.addEventListener('input', recalcTotals));
        recalcTotals();
    }

    function sum(selector) {
        let s = 0; document.querySelectorAll(selector).forEach(i => { const v = parseFloat(i.value); if(!isNaN(v)) s += v; });
        return s;
    }

    window.recalcTotals = function recalcTotals() {
        const totDr = sum('.ln-dr');
        const totCr = sum('.ln-cr');
        document.getElementById('totDr').textContent = totDr.toFixed(2);
        document.getElementById('totCr').textContent = totCr.toFixed(2);

        const amt = parseFloat(amtEl.value || 0);
        const type = document.querySelector('input[name="EntryType"]:checked')?.value || '{{ $etype }}';

        // Header holds bank leg; counter lines should match Amount on the opposite side:
        let ok = false, need = amt;
        if (type === 'PAYMENT') {
            ok = Math.abs(totDr - need) < 0.005;
            matchHint.textContent = `Need DR = ${need.toFixed(2)}; You have DR = ${totDr.toFixed(2)}`;
        } else {
            ok = Math.abs(totCr - need) < 0.005;
            matchHint.textContent = `Need CR = ${need.toFixed(2)}; You have CR = ${totCr.toFixed(2)}`;
        }
        matchState.className = 'badge ' + (ok ? 'bg-success' : 'bg-warning text-dark');
        matchState.textContent = ok ? 'Balanced' : 'Not Balanced';
        submitBtn.disabled = !ok;
    }

    function setTxnHint(){
        const opt = txnSel?.selectedOptions[0];
        txnHint.textContent = opt?.dataset?.desc || '';
    }

    async function refreshMappingLines(){
        const auto = document.getElementById('UseAutoGL')?.checked;
        const txnId = txnSel?.value;
        if (!auto || !txnId) return;

        const entryType = document.querySelector('input[name="EntryType"]:checked')?.value || '';
        const bankOpt   = bankSel?.selectedOptions[0];
        const bankGL    = bankOpt?.dataset?.gl || '';

        const params = new URLSearchParams({
            amount: (document.getElementById('Amount').value || 0),
            entry_type: entryType,
            bank_gl: bankGL || ''
        });

        const url = `{{ route('cashbook.txntype.mapping', '__ID__') }}`.replace('__ID__', encodeURIComponent(txnId)) + `?${params.toString()}`;

        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) return;
        const data = await res.json();

        const tb = document.querySelector('#glTable tbody');
        tb.innerHTML = '';
        (data.lines || []).forEach(ln => addLine(ln));
        recalcTotals();
    }

    document.getElementById('UseAutoGL')?.addEventListener('change', refreshMappingLines);
    txnSel?.addEventListener('change', () => { setTxnHint(); refreshMappingLines(); });
    document.getElementById('etypeR')?.addEventListener('change', refreshMappingLines);
    document.getElementById('etypeP')?.addEventListener('change', refreshMappingLines);
    bankSel?.addEventListener('change', refreshMappingLines);
    amtEl?.addEventListener('input', refreshMappingLines);

    // initial
    setCurrencyHint(); setBaseCalc(); toggleParty(); setTxnHint(); recalcTotals();
    curSel?.addEventListener('change', setCurrencyHint);
    rateEl?.addEventListener('input', setBaseCalc);
    partyType?.addEventListener('change', toggleParty);
})();
</script>
