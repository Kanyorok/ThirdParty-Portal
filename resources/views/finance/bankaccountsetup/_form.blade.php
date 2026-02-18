@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Fix the following:</strong>
        <ul class="mb-0">@foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach</ul>
    </div>
@endif

@php
    /** @var \App\Models\Finance\BankAccount|null $account */
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Bank <span class="text-danger">*</span></label>
        <select name="BankID" id="BankID" class="form-select" required>
            <option value="">-- select bank --</option>
            @foreach($banks as $b)
                <option value="{{ $b->BankID }}" @selected(old('BankID', $account->BankID ?? '') == $b->BankID)>
                    {{ $b->BankName }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Branch</label>
        <select name="BranchID" id="BranchID" class="form-select">
            <option value="">-- select branch --</option>
            @foreach($branches as $br)
                <option value="{{ $br->BranchID }}"
                        data-bank="{{ $br->BankID }}"
                    @selected(old('BranchID', $account->BranchID ?? '') == $br->BranchID)>
                    {{ $br->BranchName }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Filtered by selected bank.</small>
    </div>

    <div class="col-md-6">
        <label class="form-label">Account Name</label>
        <input name="AccountName" class="form-control" value="{{ old('AccountName', $account->AccountName ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Account Number <span class="text-danger">*</span></label>
        <input name="AccountNumber" class="form-control"
               value="{{ old('AccountNumber', $account->AccountNumber ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">IBAN</label>
        <input name="IBAN" class="form-control" value="{{ old('IBAN', $account->IBAN ?? '') }}">
        <small class="text-muted">Optional for Kenya (use SWIFT/BIC + account number).</small>
    </div>
    <div class="col-md-6">
        <label class="form-label">Currency <span class="text-danger">*</span></label>
        <select name="CurrencyID" id="CurrencyID" class="form-select" required>
            <option value="">-- select currency --</option>
            @foreach($currencies as $cur)
                <option value="{{ $cur->Id }}"
                        data-code="{{ $cur->Code }}"
                        data-symbol="{{ $cur->Symbol }}"
                        data-digits="{{ $cur->DecimalDigits }}"
                    @selected(old('CurrencyID', $account->CurrencyID ?? '') == $cur->Id)>
                    {{ $cur->Code }} — {{ $cur->Name }} {{ $cur->Symbol ? '(' . $cur->Symbol . ')' : '' }}
                </option>
            @endforeach
        </select>
        <small id="currencyHint" class="text-muted d-block mt-1"></small>
    </div>
    <div class="col-md-6">
        <label class="form-label">GL Account</label>
        <select name="GLAccountID" id="GLAccountID" class="form-select">
            <option value="">-- select GL account --</option>
            @forelse(($glAccounts ?? []) as $gl)
                <option value="{{ $gl->Id }}" @selected(old('GLAccountID', $account->GLAccountID ?? '') == $gl->Id)>
                    {{ $gl->GLName ?? $gl->Description ?? ('GL #' . $gl->Id) }}
                </option>
            @empty
                <option value="" disabled>No record found</option>
            @endforelse
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Opening Balance</label>
        <input type="number" step="0.01" name="OpeningBalance" class="form-control"
               value="{{ old('OpeningBalance', $account->OpeningBalance ?? 0) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Current Balance</label>
        <input class="form-control" value="{{ old('CurrentBalance', $account->CurrentBalance ?? 0) }}" disabled>
        <small class="text-muted">Auto; starts at opening balance.</small>
    </div>
    {{-- <div class="col-md-4 d-flex align-items-center">
        <div class="form-check me-4">
            <input type="checkbox" name="IsDefault" id="IsDefault" class="form-check-input"
                   {{ old('IsDefault', $account->IsDefault ?? 0) ? 'checked' : '' }}>
            <label for="IsDefault" class="form-check-label">Default</label>
        </div>
        <div class="form-check">
            <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input"
                   {{ old('IsActive', $account->IsActive ?? 1) ? 'checked' : '' }}>
            <label for="IsActive" class="form-check-label">Active</label>
        </div>
    </div> --}}
</div>

{{-- Simple client-side branch filter --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bankSel = document.getElementById('BankID');
        const branchSel = document.getElementById('BranchID');

        function filterBranches() {
            const bankId = bankSel.value;
            [...branchSel.options].forEach(opt => {
                if (!opt.value) {
                    opt.hidden = false;
                    return;
                }
                opt.hidden = (opt.getAttribute('data-bank') !== bankId);
            });
            // If current selection doesn't match bank, clear it
            if (branchSel.selectedOptions[0] && branchSel.selectedOptions[0].hidden) {
                branchSel.value = '';
            }
        }

        bankSel.addEventListener('change', filterBranches);
        filterBranches();
    });

    document.addEventListener('DOMContentLoaded', function () {
        const sel = document.getElementById('CurrencyID');
        const hint = document.getElementById('currencyHint');

        function setHint() {
            const opt = sel.selectedOptions[0];
            if (!opt || !opt.value) {
                hint.textContent = '';
                return;
            }
            hint.textContent = `Code: ${opt.dataset.code} · Symbol: ${opt.dataset.symbol || '—'} · Decimals: ${opt.dataset.digits}`;
        }

        sel.addEventListener('change', setHint);
        setHint();
    });
</script>
