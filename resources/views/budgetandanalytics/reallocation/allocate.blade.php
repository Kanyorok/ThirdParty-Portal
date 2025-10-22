@extends('layouts.app')

@section('content')
    <div class="container my-3">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <h4>{{ $msg }}</h4>

        <div class="card shadow-sm rounded-3">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('budgetandanalytics.reallocation.store') }}">
                    @csrf
                    @method('POST')

                    {{-- Hidden context (ensure these are filled) --}}
                    <input type="hidden" id="BudgetID" name="BudgetID"
                           value="{{ $BudgetID ?? ($budget['Id'] ?? ($budgetId ?? request('BudgetID'))) }}">
                    <input type="hidden" id="BranchID" name="BranchID"
                           value="{{ $BranchID ?? ($branch['Id'] ?? ($branchId ?? request('BranchID'))) }}">
                    <input type="hidden" id="DepartmentID" name="DepartmentID" value="{{ $DepartmentID ?? '' }}">
                    <input type="hidden" id="ReallocationType" name="ReallocationType" value="{{ $ReallocationType }}">

                    {{-- Amount to Reallocate (col-12) --}}
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="reallocationAmount" class="form-label">Amount to Reallocate</label>
                            <input type="number" step="0.01" id="reallocationAmount" name="Amount" class="form-control"
                                   placeholder="Enter amount">
                            <div id="amountHelp" class="form-text"></div>
                        </div>
                    </div>

                    {{-- Selections + balances + monthly tables --}}
                    <div class="row g-3 mb-3">
                        {{-- FROM side --}}
                        <div class="col-md-6">
                            <label class="form-label">From Budget Line</label>
                            <select name="FromBudgetLineID" id="fromLine" class="form-select" required>
                                <option value="">-- Select Line --</option>
                                @if($isAccrossDepertments)
                                    @forelse($fromLines as $line)
                                        <option value="{{ $line['Id'] }}">{{ $line['LineName'] }}</option>
                                    @empty
                                        {{--                                        <option disabled>No record</option>--}}
                                    @endforelse
                                @else
                                    @forelse($budgetLines as $line)
                                        <option value="{{ $line['Id'] }}">{{ $line['LineName'] }}</option>
                                    @empty
                                        {{--                                        <option disabled>No record</option>--}}
                                    @endforelse
                                @endif

                            </select>

                            <div id="fromSummary" class="small text-muted mt-2" style="display:none;"></div>

                            <div id="fromAllocations" class="mt-3"></div>
                            <div id="fromTotals" class="mt-2 small"></div>
                        </div>

                        {{-- TO side --}}
                        <div class="col-md-6">
                            <label class="form-label">To Budget Line</label>
                            <select name="ToBudgetLineID" id="toLine" class="form-select" required>
                                <option value="">-- Select Line --</option>
                                @if($isAccrossDepertments)
                                    @forelse($toLines as $line)
                                        <option value="{{ $line['Id'] }}">{{ $line['LineName'] }}</option>
                                    @empty
                                        {{--                                        <option disabled readonly>No record</option>--}}
                                    @endforelse
                                @else
                                    @forelse($budgetLines as $line)
                                        <option value="{{ $line['Id'] }}">{{ $line['LineName'] }}</option>
                                    @empty
                                        {{--                                        <option disabled readonly>No record</option>--}}
                                    @endforelse
                                @endif

                            </select>

                            <div id="toSummary" class="small text-muted mt-2" style="display:none;"></div>

                            <div id="toAllocations" class="mt-3"></div>
                            <div id="toTotals" class="mt-2 small"></div>
                        </div>
                    </div>

                    {{-- Justification (hidden until valid) --}}
                    <div class="mb-4" id="justificationSection" style="display:none;">
                        <label class="form-label">Justification</label>
                        <textarea name="Justification" class="form-control" rows="3"></textarea>
                    </div>

                    {{-- Submit (hidden until valid) --}}
                    <div class="text-end" id="submitButton" style="display:none;">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; this.innerHTML='<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Submitting...'; this.form.submit();">
                            <i class="fas fa-save me-1"></i> Submit Reallocation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // --- keep your existing top definitions/imports/CSRF etc. ---

        const ALLOCATIONS_URL = @json(route('budgetandanalytics.reallocation.allocations'));
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const TZ_NOW = new Date();

        const budgetId = document.getElementById('BudgetID')?.value;
        const branchId = document.getElementById('BranchID')?.value;

        const fromLine = document.getElementById('fromLine');
        const toLine = document.getElementById('toLine');

        const amountInput = document.getElementById('reallocationAmount');
        const amountHelp = document.getElementById('amountHelp');

        const fromSummary = document.getElementById('fromSummary');
        const toSummary = document.getElementById('toSummary');
        const fromAllocations = document.getElementById('fromAllocations');
        const toAllocations = document.getElementById('toAllocations');
        const fromTotals = document.getElementById('fromTotals');
        const toTotals = document.getElementById('toTotals');

        const justificationSection = document.getElementById('justificationSection');
        const submitButton = document.getElementById('submitButton');

        let fromMeta = {months: [], allocated: 0, usage: 0, balance: 0};
        let toMeta = {months: [], allocated: 0, usage: 0, balance: 0};

        // ---- helpers ----
        const fmt = n => Number(n ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const parseMoney = v => (isFinite(parseFloat(v)) ? parseFloat(v) : 0);

        function monthLabelToDate(label) {
            const map = {
                Jan: 0,
                Feb: 1,
                Mar: 2,
                Apr: 3,
                May: 4,
                Jun: 5,
                Jul: 6,
                Aug: 7,
                Sep: 8,
                Oct: 9,
                Nov: 10,
                Dec: 11
            };
            const [mon, year] = label.split(' ');
            const y = parseInt(year, 10), m = map[mon];
            return (isNaN(y) || m === undefined) ? null : new Date(y, m, 1);
        }

        function isPastMonth(label) {
            const d = monthLabelToDate(label);
            const startThisMonth = new Date(TZ_NOW.getFullYear(), TZ_NOW.getMonth(), 1);
            return d && d < startThisMonth;
        }

        function buildMonthlyTable(months, prefix) {
            const labels = Array.isArray(months) && months.length ? months.slice(0, 12) : Array.from({length: 12}, (_, i) => `Month ${i + 1}`);
            const rows = labels.map((label, idx) => `
      <tr>
        <td class="align-middle">${label}</td>
        <td style="max-width:180px;">
          <input type="number" step="0.01" class="form-control alloc-input"
                 name="${prefix}[${idx + 1}]" data-month="${label}" ${isPastMonth(label) ? 'readonly' : ''} value="0">
        </td>
      </tr>`).join('');
            return `
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead><tr><th style="width:55%;">Month</th><th>Allocation</th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>`;
        }

        function renderSummary(el, meta, side) {
            const {allocated, usage, balance} = meta;
            el.innerHTML = `
      <span class="badge bg-light text-dark border me-2">Allocated: <strong>${fmt(allocated)}</strong></span>
      <span class="badge bg-light text-dark border me-2">Usage: <strong>${fmt(usage)}</strong></span>
      <span class="badge ${balance >= 0 ? 'bg-success' : 'bg-danger'}">${side} Balance: <strong>${fmt(balance)}</strong></span>
    `;
            el.style.display = 'block';
        }

        function collectTotal(container) {
            let sum = 0;
            container.querySelectorAll('input.alloc-input').forEach(i => sum += parseMoney(i.value));
            return sum;
        }

        // NEW: targets are AFTER reallocation (From: balance-amount, To: balance+amount)
        function targetAfter(sideMeta, sideLabel) {
            const amt = parseMoney(amountInput.value);
            return sideLabel === 'From' ? Math.max(0, sideMeta.balance - amt) : (sideMeta.balance + amt);
        }

        function showTotals(container, totalsEl, label, meta) {
            const total = collectTotal(container);
            const amt = parseMoney(amountInput.value);
            const target = targetAfter(meta, label);
            const ok = amt > 0 && Math.abs(total - target) < 0.005;

            totalsEl.innerHTML = `
      <div class="${ok ? 'text-success' : 'text-danger'}">
        ${label} total: <strong>${fmt(total)}</strong>
        / target after reallocation <strong>${fmt(target)}</strong>
        (realloc <strong>${fmt(amt)}</strong>)
        ${ok ? '✓' : '— adjust to match'}
      </div>
    `;
        }

        function syncDisabledOptions() {
            [...fromLine.options].forEach(o => o.disabled = false);
            [...toLine.options].forEach(o => o.disabled = false);
            if (fromLine.value) ([...toLine.options].find(o => o.value === fromLine.value) || {}).disabled = true;
            if (toLine.value) ([...fromLine.options].find(o => o.value === toLine.value) || {}).disabled = true;
        }

        function validateAndToggleSubmit() {
            const bothSelected = !!(fromLine.value && toLine.value);
            const amount = parseMoney(amountInput.value);

            // Must have enough on FROM before reallocation
            const fromEnough = amount > 0 && fromMeta.balance >= amount;

            // Monthly totals must equal the AFTER targets
            const fromTotal = collectTotal(fromAllocations);
            const toTotal = collectTotal(toAllocations);
            const fromTarget = targetAfter(fromMeta, 'From');
            const toTarget = targetAfter(toMeta, 'To');

            const fromOk = amount > 0 && Math.abs(fromTotal - fromTarget) < 0.005;
            const toOk = amount > 0 && Math.abs(toTotal - toTarget) < 0.005;

            // Feedback
            let msg = '';
            if (amount <= 0) {
                msg = 'Enter a positive amount to reallocate.';
            } else if (!fromEnough) {
                msg = `Insufficient balance on From line. Needed ${fmt(amount)}, available ${fmt(fromMeta.balance)}.`;
            } else if (!fromOk || !toOk) {
                msg = `Distribute monthly amounts to match post-reallocation targets — From: ${fmt(fromTarget)}, To: ${fmt(toTarget)}.`;
            }
            amountHelp.textContent = msg;
            amountHelp.className = 'form-text ' + (msg ? 'text-danger' : 'text-muted');

            const allow = bothSelected && amount > 0 && fromEnough && fromOk && toOk;
            justificationSection.style.display = allow ? 'block' : 'none';
            submitButton.style.display = allow ? 'block' : 'none';
        }

        function attachInputListeners(container, totalsEl, label, meta) {
            container.addEventListener('input', e => {
                if (!e.target.matches('input.alloc-input')) return;
                showTotals(container, totalsEl, label, meta);
                validateAndToggleSubmit();
            });
        }

        async function fetchAllocations(side, lineId) {
            const summaryEl = side === 'FROM' ? fromSummary : toSummary;
            const targetEl = side === 'FROM' ? fromAllocations : toAllocations;
            const totalsEl = side === 'FROM' ? fromTotals : toTotals;
            const metaHolder = side === 'FROM' ? fromMeta : toMeta;

            targetEl.innerHTML = `
      <div class="d-flex align-items-center gap-2 text-muted">
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        <span>Loading allocations…</span>
      </div>`;
            summaryEl.style.display = 'none';
            totalsEl.textContent = '';

            try {
                const res = await fetch(ALLOCATIONS_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        BudgetID: budgetId,
                        BranchID: branchId,
                        BudgetLineID: lineId
                    })
                });
                const data = await res.json();
                console.log(data)
                const payload = Array.isArray(data) ? (data[0] || {}) : (data || {});
                const months = payload.months || [];
                const allocated = parseMoney(payload.totAmountAllocated);
                const usage = parseMoney(payload.totUsage);
                const balance = allocated - usage;

                metaHolder.months = months;
                metaHolder.allocated = allocated;
                metaHolder.usage = usage;
                metaHolder.balance = balance;

                renderSummary(summaryEl, metaHolder, side === 'FROM' ? 'From' : 'To');
                targetEl.innerHTML = buildMonthlyTable(months, side === 'FROM' ? 'FromAllocations' : 'ToAllocations');

                attachInputListeners(targetEl, totalsEl, side === 'FROM' ? 'From' : 'To', metaHolder);
                showTotals(targetEl, totalsEl, side === 'FROM' ? 'From' : 'To', metaHolder);
            } catch {
                targetEl.innerHTML = `<div class="alert alert-danger py-2 mb-0">Error loading allocations. Please try again.</div>`;
            } finally {
                validateAndToggleSubmit();
            }
        }

        // Events
        fromLine.addEventListener('change', () => {
            syncDisabledOptions();
            if (fromLine.value) {
                fetchAllocations('FROM', fromLine.value);
            } else {
                fromAllocations.innerHTML = '';
                fromSummary.style.display = 'none';
                fromTotals.textContent = '';
            }
            validateAndToggleSubmit();
        });

        toLine.addEventListener('change', () => {
            syncDisabledOptions();
            if (toLine.value) {
                fetchAllocations('TO', toLine.value);
            } else {
                toAllocations.innerHTML = '';
                toSummary.style.display = 'none';
                toTotals.textContent = '';
            }
            validateAndToggleSubmit();
        });

        amountInput.addEventListener('input', () => {
            // Recompute targets and feedback live
            showTotals(fromAllocations, fromTotals, 'From', fromMeta);
            showTotals(toAllocations, toTotals, 'To', toMeta);
            validateAndToggleSubmit();
        });

        // Optional department-driven population left as-is if you use it elsewhere
        document.getElementById('departmentSelect')?.addEventListener('change', function () {
            let deptId = this.value;
            if (!deptId) return;
            fetch(`/budgetandanalytics/reallocation/budget-lines/${deptId}`)
                .then(res => res.json())
                .then(data => {
                    const fromSelect = document.getElementById('fromLine');
                    const toSelect = document.getElementById('toLine');
                    fromSelect.innerHTML = '<option value="">-- Select Line --</option>';
                    toSelect.innerHTML = '<option value="">-- Select Line --</option>';
                    data.forEach(line => {
                        fromSelect.add(new Option(line.LineName, line.Id));
                        toSelect.add(new Option(line.LineName, line.Id));
                    });
                    syncDisabledOptions();
                })
                .catch(() => {
                });
        });

        // Init
        syncDisabledOptions();
        validateAndToggleSubmit();
    </script>

@endsection
