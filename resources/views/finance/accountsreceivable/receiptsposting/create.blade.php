@extends('layouts.app')
@section('title','New Receipt')

@section('content')
    <div class="container my-3">
        <div id="loadingOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.8); z-index: 2000;">
            <div class="text-center">
                <div class="spinner-border text-info" role="status" style="width: 4rem; height: 4rem;"></div>
                <div class="mt-2 text-muted">Searching…</div>
            </div>
        </div>
        <form action="{{ route('receiptsposting.store') }}" method="post" enctype="multipart/form-data" id="receiptForm">
            @csrf

            <!-- Alerts / Toasts -->
            <div id="flashArea"></div>
            <div id="toastArea" class="position-fixed top-0 end-0 p-3" style="z-index:1080;"></div>

            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-muted">
                        <i class="fas fa-receipt text-info me-2"></i> Create New Receipt
                    </h6>
                    <a href="{{ route('receiptsposting.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Receipts
                    </a>
                </div>

                <div class="card-body p-3">

                    <!-- STEP 1: Search customer -->
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Search Customer (Reg No./Tax PIN/Email/Phone/Name)</label>
                                <input type="text" class="form-control" id="searchIdNumber" placeholder="e.g., REG123456 / P123456789 / email@domain.com / +2547... / Acme" autocomplete="off">
                                <div class="invalid-feedback">Please enter an ID number to search.</div>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-info" id="btnSearchCustomer">
                                    <i class="fas fa-search me-1"></i> Find Customer
                                </button>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <span id="searchHint" class="small text-muted">Enter a registration number, Tax PIN, email, phone or name, then click Find.</span>
                                <span id="searchSpinner" class="small ms-2 d-none">
                <i class="fas fa-spinner fa-spin"></i> searching…
              </span>
                            </div>
                        </div>

                        <!-- Customer summary (hidden until found) -->
                        <div id="customerCard" class="row g-3 mt-3 d-none">
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="text-uppercase text-muted small">Customer</div>
                                                <div class="h6 mb-0" id="custName">—</div>
                                                <div class="small text-muted" id="custId">—</div>
                                            </div>
                                            <span class="badge bg-secondary" id="custStatus">—</span>
                                        </div>
                                        <div class="row small mt-2">
                                            <div class="col-md-4">Email: <span class="text-dark" id="custEmail">—</span></div>
                                            <div class="col-md-4">Phone: <span class="text-dark" id="custPhone">—</span></div>
                                            <div class="col-md-4">Outstanding: <strong id="custOutstanding">—</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="alert alert-info mb-0">
                                    <div class="fw-semibold">Next</div>
                                    <div class="small">Tick one or more invoices and set allocation per invoice. You can auto‑allocate too.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Pending Invoices with multi-allocate -->
                    <div id="invoicesBlock" class="border rounded-3 p-3 mb-3 d-none">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <h6 class="mb-0 text-muted">
                                <i class="fas fa-file-invoice text-info me-2"></i> Pending Invoices
                            </h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSelectAll">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnClearAll">Clear</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAutoAllocate">Auto‑allocate</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle" id="invoicesTable">
                                <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:40px">✔</th>
                                    <th>Invoice No.</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Currency</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                    <th class="text-end" style="width:160px">Allocate</th>
                                </tr>
                                </thead>
                                <tbody id="invoiceRows"></tbody>
                            </table>
                        </div>

                        <div class="small text-muted" id="invoiceCountHint">0 pending invoices</div>
                    </div>

                    <!-- STEP 3: Payment details + live totals -->
                    <div id="paymentBlock" class="row g-3 d-none">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-money-check-alt text-info me-2"></i> Payment Details
                                    </h6>

                                    <!-- Streamlined receipt form -->
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Amount Received</label>
                                            <input type="number" step="0.01" class="form-control" id="amountReceived" name="AmountReceived" placeholder="0.00">
                                            <div class="form-text">Digits only (no currency symbol).</div>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="paymentMethod" class="form-label">Payment Method</label>
                                            <select class="form-select" id="paymentMethod" name="PaymentMethod">
                                              <option value="">-- select --</option>
                                              @foreach(($paymentMethods ?? []) as $pm)
                                                <option value="{{ $pm->Value }}" {{ old('PaymentMethod')===$pm->Value ? 'selected' : '' }}>
                                                  {{ $pm->Description }}
                                                </option>
                                              @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Reference No.</label>
                                            <input type="text" class="form-control" id="referenceNo" name="ReferenceNo" placeholder="e.g., TRX-883728 / M-Pesa Code">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Value Date</label>
                                            <input type="date" class="form-control" id="valueDate" name="ValueDate" value="{{ now()->format('Y-m-d') }}">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Posting Date</label>
                                            <input type="date" class="form-control" id="postingDate" name="PostingDate" value="{{ now()->format('Y-m-d') }}">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Attachment (optional)</label>
                                            <input type="file" class="form-control" id="attachment" name="Attachment" accept=".pdf,.jpg,.jpeg,.png">
                                            <div class="form-text">Bank slip, cheque scan, or M‑Pesa screenshot.</div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Remarks</label>
                                            <textarea class="form-control" id="remarks" name="Remarks" rows="3" placeholder="Add any relevant details..."></textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Live Summary (digits only) -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-list-ol text-info me-2"></i> Summary
                                    </h6>

                                    <div class="border rounded-3 p-2 small">
                                        <div>Total Outstanding: <strong id="sumOutstanding">—</strong></div>
                                        <div>Amount Received: <strong id="sumReceived">—</strong></div>
                                        <div>Applied to Invoices: <strong id="sumApplied">—</strong></div>
                                        <div>Unapplied / Overpay: <strong id="sumUnapplied">—</strong> <span id="appliedBadge" class="badge ms-1"></span></div>
                                    </div>

                                    <input type="hidden" name="CustomerId" id="customerIdHidden">
                                    <button type="submit" class="btn btn-success w-100 mt-3" id="submitBtn" disabled>
                                        <i class="fas fa-save me-1"></i> Save Receipt
                                    </button>
                                    <div class="small text-muted mt-2">
                                        We’ll attach this receipt to the selected invoices using the entered allocations.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- /paymentBlock -->

                </div>
            </div>
        </form>
    </div>
@endsection

@section('styles')
    <style>
        .table-hover tbody tr:hover { background-color: #f8f9fa; transition: background-color .2s; }
        .card { border: none; border-radius: .5rem; }
        .form-text { font-size: .75rem; }
        .alloc-input { max-width: 140px; }
    </style>
@endsection

@section('scripts')
    <script>
        /* ============================================================
           CONFIG (only backend; no demo; no default currency)
           ============================================================ */
        const LOOKUP_URL = "{{ url('/finance/ar/receiptsposting/api/customers') }}"; // ?id_number=XXXX

        /* ====================== DOM ====================== */
        const btnSearch = document.getElementById('btnSearchCustomer');
        const idInput   = document.getElementById('searchIdNumber');
        const spinner   = document.getElementById('searchSpinner');
        const overlay   = document.getElementById('loadingOverlay');
        const hint      = document.getElementById('searchHint');

        const custCard  = document.getElementById('customerCard');
        const custName  = document.getElementById('custName');
        const custId    = document.getElementById('custId');
        const custEmail = document.getElementById('custEmail');
        const custPhone = document.getElementById('custPhone');
        const custStat  = document.getElementById('custStatus');
        const custOut   = document.getElementById('custOutstanding');

        const invoicesBlock = document.getElementById('invoicesBlock');
        const invoiceRows   = document.getElementById('invoiceRows');
        const invoiceCount  = document.getElementById('invoiceCountHint');

        const paymentBlock  = document.getElementById('paymentBlock');
        const amountInput   = document.getElementById('amountReceived');

        const sumOut   = document.getElementById('sumOutstanding');
        const sumRecv  = document.getElementById('sumReceived');
        const sumApp   = document.getElementById('sumApplied');
        const sumUnapp = document.getElementById('sumUnapplied');
        const appliedBadge = document.getElementById('appliedBadge');

        const selectAllBtn = document.getElementById('btnSelectAll');
        const clearAllBtn  = document.getElementById('btnClearAll');
        const autoAllocBtn = document.getElementById('btnAutoAllocate');

        const customerIdHidden = document.getElementById('customerIdHidden');
        const submitBtn        = document.getElementById('submitBtn');

        const flashArea        = document.getElementById('flashArea');
        const toastArea        = document.getElementById('toastArea');

        /* ====================== HELPERS ====================== */
        const fmt = n => Number(n || 0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
        function setHidden(el, hidden){ el.classList.toggle('d-none', hidden); }
        function balanceOf(inv){ return Math.max(0, (Number(inv.total) - Number(inv.paid || 0))); }
        function sum(arr){ return arr.reduce((a,b)=> a + Number(b||0), 0); }
        function flash(message, type='info'){
            const id = 'alert-' + Math.random().toString(36).slice(2);
            flashArea.innerHTML = `
    <div id="${id}" class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
            setTimeout(() => {
                const el = document.getElementById(id);
                if (el) bootstrap.Alert.getOrCreateInstance(el).close();
            }, 5000);
        }
        function toast(message, header='Notice'){
            const id = 'toast-' + Math.random().toString(36).slice(2);
            const tpl = document.createElement('div');
            tpl.innerHTML = `
    <div id="${id}" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <strong class="me-auto">${header}</strong>
        <small>now</small>
        <button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">${message}</div>
    </div>`;
            toastArea.appendChild(tpl.firstElementChild);
            const t = new bootstrap.Toast(document.getElementById(id), { delay: 3000 });
            t.show();
        }

        /* ====================== STATE ====================== */
        let state = {
            customer: null,
            invoices: [] // each: { id, number, issue_date, due_date, currency:{code,symbol?}, total, paid, balance, allocate, selected }
        };

        /* ====================== RESET / CLEAR ====================== */
        function clearCustomerUI(){
            custName.textContent  = '—';
            custId.textContent    = '—';
            custEmail.textContent = '—';
            custPhone.textContent = '—';
            custStat.textContent  = '—';
            custOut.textContent   = '—';
            customerIdHidden.value= '';
            setHidden(custCard, true);
        }
        function clearInvoicesUI(){
            invoiceRows.innerHTML = '';
            invoiceCount.textContent = '0 pending invoices';
            setHidden(invoicesBlock, true);
        }
        function clearPaymentUI(){
            amountInput.value = '';
            sumOut.textContent   = '—';
            sumRecv.textContent  = '—';
            sumApp.textContent   = '—';
            sumUnapp.textContent = '—';
            appliedBadge.className = 'badge ms-1';
            appliedBadge.textContent = '';
            submitBtn.disabled = true;
            setHidden(paymentBlock, true);
        }
        function resetAll(message){
            state.customer = null;
            state.invoices = [];
            clearCustomerUI();
            clearInvoicesUI();
            clearPaymentUI();
            if (message) hint.textContent = message;
        }

        /* ====================== SEARCH FLOW ====================== */
        async function doSearch(){
            const idNo = (idInput.value || '').trim();
            if (!idNo){
                idInput.classList.add('is-invalid');
                resetAll('Please enter a valid ID number.');
                idInput.focus();
                flash('Enter an ID number to search.', 'warning');
                return;
            } else {
                idInput.classList.remove('is-invalid');
            }

            btnSearch.disabled = true;
            setHidden(spinner, false);
            overlay.classList.remove('d-none');
            hint.textContent = 'Searching…';

            try {
                const res = await fetch(`${LOOKUP_URL}?id_number=${encodeURIComponent(idNo)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error(res.status === 404 ? 'NOT_FOUND' : 'LOOKUP_FAILED');
                const data = await res.json();

                // Seed state
                resetAll();
                state.customer = data.customer || null;
                state.invoices = (data.invoices || []).map(x => ({ ...x, balance: balanceOf(x), allocate: 0, selected: false }));

                renderCustomer();
                renderInvoices();
                setHidden(paymentBlock, state.invoices.filter(i => i.balance>0).length === 0);

                hint.textContent = 'Customer loaded. Select invoices and allocate amounts.';
                flash('Customer found and loaded successfully.','success');
                toast('Customer loaded. You can now allocate payments.','Success');
            } catch (err){
                if (err.message === 'NOT_FOUND'){
                    resetAll('Customer not found or no posted unpaid invoices.');
                    flash('Customer not found or no posted unpaid invoices.','danger');
                } else {
                    resetAll('Lookup failed. Please try again.');
                    flash('Lookup failed. Check your connection and try again.','danger');
                }
            } finally {
                setHidden(spinner, true);
                overlay.classList.add('d-none');
                btnSearch.disabled = false;
            }
        }

        btnSearch.addEventListener('click', doSearch);
        idInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter'){ e.preventDefault(); doSearch(); }
        });

        /* ====================== RENDERERS ====================== */
        function renderCustomer(){
            const c = state.customer;
            if (!c){ clearCustomerUI(); return; }

            custName.textContent  = c.name || '—';
            custId.textContent    = `ID: ${c.id_number || '—'}`;
            custEmail.textContent = c.email || '—';
            custPhone.textContent = c.phone || '—';
            custStat.textContent  = c.status || '—';
            customerIdHidden.value= c.id || '';

            // Outstanding (digits only)
            const outstanding = sum(state.invoices.map(i => i.balance));
            custOut.textContent = fmt(outstanding);

            setHidden(custCard, false);
        }

        function renderInvoices(){
            invoiceRows.innerHTML = '';
            const pending = state.invoices.filter(i => i.balance > 0);

            pending.forEach(inv => {
                const sym = inv.currency?.symbol || ''; // currency is allowed ONLY in invoice table
                const cur = inv.currency?.code || '';
                const symPrefix = sym ? `${sym} ` : ''; // when missing, amounts show digits only inside table? requirement says currency shown in table—so we show if provided.

                const tr = document.createElement('tr');
                tr.innerHTML = `
      <td class="text-center">
        <input class="form-check-input inv-select" type="checkbox" data-id="${inv.id}" ${inv.selected ? 'checked':''}>
      </td>
      <td class="fw-semibold">${inv.number}</td>
      <td>${inv.issue_date}</td>
      <td>${inv.due_date}</td>
      <td>${cur}</td>
      <td class="text-end">${symPrefix}${fmt(inv.total)}</td>
      <td class="text-end">${symPrefix}${fmt(inv.paid || 0)}</td>
      <td class="text-end">${symPrefix}${fmt(inv.balance)}</td>
      <td class="text-end">
        <div class="input-group input-group-sm justify-content-end">
          ${sym ? `<span class="input-group-text">${sym}</span>` : ''}
          <input type="number" step="0.01" class="form-control text-end alloc-input inv-alloc"
                 data-id="${inv.id}" value="${inv.allocate || 0}" ${inv.selected?'':'disabled'}>
        </div>
      </td>
    `;
                invoiceRows.appendChild(tr);
            });

            invoiceCount.textContent = `${pending.length} pending ${pending.length===1?'invoice':'invoices'}`;
            setHidden(invoicesBlock, pending.length === 0);
            setHidden(paymentBlock, pending.length === 0);

            // Bind events for the freshly rendered rows
            invoiceRows.querySelectorAll('.inv-select').forEach(cb => cb.addEventListener('change', onSelectInvoice));
            invoiceRows.querySelectorAll('.inv-alloc').forEach(inp => inp.addEventListener('input', onAllocationChanged));

            recalcSummary();
        }

        /* ====================== SELECTION / ALLOCATION ====================== */
        function onSelectInvoice(e){
            const id = Number(e.target.getAttribute('data-id'));
            const inv = state.invoices.find(i => i.id===id);
            if (!inv) return;

            inv.selected = e.target.checked;

            const allocInput = invoiceRows.querySelector(`.inv-alloc[data-id="${id}"]`);
            if (allocInput){
                allocInput.disabled = !inv.selected;
                if (!inv.selected){ inv.allocate = 0; allocInput.value = '0'; }
            }

            recalcSummary();
        }

        function onAllocationChanged(e){
            const id = Number(e.target.getAttribute('data-id'));
            const inv = state.invoices.find(i => i.id===id);
            if (!inv) return;

            let val = Number(e.target.value || 0);
            if (val < 0) val = 0;
            if (val > inv.balance) val = inv.balance;
            inv.allocate = val;
            e.target.value = inv.allocate.toFixed(2);

            if (val > 0){
                inv.selected = true;
                const cb = invoiceRows.querySelector(`.inv-select[data-id="${id}"]`);
                if (cb && !cb.checked) cb.checked = true;
                e.target.disabled = false;
            }

            recalcSummary();
        }

        document.getElementById('btnSelectAll').addEventListener('click', () => {
            state.invoices.forEach(i => { if (i.balance>0){ i.selected = true; } });
            renderInvoices();
        });
        document.getElementById('btnClearAll').addEventListener('click', () => {
            state.invoices.forEach(i => { i.selected = false; i.allocate = 0; });
            renderInvoices();
        });
        document.getElementById('btnAutoAllocate').addEventListener('click', () => {
            const received = Number(amountInput.value || 0);
            if (received <= 0){ amountInput.focus(); flash('Enter an Amount Received before auto‑allocating.','warning'); return; }

            state.invoices.forEach(i => { i.selected=false; i.allocate=0; });
            const ordered = [...state.invoices].sort((a,b) => new Date(a.issue_date) - new Date(b.issue_date));
            let remaining = received;
            for (const inv of ordered){
                if (inv.balance <= 0 || remaining <= 0) continue;
                const alloc = Math.min(inv.balance, remaining);
                inv.selected = alloc > 0;
                inv.allocate = alloc;
                remaining -= alloc;
            }
            renderInvoices();
            amountInput.dispatchEvent(new Event('input'));
        });

        /* ====================== PAYMENT + SUMMARY (digits only) ====================== */
        amountInput.addEventListener('input', recalcSummary);

        function recalcSummary(){
            const outstanding = sum(state.invoices.map(i => i.balance));
            const received    = Number(amountInput.value || 0);
            const applied     = sum(state.invoices.filter(i => i.selected).map(i => Math.min(i.allocate, i.balance)));
            const unapplied   = (received - applied);

            sumOut.textContent   = fmt(outstanding);
            sumRecv.textContent  = fmt(received);
            sumApp.textContent   = fmt(applied);
            sumUnapp.textContent = fmt(unapplied);

            appliedBadge.className = 'badge ms-1';
            if (unapplied > 0) {
                appliedBadge.classList.add('bg-warning','text-dark');
                appliedBadge.textContent = 'Unapplied';
            } else if (unapplied < 0) {
                appliedBadge.classList.add('bg-danger');
                appliedBadge.textContent = 'Over‑allocated';
            } else if (received > 0) {
                appliedBadge.classList.add('bg-success');
                appliedBadge.textContent = 'Balanced';
            } else {
                appliedBadge.textContent = '';
            }

            const hasCustomer = !!(state.customer && state.customer.id);
            const noOverAlloc = (unapplied >= 0);
            const hasAnySelection = state.invoices.some(i => i.selected && i.allocate>0);
            submitBtn.disabled = !(hasCustomer && received>0 && noOverAlloc && hasAnySelection);
        }

        /* ====================== SUBMIT: build payload ====================== */
        document.getElementById('receiptForm').addEventListener('submit', (e) => {
            const allocations = state.invoices
                .filter(i => i.selected && i.allocate>0)
                .map(i => ({ invoice_id: i.id, allocate: Number(i.allocate.toFixed(2)) }));

            let hidden = document.getElementById('allocationsJson');
            if (!hidden){
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'Allocations';
                hidden.id   = 'allocationsJson';
                e.target.appendChild(hidden);
            }
            hidden.value = JSON.stringify(allocations);

            const received = Number(amountInput.value || 0);
            const applied  = sum(allocations.map(a => a.allocate));
            if (received <= 0) { e.preventDefault(); flash('Enter a valid Amount Received.','danger'); return; }
            if (applied > received) { e.preventDefault(); flash('Allocated amount exceeds Amount Received.','danger'); return; }
            if (allocations.length === 0) {
                e.preventDefault(); flash('Allocate at least one invoice to proceed.','danger'); return;
            }
        });
    </script>
@endsection
