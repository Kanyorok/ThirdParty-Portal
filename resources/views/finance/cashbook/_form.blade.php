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
    $partyType = old('PartyType', $entry->PartyType ?? '');
    $partyId   = old('PartyID', $entry->PartyID ?? '');
    $partyName = old('PartyName', $entry->PartyName ?? '');
    $partyContact = old('PartyContact', $entry->PartyContact ?? '');
    $partyEmail = old('PartyEmail', $entry->PartyEmail ?? '');
    $isPayment = strtoupper($etype ?? '') === 'PAYMENT';
    $lockType = ($presetType ?? '') === 'PAYMENT' && !$isEdit;
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
    @if(!$lockType)
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
    @else
        <input type="hidden" name="EntryType" value="PAYMENT">
    @endif

    <div class="col-md-5">
        <label class="form-label">Bank Account <span class="text-danger">*</span></label>
        <select name="BankAccountID" id="BankAccountID" class="form-select select2-basic"
                data-placeholder="Select bank account" required>
            <option value="">Select bank account</option>
            @foreach($bankAccounts as $ba)
                <option value="{{ $ba->AccountID }}"
                        data-gl="{{ $ba->GLAccountID ?? '' }}"
                        data-gl-code="{{ $ba->glAccount?->GLCode ?? '' }}"
                        data-gl-name="{{ $ba->glAccount?->GLName ?? '' }}"
                        @selected($bankId==$ba->AccountID)>
                    {{ optional($ba->bank)->BankName }} — {{ $ba->AccountNumber }}
                    @if(!empty($ba->GLAccountID))
                        (GL {{ $ba->glAccount?->GLCode ?? $ba->GLAccountID }})
                    @endif
                </option>
            @endforeach
        </select>
        <small class="text-muted">Bank GL will be posted from account setup; counter-GLs are below.</small>
        <small id="bankGlHint" class="text-muted d-block"></small>
    </div>

    <div class="col-md-2">
        <label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" name="DocDate" class="form-control"
               value="{{ old('DocDate', isset($entry)?$entry->DocDate:now()->toDateString()) }}" required>
    </div>

    <div class="col-md-2">
        <label class="form-label">Currency <span class="text-danger">*</span></label>
        <select name="CurrencyID" id="CurrencyID" class="form-select select2-basic"
                data-placeholder="Select currency" required>
            <option value="">Select currency</option>
            @foreach($currencies as $cur)
                <option value="{{ $cur->Id }}" data-code="{{ $cur->Code }}" data-digits="{{ $cur->DecimalDigits }}"
                    @selected($curId==$cur->Id)>{{ $cur->Code }} — {{ $cur->Name }}</option>
            @endforeach
        </select>
        <small id="currencyHint" class="text-muted d-block"></small>
    </div>

    <div class="col-md-3">
        <label class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="Amount" id="Amount" class="form-control" value="{{ $amount }}" required>
        <small id="baseCalc" class="text-muted d-block"></small>
    </div>
</div>

{{-- Commented for Cashbook Payment UX: Doc No, Reference, Transaction Type, Exchange Rate, Auto GL mapping --}}
<input type="hidden" name="ExchangeRate" id="ExchangeRate" value="{{ $exRate }}">
<input type="hidden" name="TransactionTypeID" id="TransactionTypeID" value="{{ $txnId }}">
<input type="hidden" name="UseAutoGL" id="UseAutoGL" value="{{ $useAuto ? 1 : 0 }}">

<div class="row g-3 mt-1">
    <div class="col-md-3">
        <label class="form-label">Party Type</label>
        <select name="PartyType" id="PartyType" class="form-select select2-basic"
                data-placeholder="Select party type">
            <option value="" disabled @selected($partyType==='')>Select party type</option>
            <option value="VENDOR" @selected($partyType==='VENDOR')>Vendor</option>
            <option value="TENANT" @selected($partyType==='TENANT')>Tenant</option>
            <option value="OTHER" @selected($partyType==='OTHER')>Other</option>
        </select>
    </div>
    <div class="col-md-5 party-select-block d-none">
        <label class="form-label">Party</label>
        <select name="PartyID" id="PartyLookup" class="form-select select2-basic"
                data-vendor-url="{{ route('cashbook.party.vendors') }}"
                data-tenant-url="{{ route('cashbook.party.tenants') }}">
            <option value="">Search and select</option>
            @if(!empty($partyId) && in_array($partyType, ['VENDOR','TENANT'], true))
                <option value="{{ $partyId }}" selected>{{ $partyName ?: ('#'.$partyId) }}</option>
            @endif
        </select>
        <small id="partyLookupHint" class="text-muted d-block"></small>
    </div>
    <div class="col-md-4 party-other-block d-none">
        <label class="form-label">Other Party Name</label>
        <input name="PartyName" id="PartyName" class="form-control" value="{{ $partyName }}">
    </div>
    <div class="col-md-2 party-other-block d-none">
        <label class="form-label">Contact (Optional)</label>
        <input name="PartyContact" class="form-control" value="{{ $partyContact }}">
    </div>
    <div class="col-md-3 party-other-block d-none">
        <label class="form-label">Email (Optional)</label>
        <input type="email" name="PartyEmail" class="form-control" value="{{ $partyEmail }}">
    </div>
    <div class="col-md-12">
        <label class="form-label">Narration (Optional)</label>
        <textarea name="Narration" class="form-control" rows="2" placeholder="Narration">{{ old('Narration', $entry->Narration ?? '') }}</textarea>
    </div>
</div>

<hr class="my-4">

<h5 class="mt-0">Counter-GL Split</h5>
<div class="table-responsive">
    @if($isPayment)
        <table class="table table-bordered align-middle table-sm cb-table" id="glTable">
            <thead class="table-light">
            <tr>
                <th style="width:56px">#</th>
                <th class="col-gl">GL Account</th>
                <th class="col-amt">Amount (DR)</th>
                <th class="col-desc">Narration</th>
                <th style="width:90px">Action</th>
            </tr>
            </thead>
            <tbody id="glBody">
            @forelse($rows as $idx => $ln)
                <tr>
                    <td class="line-number">{{ $idx + 1 }}</td>
                    <td class="col-gl">
                        <select name="lines[{{ $idx }}][GLAccountID]" class="form-select gl-account-select" required>
                            <option value="">Select GL</option>
                            @foreach($gls as $gl)
                                <option value="{{ $gl->Id }}" data-code="{{ $gl->GLCode }}" data-name="{{ $gl->GLName }}"
                                    @selected(($ln['GLAccountID'] ?? '') == $gl->Id)>
                                    {{ $gl->GLCode }} ({{ $gl->GLName }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="col-amt">
                        <input type="number" step="0.01" name="lines[{{ $idx }}][AmountDr]"
                               class="form-control line-amount text-end" value="{{ $ln['AmountDr'] ?? '' }}">
                    </td>
                    <td class="col-desc">
                        <input name="lines[{{ $idx }}][Description]" class="form-control"
                               value="{{ $ln['Description'] ?? '' }}" placeholder="Narration">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="line-number">1</td>
                    <td class="col-gl">
                        <select name="lines[0][GLAccountID]" class="form-select gl-account-select" required>
                            <option value="">Select GL</option>
                            @foreach($gls as $gl)
                                <option value="{{ $gl->Id }}" data-code="{{ $gl->GLCode }}" data-name="{{ $gl->GLName }}">
                                    {{ $gl->GLCode }} ({{ $gl->GLName }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="col-amt">
                        <input type="number" step="0.01" name="lines[0][AmountDr]"
                               class="form-control line-amount text-end">
                    </td>
                    <td class="col-desc">
                        <input name="lines[0][Description]" class="form-control" placeholder="Narration">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    @else
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
                    <td><input name="lines[{{ $idx }}][GLAccountID]" class="form-control"
                               value="{{ $ln['GLAccountID'] ?? '' }}"></td>
                    <td><input name="lines[{{ $idx }}][Description]" class="form-control"
                               value="{{ $ln['Description'] ?? '' }}"></td>
                    <td><input type="number" step="0.01" name="lines[{{ $idx }}][AmountDr]" class="form-control ln-dr"
                               value="{{ $ln['AmountDr'] ?? '' }}"></td>
                    <td><input type="number" step="0.01" name="lines[{{ $idx }}][AmountCr]" class="form-control ln-cr"
                               value="{{ $ln['AmountCr'] ?? '' }}"></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="this.closest('tr').remove(); recalcTotals();">Del
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td><input name="lines[0][GLAccountID]" class="form-control"></td>
                    <td><input name="lines[0][Description]" class="form-control"></td>
                    <td><input type="number" step="0.01" name="lines[0][AmountDr]" class="form-control ln-dr"></td>
                    <td><input type="number" step="0.01" name="lines[0][AmountCr]" class="form-control ln-cr"></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="this.closest('tr').remove(); recalcTotals();">Del
                        </button>
                    </td>
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
    @endif
</div>

<div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" id="addLine">Add Line</button>
    <button type="button" class="btn btn-outline-warning btn-sm" id="clearLines">Clear Lines</button>
</div>

<hr class="my-4">

<div class="totals-box alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <strong>Total DR:</strong> <span id="sumDr">0.00</span> &nbsp;
        <strong>Total CR:</strong> <span id="sumCr">0.00</span>
        <small id="matchHint" class="text-muted ms-2"></small>
    </div>
    <span id="matchState" class="badge bg-warning text-dark fw-bold px-3 py-2">Pending</span>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <input type="hidden" name="SourceModule" value="{{ old('SourceModule', $entry->SourceModule ?? 'MANUAL') }}">
    <input type="hidden" name="IsSystemGenerated"
           value="{{ old('IsSystemGenerated', $entry->IsSystemGenerated ?? 0) }}">
    <button class="btn btn-success" id="submitBtn">{{ $isEdit ? 'Save' : 'Save Draft' }}</button>
    <a href="{{ $isEdit ? route('cashbook.show',$entry->CashbookID) : route('cashbook.index') }}"
       class="btn btn-secondary">Cancel</a>
</div>

{{-- ====== JS helpers ====== --}}
<script>
    (function () {
        const curSel = document.getElementById('CurrencyID');
        const rateEl = document.getElementById('ExchangeRate');
        const amtEl = document.getElementById('Amount');
        const baseEl = document.getElementById('baseCalc');
        const partyType = document.getElementById('PartyType');
        const partySelectBlock = document.querySelector('.party-select-block');
        const partyOtherBlocks = document.querySelectorAll('.party-other-block');
        const partyLookup = document.getElementById('PartyLookup');
        const partyNameInput = document.getElementById('PartyName');
        const otherContactInput = document.querySelector('input[name="PartyContact"]');
        const otherEmailInput = document.querySelector('input[name="PartyEmail"]');
        const submitBtn = document.getElementById('submitBtn');
        const matchState = document.getElementById('matchState');
        const matchHint = document.getElementById('matchHint');
        const txnSel = document.getElementById('TransactionTypeID');
        const bankSel = document.getElementById('BankAccountID');
        const bankHint = document.getElementById('bankGlHint');
        const isPayment = {{ $isPayment ? 'true' : 'false' }};
        const tbody = document.querySelector('#glTable tbody');
        const templateRow = tbody?.querySelector('tr')?.cloneNode(true) || null;

        function setCurrencyHint() {
            const opt = curSel?.selectedOptions[0];
            document.getElementById('currencyHint').textContent =
                (opt && opt.value) ? `Code: ${opt.dataset.code} · Decimals: ${opt.dataset.digits}` : '';
        }

        function setBaseCalc() {
            const amt = parseFloat(amtEl.value || 0);
            const rt = rateEl ? parseFloat(rateEl.value || 1) : 1;
            baseEl.textContent = (!isNaN(amt) && !isNaN(rt)) ? `Base: ${(amt * rt).toFixed(2)}` : '';
            recalcTotals();
        }

        function setBankHint() {
            if (!bankHint || !bankSel) return;
            const opt = bankSel.selectedOptions[0];
            if (!opt || !opt.value) {
                bankHint.textContent = '';
                return;
            }
            const code = opt.dataset.glCode || '';
            const name = opt.dataset.glName || '';
            bankHint.textContent = (code || name) ? `Bank GL: ${code} ${name ? '— ' + name : ''}` : '';
        }

        function getBankGl() {
            return bankSel?.selectedOptions[0]?.dataset?.gl || '';
        }

        function updateGlAccountOptions() {
            if (!bankSel) return;
            const bankGl = getBankGl();
            document.querySelectorAll('.gl-account-select').forEach(sel => {
                sel.querySelectorAll('option').forEach(opt => {
                    if (!opt.value) return;
                    opt.disabled = !!bankGl && String(opt.value) === String(bankGl);
                });
                if (bankGl && String(sel.value) === String(bankGl)) {
                    if (window.jQuery && $.fn.select2) {
                        $(sel).val(null).trigger('change');
                    } else {
                        sel.value = '';
                    }
                }
            });
        }

        function getEntryType() {
            return document.querySelector('input[name="EntryType"]:checked')?.value || '{{ $etype }}';
        }

        function resetPartySelect() {
            if (!partyLookup) return;
            partyLookup.value = '';
            if (window.jQuery && $.fn.select2) {
                $(partyLookup).val(null).trigger('change');
            }
        }

        function clearOtherPartyFields() {
            if (partyNameInput) partyNameInput.value = '';
            if (otherContactInput) otherContactInput.value = '';
            if (otherEmailInput) otherEmailInput.value = '';
        }

        function setPartyRequired(type) {
            if (partyLookup) partyLookup.required = (type === 'VENDOR' || type === 'TENANT');
            if (partyNameInput) partyNameInput.required = (type === 'OTHER');
        }

        function initPartySelect(type) {
            if (!partyLookup || !(window.jQuery && $.fn.select2)) return;
            const vendorUrl = partyLookup.dataset.vendorUrl;
            const tenantUrl = partyLookup.dataset.tenantUrl;
            const url = type === 'TENANT' ? tenantUrl : vendorUrl;
            const placeholder = type === 'TENANT' ? 'Search tenant' : 'Search vendor';
            $(partyLookup).select2('destroy').select2({
                placeholder: placeholder,
                width: '100%',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data.results || [] })
                }
            }).on('select2:select', function (e) {
                const selected = e.params?.data;
                if (partyNameInput) {
                    partyNameInput.value = selected?.text || '';
                }
            }).on('select2:clear', function () {
                if (partyNameInput) {
                    partyNameInput.value = '';
                }
            });
        }

        function toggleParty() {
            const type = partyType?.value || '';
            const showSelect = type === 'VENDOR' || type === 'TENANT';
            const showOther = type === 'OTHER';

            partySelectBlock?.classList.toggle('d-none', !showSelect);
            partyOtherBlocks.forEach(e => e.classList.toggle('d-none', !showOther));
            setPartyRequired(type);

            if (!showSelect) {
                resetPartySelect();
            }
            if (!showOther) {
                clearOtherPartyFields();
            }
            if (showSelect) {
                initPartySelect(type);
            }
        }

        function updateLineNumbers() {
            if (!tbody) return;
            tbody.querySelectorAll('tr').forEach((row, index) => {
                const cell = row.querySelector('.line-number');
                if (cell) cell.textContent = index + 1;
                row.querySelectorAll('[name^="lines["]').forEach(el => {
                    el.name = el.name.replace(/lines\[\d+]/, `lines[${index}]`);
                });
            });
        }

        function initGlSelect2() {
            if (!(window.jQuery && $.fn.select2)) return;
            const defaultMatcher = $.fn.select2.defaults.defaults.matcher;
            $('.gl-account-select').select2({
                placeholder: 'Select GL',
                width: '100%',
                dropdownAutoWidth: true,
                matcher: function (params, data) {
                    if (!data.id) return data;
                    const bankGl = getBankGl();
                    if (bankGl && String(data.id) === String(bankGl)) {
                        return null;
                    }
                    return defaultMatcher ? defaultMatcher(params, data) : data;
                },
                templateResult: function (data) {
                    if (!data.id) return data.text;
                    const $option = $(data.element);
                    return $(
                        '<div><strong>' + $option.data('code') + '</strong> ' +
                        '<small class="text-muted">(' + $option.data('name') + ')</small></div>'
                    );
                },
                templateSelection: function (data) {
                    if (!data.id) return data.text;
                    const $option = $(data.element);
                    return $(
                        '<div><strong>' + $option.data('code') + '</strong> ' +
                        '<small>(' + $option.data('name') + ')</small></div>'
                    );
                }
            });
            updateGlAccountOptions();
        }

        function addPaymentLine(line = {}) {
            if (!tbody || !templateRow) return;
            if (window.jQuery && $.fn.select2) {
                $('.gl-account-select').select2('destroy');
            }
            const clone = templateRow.cloneNode(true);
            clone.querySelectorAll('input, select').forEach(el => {
                if (el.tagName === 'INPUT') el.value = '';
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });
            tbody.appendChild(clone);
            updateLineNumbers();
            initGlSelect2();
            if (line.GLAccountID) {
                const sel = clone.querySelector('select[name*="[GLAccountID]"]');
                if (sel) sel.value = line.GLAccountID;
            }
            if (line.Description) {
                const desc = clone.querySelector('input[name*="[Description]"]');
                if (desc) desc.value = line.Description;
            }
            if (line.AmountDr) {
                const amt = clone.querySelector('input[name*="[AmountDr]"]');
                if (amt) amt.value = line.AmountDr;
            }
            updateGlAccountOptions();
            recalcTotals();
        }

        function sum(selector) {
            let s = 0;
            document.querySelectorAll(selector).forEach(i => {
                const v = parseFloat(i.value);
                if (!isNaN(v)) s += v;
            });
            return s;
        }

        window.recalcTotals = function recalcTotals() {
            if (isPayment) {
                const total = sum('.line-amount');
                const sumDrEl = document.getElementById('sumDr');
                const sumCrEl = document.getElementById('sumCr');
                if (sumDrEl) sumDrEl.textContent = total.toFixed(2);

                let valid = true;
                tbody?.querySelectorAll('tr').forEach(row => {
                    const gl = row.querySelector('select[name*="[GLAccountID]"]')?.value;
                    const amt = parseFloat(row.querySelector('input[name*="[AmountDr]"]')?.value || 0);
                    if (!gl || amt <= 0) valid = false;
                });

                const amt = parseFloat(amtEl.value || 0);
                if (sumCrEl) sumCrEl.textContent = (!isNaN(amt) ? amt.toFixed(2) : '0.00');
                const ok = valid && amt > 0 && Math.abs(total - amt) < 0.005;
                matchHint.textContent = `Need DR = ${amt.toFixed(2)}; You have DR = ${total.toFixed(2)}`;
                matchState.className = 'badge ' + (ok ? 'bg-success' : 'bg-warning text-dark');
                matchState.textContent = ok ? 'Balanced' : 'Not Balanced';
                if (submitBtn) submitBtn.disabled = !ok;
                return;
            }

            const totDr = sum('.ln-dr');
            const totCr = sum('.ln-cr');
            const totDrEl = document.getElementById('totDr');
            const totCrEl = document.getElementById('totCr');
            const sumDrEl = document.getElementById('sumDr');
            const sumCrEl = document.getElementById('sumCr');
            if (totDrEl) totDrEl.textContent = totDr.toFixed(2);
            if (totCrEl) totCrEl.textContent = totCr.toFixed(2);
            if (sumDrEl) sumDrEl.textContent = totDr.toFixed(2);
            if (sumCrEl) sumCrEl.textContent = totCr.toFixed(2);

            const amt = parseFloat(amtEl.value || 0);
            const type = getEntryType();
            let ok = false, need = amt;
            if (type === 'PAYMENT') {
                ok = Math.abs(totDr - need) < 0.005;
                matchHint.textContent = `Need DR = ${need.toFixed(2)}; You have DR = ${totDr.toFixed(2)}`;
            } else {
                ok = Math.abs(totCr - need) < 0.005;
                matchHint.textContent = `Need CR = ${need.toFixed(2)}; You have CR = ${totCr.toFixed(2)}`;
            }
            matchState.className = 'badge ' + (ok ? 'bg-success' : 'bg-danger');
            matchState.textContent = ok ? 'Balanced' : 'Unbalanced / Invalid';
            if (submitBtn) submitBtn.disabled = !ok;
        };

        async function refreshMappingLines() {
            const auto = document.getElementById('UseAutoGL')?.checked;
            const txnId = txnSel?.value;
            if (!auto || !txnId) return;

            const entryType = getEntryType();
            const bankOpt = bankSel?.selectedOptions[0];
            const bankGL = bankOpt?.dataset?.gl || '';

            const params = new URLSearchParams({
                amount: (document.getElementById('Amount').value || 0),
                entry_type: entryType,
                bank_gl: bankGL || ''
            });

            const url = `{{ route('cashbook.txntype.mapping', '__ID__') }}`.replace('__ID__', encodeURIComponent(txnId)) + `?${params.toString()}`;

            const res = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (!res.ok) return;
            const data = await res.json();

            const tb = document.querySelector('#glTable tbody');
            if (!tb) return;
            tb.innerHTML = '';
            if (isPayment) {
                (data.lines || []).forEach(ln => addPaymentLine(ln));
            } else {
                (data.lines || []).forEach(ln => addLine(ln));
            }
            recalcTotals();
        }

        function addLine(line = {GLAccountID: '', Description: '', AmountDr: '', AmountCr: ''}) {
            if (!tbody) return;
            const idx = tbody.querySelectorAll('tr').length;
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td><input name="lines[${idx}][GLAccountID]" class="form-control" value="${line.GLAccountID || ''}"></td>
            <td><input name="lines[${idx}][Description]" class="form-control" value="${line.Description || ''}"></td>
            <td><input type="number" step="0.01" name="lines[${idx}][AmountDr]" class="form-control ln-dr" value="${line.AmountDr || ''}"></td>
            <td><input type="number" step="0.01" name="lines[${idx}][AmountCr]" class="form-control ln-cr" value="${line.AmountCr || ''}"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalcTotals();">Del</button></td>
        `;
            tbody.appendChild(tr);
            tr.querySelectorAll('.ln-dr, .ln-cr').forEach(inp => inp.addEventListener('input', recalcTotals));
            recalcTotals();
        }

        document.getElementById('addLine')?.addEventListener('click', () => {
            if (isPayment) {
                addPaymentLine();
            } else {
                addLine();
            }
        });

        document.getElementById('clearLines')?.addEventListener('click', () => {
            if (!tbody) return;
            tbody.innerHTML = '';
            if (isPayment) {
                if (templateRow) {
                    const row = templateRow.cloneNode(true);
                    row.querySelectorAll('input, select').forEach(el => {
                        if (el.tagName === 'INPUT') el.value = '';
                        if (el.tagName === 'SELECT') el.selectedIndex = 0;
                    });
                    tbody.appendChild(row);
                    updateLineNumbers();
                    initGlSelect2();
                }
            }
            recalcTotals();
        });

        if (tbody && isPayment) {
            tbody.addEventListener('click', function (e) {
                if (e.target.closest && e.target.closest('.remove-line') && tbody.rows.length > 1) {
                    e.target.closest('tr').remove();
                    updateLineNumbers();
                    recalcTotals();
                }
            });
        }

        document.getElementById('UseAutoGL')?.addEventListener('change', refreshMappingLines);
        txnSel?.addEventListener('change', refreshMappingLines);
        document.getElementById('etypeR')?.addEventListener('change', refreshMappingLines);
        document.getElementById('etypeP')?.addEventListener('change', refreshMappingLines);
        bankSel?.addEventListener('change', () => {
            setBankHint();
            updateGlAccountOptions();
            refreshMappingLines();
        });
        amtEl?.addEventListener('input', refreshMappingLines);

        window.initCashbookSelect2 = function () {
            if (!(window.jQuery && $.fn.select2)) return;
            $('.select2-basic').each(function () {
                const placeholder = this.dataset.placeholder || 'Select';
                $(this).select2({placeholder: placeholder, width: '100%', dropdownAutoWidth: true, allowClear: true});
            });
            initGlSelect2();
            if (partyType) {
                $(partyType)
                    .off('select2:select.cashbookParty select2:clear.cashbookParty')
                    .on('select2:select.cashbookParty select2:clear.cashbookParty', toggleParty);
                toggleParty();
            }
        };

        // initial
        setCurrencyHint();
        setBaseCalc();
        setBankHint();
        updateGlAccountOptions();
        toggleParty();
        recalcTotals();
        curSel?.addEventListener('change', setCurrencyHint);
        rateEl?.addEventListener('input', setBaseCalc);
        partyType?.addEventListener('change', toggleParty);
        if (amtEl) amtEl.addEventListener('input', recalcTotals);
        document.addEventListener('input', recalcTotals);
        document.addEventListener('change', recalcTotals);
        if (window.jQuery && $.fn.select2) {
            window.initCashbookSelect2();
        }

        document.getElementById('cashbookForm')?.addEventListener('submit', function () {
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
            }
        });
    })();
</script>
