@extends('layouts.app')
@section('title','New Credit Profile')

@section('content')
    <div class="container my-3">
        <form action="{{ route('creditmanagement.store') }}" method="post" id="creditForm">
            @csrf

            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-muted">
                        <i class="fas fa-user-shield text-info me-2"></i> Create Credit Profile
                    </h6>
                    <a href="{{ route('creditmanagement.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>

                <div class="card-body p-3">
                    <!-- STEP 1: Customer lookup -->
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Customer ID Number</label>
                                <input type="text" class="form-control" id="idNumber" placeholder="e.g., 12345678"
                                       autocomplete="off">
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-info" id="btnFind">
                                    <i class="fas fa-search me-1"></i> Find Customer
                                </button>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <span id="hint" class="small text-muted">Enter ID and click Find.</span>
                                <span id="spin" class="small ms-2 d-none"><i class="fas fa-spinner fa-spin"></i> searching…</span>
                            </div>
                        </div>

                        <!-- Customer card -->
                        <div id="custCard" class="row g-3 mt-3 d-none">
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
                                            <div class="col-md-4">Email: <span class="text-dark" id="custEmail">—</span>
                                            </div>
                                            <div class="col-md-4">Phone: <span class="text-dark" id="custPhone">—</span>
                                            </div>
                                            <div class="col-md-4">Avg. Mo. Sales: <strong id="custAvgSales">KSh
                                                    0.00</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="alert alert-info mb-0 small">
                                    After loading the customer, set **Limit, Terms & Risk** below. Suggested limit uses
                                    avg. monthly sales × factor.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Credit terms -->
                    <div id="termsBlock" class="row g-3 d-none">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3"><i class="fas fa-sliders-h text-info me-2"></i> Credit
                                        Terms</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Credit Limit (KES)</label>
                                            <input type="number" step="0.01" class="form-control" id="creditLimit"
                                                   name="CreditLimit" placeholder="0.00">
                                            <div class="form-text">Editable suggested value.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Payment Terms</label>
                                            <select class="form-control" id="paymentTerms" name="PaymentTerms">
                                                <option value="Net 15">Net 15</option>
                                                <option value="Net 30" selected>Net 30</option>
                                                <option value="Net 45">Net 45</option>
                                                <option value="Net 60">Net 60</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Max Overdue (days)</label>
                                            <input type="number" class="form-control" id="maxOverdue" name="MaxOverdue"
                                                   value="30">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Risk Rating</label>
                                            <select class="form-control" id="riskRating" name="RiskRating">
                                                <option>Low</option>
                                                <option selected>Medium</option>
                                                <option>High</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Risk Score (0–100)</label>
                                            <input type="number" class="form-control" id="riskScore" name="RiskScore"
                                                   min="0" max="100" value="55">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Review Cycle (months)</label>
                                            <input type="number" class="form-control" id="reviewCycle"
                                                   name="ReviewCycle" value="6">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Effective From</label>
                                            <input type="date" class="form-control" id="effectiveFrom"
                                                   name="EffectiveFrom" value="{{ now()->format('Y-m-d') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Expiry / Next Review</label>
                                            <input type="date" class="form-control" id="expiryDate" name="ExpiryDate">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Collateral / Security</label>
                                            <input type="text" class="form-control" id="collateral" name="Collateral"
                                                   placeholder="e.g., Bank Guarantee, Title Deed, Debenture">
                                        </div>

                                        <div class="col-12 d-flex align-items-center gap-2">
                                            <input class="form-check-input" type="checkbox" id="allowOverLimit"
                                                   name="AllowOverLimit">
                                            <label for="allowOverLimit" class="form-check-label small">
                                                Allow over‑limit **with approval**
                                            </label>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Remarks</label>
                                            <textarea class="form-control" id="remarks" name="Remarks" rows="3"
                                                      placeholder="Add any relevant notes..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Live summary -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3"><i class="fas fa-chart-pie text-info me-2"></i> Summary
                                    </h6>
                                    <div class="border rounded-3 p-2 small">
                                        <div>Customer: <strong id="sumName">—</strong></div>
                                        <div>Suggested Limit: <strong id="sumSuggested">KSh 0.00</strong></div>
                                        <div>Chosen Limit: <strong id="sumLimit">KSh 0.00</strong></div>
                                        <div>Terms: <strong id="sumTerms">—</strong></div>
                                        <div>Risk: <strong id="sumRisk">—</strong></div>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100 mt-3" id="submitBtn" disabled>
                                        <i class="fas fa-save me-1"></i> Save Profile
                                    </button>
                                    <div class="small text-muted mt-2">
                                        The credit profile becomes Active from the Effective Date.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /termsBlock -->
                </div>
            </div>
        </form>
    </div>
@endsection

@section('styles')
    <style>
        :root {
            --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
        }

        body, .card, .table {
            font-family: var(--font-sans);
        }

        .card {
            border: none;
            border-radius: .5rem;
        }

        .form-text {
            font-size: .75rem;
        }
    </style>
@endsection

@section('scripts')
    <script>
        // Demo only — swap with your API later
        const USE_DEMO = true;
        const LOOKUP_URL = "{{ url('/finance/ar/creditmanagement/api/customer') }}"; // ?id=XXXX

        const btnFind = document.getElementById('btnFind');
        const idNumber = document.getElementById('idNumber');
        const hint = document.getElementById('hint');
        const spin = document.getElementById('spin');

        const custCard = document.getElementById('custCard');
        const termsBlock = document.getElementById('termsBlock');
        const submitBtn = document.getElementById('submitBtn');

        const custName = document.getElementById('custName');
        const custId = document.getElementById('custId');
        const custEmail = document.getElementById('custEmail');
        const custPhone = document.getElementById('custPhone');
        const custStatus = document.getElementById('custStatus');
        const custAvgSales = document.getElementById('custAvgSales');

        const creditLimit = document.getElementById('creditLimit');
        const paymentTerms = document.getElementById('paymentTerms');
        const riskRating = document.getElementById('riskRating');
        const riskScore = document.getElementById('riskScore');

        const sumName = document.getElementById('sumName');
        const sumSuggested = document.getElementById('sumSuggested');
        const sumLimit = document.getElementById('sumLimit');
        const sumTerms = document.getElementById('sumTerms');
        const sumRisk = document.getElementById('sumRisk');

        function kes(n) {
            return Number(n || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function toggle(el, on) {
            el.classList.toggle('d-none', !on);
        }

        function demoPayload(id) {
            return {
                customer: {
                    id: 901, id_number: id || '12345678',
                    name: 'ABC Properties Ltd',
                    email: 'abc@props.co.ke',
                    phone: '+254 722 555 000',
                    status: 'Active',
                    avg_monthly_sales: 1250000.00
                }
            }
        }

        btnFind.addEventListener('click', async () => {
            if (!idNumber.value.trim()) {
                hint.textContent = 'Enter a valid ID number.';
                idNumber.focus();
                return;
            }
            spin.classList.remove('d-none');
            hint.textContent = 'Searching…';
            let data;
            try {
                if (USE_DEMO) {
                    await new Promise(r => setTimeout(r, 500));
                    data = demoPayload(idNumber.value.trim());
                } else {
                    const res = await fetch(`${LOOKUP_URL}?id=${encodeURIComponent(idNumber.value.trim())}`, {headers: {'Accept': 'application/json'}});
                    if (!res.ok) throw new Error('lookup failed');
                    data = await res.json();
                }
            } catch (e) {
                hint.textContent = 'Customer not found.';
                spin.classList.add('d-none');
                return;
            }
            spin.classList.add('d-none');
            hint.textContent = 'Customer loaded.';

            // Fill card
            const c = data.customer;
            custName.textContent = c.name || '—';
            custId.textContent = `ID: ${c.id_number || '—'}`;
            custEmail.textContent = c.email || '—';
            custPhone.textContent = c.phone || '—';
            custStatus.textContent = c.status || '—';
            custAvgSales.textContent = `KSh ${kes(c.avg_monthly_sales || 0)}`;
            sumName.textContent = c.name || '—';
            toggle(custCard, true);
            toggle(termsBlock, true);

            // Suggest limit: avg sales × 4 (demo logic)
            const suggested = Number(c.avg_monthly_sales || 0) * 4;
            creditLimit.value = suggested.toFixed(2);
            sumSuggested.textContent = `KSh ${kes(suggested)}`;
            sumLimit.textContent = `KSh ${kes(creditLimit.value)}`;
            sumTerms.textContent = paymentTerms.value;
            sumRisk.textContent = `${riskRating.value} (score ${riskScore.value})`;
            submitBtn.disabled = false;
        });

        creditLimit.addEventListener('input', () => sumLimit.textContent = `KSh ${kes(creditLimit.value)}`);
        paymentTerms.addEventListener('change', () => sumTerms.textContent = paymentTerms.value);
        [riskRating, riskScore].forEach(el => el.addEventListener('input', () => {
            sumRisk.textContent = `${riskRating.value} (score ${riskScore.value})`;
        }));
    </script>
@endsection
