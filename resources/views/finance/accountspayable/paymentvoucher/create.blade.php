@extends('layouts.app')
@section('title', 'Create Payment Voucher')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info"><i class="fas fa-file-invoice-dollar me-1"></i> Create Payment Voucher</h5>
                <a href="{{ route('paymentvoucher.index') }}" class="btn btn-outline-secondary btn-sm p-2">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body p-3">
                @if ($errors->any() || session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Please correct the following errors:</strong>
                        </div>
                        @if(session('error'))
                            <div class="mb-2">{{ session('error') }}</div>
                        @endif
                        @if ($errors->any())
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(!empty($contractExceptions))
                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning-subtle">
                            <strong>Contract Invoice Alerts (Penalty Decision Optional)</strong>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Reason</th>
                                        <th class="text-end">Penalty Suggestion</th>
                                        <th class="text-end">Balance</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($contractExceptions as $ex)
                                        <tr>
                                            <td>{{ $ex['InvoiceNumber'] }}</td>
                                            <td>{{ $ex['HoldReason'] }}</td>
                                            <td class="text-end">{{ $ex['CurrencyCode'] }} {{ number_format($ex['PenaltySuggestedAmount'] ?? 0, 2) }}</td>
                                            <td class="text-end">{{ $ex['CurrencyCode'] }} {{ number_format($ex['Balance'] ?? 0, 2) }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <form action="{{ route('paymentvoucher.contracts.apply-penalty', $ex['Id']) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="penalty_amount" value="{{ $ex['PenaltySuggestedAmount'] ?? 0 }}">
                                                        <button class="btn btn-sm btn-outline-danger">Apply Penalty + Release</button>
                                                    </form>
                                                    <form action="{{ route('paymentvoucher.contracts.waive-hold', $ex['Id']) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="reason" value="Milestone hold waived at voucher stage">
                                                        <button class="btn btn-sm btn-outline-secondary">Waive Hold</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <form id="paymentVoucherForm" action="{{ route('paymentvoucher.store') }}" method="POST">
                    @csrf
                    <input type="hidden" id="VoucherNo" name="VoucherNo" value="{{ $VoucherNo }}">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="InvoiceNo">Invoice Ref <span
                                    class="text-danger">*</span></label>
                            <select name="InvoiceNo" id="InvoiceNo"
                                    class="form-select @error('InvoiceNo') is-invalid @enderror" required>
                                <option value="">-- Select Invoice --</option>
                                @foreach($invoices as $invoice)
                                <option value="{{ $invoice['Id'] }}"
                                            data-amount="{{ (float) $invoice['Balance'] }}"
                                            data-currency="{{ $invoice['CurrencyCode'] }}"
                                        @selected(old('InvoiceNo') == $invoice['Id'])>
                                        [{{ strtoupper($invoice['InvoiceSourceType'] ?? 'PO') }}] {{ $invoice['InvoiceNumber'] }}
                                        @if(($invoice['IsOnHold'] ?? false) && strtoupper(($invoice['InvoiceSourceType'] ?? 'PO')) === 'CONTRACT')
                                            [MILESTONE PENDING]
                                        @endif
                                        - {{ $invoice['CurrencyCode'] }} {{ number_format($invoice['InvoiceAmount'], 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('InvoiceNo')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="PaymentMethod">Payment Method <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('PaymentMethod') is-invalid @enderror"
                                    name="PaymentMethod" id="PaymentMethod" required>
                                <option value="" disabled selected>-- Select Payment Method --</option>
                                @forelse($paymentMethods as $paymentMethod)
                                    <option
                                        value="{{ $paymentMethod->Description }}" @selected(old('PaymentMethod') == $paymentMethod->Description)>
                                        {{ $paymentMethod->Description }}
                                    </option>
                                @empty
                                    <option disabled>No Payment Method Found</option>
                                @endforelse
                            </select>
                            @error('PaymentMethod')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="paymentType">Payment Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('PaymentType') is-invalid @enderror" name="PaymentType"
                                    id="paymentType" required>
                                <option value="" disabled selected>-- Select Payment Type --</option>
                                @forelse($paymentTypes as $paymentType)
                                    <option
                                        value="{{ $paymentType->Description }}" @selected(old('PaymentType') == $paymentType->Description)>
                                        {{ $paymentType->Description }}
                                    </option>
                                @empty
                                    <option disabled>No Payment Type Found</option>
                                @endforelse
                            </select>
                            @error('PaymentType')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="invoicePreviewCard" class="card border-info-subtle bg-light d-none mb-3">
                        <div class="card-header bg-info-subtle py-2 px-3">
                            <strong>Selected Invoice Preview</strong>
                        </div>
                        <div class="card-body p-3" id="invoicePreviewBody">
                            <div class="text-muted small">Select an invoice to view details.</div>
                        </div>
                    </div>

                    <div id="amountRow" class="row g-3 mb-3" style="display:none;">
                        <div class="col-12">
                            <label class="form-label" for="PayAmount">Amount <span class="text-danger">*</span></label>
                            <input type="number"
                                   id="PayAmount"
                                   class="form-control @error('TotAmnt') is-invalid @enderror"
                                   name="TotAmnt"
                                   step="0.01"
                                   min="0"
                                   value="{{ old('TotAmnt') }}"
                                   placeholder="Enter amount">
                            @error('TotAmnt')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="scheduleRow" class="row g-3 mb-3" style="display:none;">
                        <div class="col-md-4">
                            <label class="form-label" for="SchedAmount">Amount <span
                                    class="text-danger">*</span></label>
                            <input type="number"
                                   id="SchedAmount"
                                   class="form-control @error('TotAmnt') is-invalid @enderror"
                                   step="0.01"
                                   min="0"
                                   value="{{ old('TotAmnt') }}"
                                   placeholder="Enter amount">
                            @error('TotAmnt')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="StartDate">Start Date <span
                                    class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control @error('StartDate') is-invalid @enderror"
                                   name="StartDate"
                                   value="{{ old('StartDate') }}">
                            @error('StartDate')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        {{--                        <div class="col-md-2">--}}
                        {{--                            <label class="form-label" for="EndDate">End Date <span class="text-danger">*</span></label>--}}
                        {{--                            <input type="date"--}}
                        {{--                                   class="form-control @error('EndDate') is-invalid @enderror"--}}
                        {{--                                   name="EndDate"--}}
                        {{--                                   value="{{ old('EndDate') }}">--}}
                        {{--                            @error('EndDate') <div class="invalid-feedback">{{ $message }}</div> @enderror--}}
                        {{--                        </div>--}}
                        <div class="col-md-4">
                            <label class="form-label" for="Frequency">Frequency <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('Frequency') is-invalid @enderror" name="Frequency"
                                    id="Frequency">
                                <option value="" disabled selected>-- Select Frequency --</option>
                                @forelse($paymentFrequencies as $paymentFrequency)
                                    <option
                                        value="{{ $paymentFrequency->Description }}" @selected(old('PaymentType') == $paymentFrequency->Description)>
                                        {{ $paymentFrequency->Description }}
                                    </option>
                                @empty
                                    <option disabled>No Payment Frequencies Found</option>
                                @endforelse
                                <option value="Monthly" @selected(old('Frequency') === 'Monthly')>Monthly</option>
                                <option value="Biweekly" @selected(old('Frequency') === 'Biweekly')>Biweekly</option>
                                <option value="Weekly" @selected(old('Frequency') === 'Weekly')>Weekly</option>
                                <option value="Custom" @selected(old('Frequency') === 'Custom')>Custom</option>
                            </select>
                            @error('Frequency')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="descriptionRow" class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label" for="Description">Description</label>
                            <textarea class="form-control @error('Description') is-invalid @enderror"
                                      name="Description"
                                      id="Description"
                                      rows="3"
                                      placeholder="Enter voucher description">{{ old('Description') }}</textarea>
                            @error('Description')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="formButtons" class="text-end" style="display:none;">
                        <a href="{{ route('paymentvoucher.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        <button type="submit"
                                id="submitButton"
                                class="btn btn-outline-success">
                            <i class="fas fa-save me-1"></i> Save Voucher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Form styling */
        .form-control, .form-select {
            font-size: 0.875rem;
            padding: 0.5rem;
        }

        /* Card styling */
        .card {
            border: none;
            border-radius: 0.5rem;
        }

        /* Button styling */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Hover effect for buttons */
        .btn-outline-success:hover, .btn-outline-secondary:hover {
            transition: background-color 0.2s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .form-control, .form-select {
                font-size: 0.75rem;
                padding: 0.4rem;
            }

            .btn-sm {
                padding: 0.2rem 0.4rem;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        (function () {
            const paymentTypeEl = document.getElementById('paymentType');
            const invoiceEl = document.getElementById('InvoiceNo');
            const amountRow = document.getElementById('amountRow');
            const scheduleRow = document.getElementById('scheduleRow');
            const formButtons = document.getElementById('formButtons');
            const payAmountEl = document.getElementById('PayAmount');
            const schedAmountEl = document.getElementById('SchedAmount');
            const startDateEl = document.getElementById('StartDate');
            const freqEl = document.getElementById('Frequency');
            const form = document.getElementById('paymentVoucherForm');
            const submitButton = document.getElementById('submitButton');
            const invoicePreviewCard = document.getElementById('invoicePreviewCard');
            const invoicePreviewBody = document.getElementById('invoicePreviewBody');
            const invoicePreviewBaseUrl = @json(url('finance/paymentvoucher/api/invoices'));

            function getSelectedInvoiceAmount() {
                const opt = invoiceEl?.options[invoiceEl.selectedIndex];
                return opt ? parseFloat(opt.getAttribute('data-amount') || '0') : 0;
            }

            function escapeHtml(input) {
                const text = `${input ?? ''}`;
                return text
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function formatMoney(currencySymbol, amount) {
                const num = Number.parseFloat(amount || 0);
                const safe = Number.isFinite(num) ? num : 0;
                return `${currencySymbol} ${safe.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }

            function renderAttachments(attachments) {
                if (!Array.isArray(attachments) || attachments.length === 0) {
                    return '<span class="text-muted small">No attachments on this invoice.</span>';
                }

                const items = attachments.map((doc) => {
                    const name = escapeHtml(doc.name || doc.document_id || 'Attachment');
                    const mime = escapeHtml(doc.mime_type || '');
                    return `<li class="small mb-1"><i class="fas fa-paperclip me-1 text-muted"></i>${name}${mime ? ` <span class="text-muted">(${mime})</span>` : ''}</li>`;
                }).join('');

                return `<ul class="mb-0 ps-3">${items}</ul>`;
            }

            function renderContractDetails(contract, currencySymbol) {
                const milestones = Array.isArray(contract?.milestones) ? contract.milestones : [];
                if (!milestones.length) {
                    return '<div class="text-muted small">No milestone allocations for this contract invoice.</div>';
                }

                const rows = milestones.map((m) => {
                    const checklistItems = Array.isArray(m.checklist_items) ? m.checklist_items : [];
                    const checklistHtml = checklistItems.length
                        ? `<ul class="mb-0 ps-3">` + checklistItems.map((c) => {
                            return `<li class="small mb-1">${escapeHtml(c.description)} <span class="badge ${c.fulfilled ? 'bg-success' : 'bg-warning text-dark'}">${c.fulfilled ? 'Fulfilled' : 'Pending'}</span> <span class="text-muted">[${c.required ? 'Required' : 'Optional'}]</span>${c.notes ? ` <span class="text-muted">(${escapeHtml(c.notes)})</span>` : ''}</li>`;
                        }).join('') + `</ul>`
                        : '<span class="text-muted small">No checklist items.</span>';

                    return `
                        <tr>
                            <td><strong>M${escapeHtml(m.milestone_no)} - ${escapeHtml(m.title)}</strong></td>
                            <td><span class="badge ${m.status === 'Accepted' ? 'bg-success' : (m.status === 'Waived' ? 'bg-secondary' : (m.status === 'Submitted' ? 'bg-info' : (m.status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark')))}">${escapeHtml(m.status || 'N/A')}</span></td>
                            <td>${escapeHtml(m.due_date || 'N/A')}</td>
                            <td>${escapeHtml(m.required_checklist_fulfilled || 0)}/${escapeHtml(m.required_checklist_total || 0)}</td>
                            <td class="text-end">${formatMoney(currencySymbol, m.billed_amount || 0)}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="bg-light-subtle">${checklistHtml}</td>
                        </tr>
                    `;
                }).join('');

                return `
                    <div class="mt-2">
                        <div class="small text-muted mb-2">Contract Ref: <strong>${escapeHtml(contract.reference || 'N/A')}</strong></div>
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Milestone</th>
                                    <th>Status</th>
                                    <th>Due</th>
                                    <th>Checklist</th>
                                    <th class="text-end">Billed</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                `;
            }

            function renderPoDetails(po, currencySymbol) {
                const order = po?.order || null;
                const grn = po?.grn || null;
                const items = Array.isArray(grn?.items) ? grn.items : [];

                const orderHtml = order
                    ? `
                        <div class="small mb-1"><span class="text-muted">PO:</span> <strong>${escapeHtml(order.order_no || 'N/A')}</strong></div>
                        <div class="small mb-1"><span class="text-muted">Date:</span> ${escapeHtml(order.order_date || 'N/A')}</div>
                        <div class="small mb-1"><span class="text-muted">Before Tax:</span> ${formatMoney(currencySymbol, order.before_tax || 0)}</div>
                        <div class="small mb-1"><span class="text-muted">Tax %:</span> ${Number.parseFloat(order.tax_percentage || 0).toFixed(2)}%</div>
                        <div class="small"><span class="text-muted">After Tax:</span> <strong>${formatMoney(currencySymbol, order.after_tax || 0)}</strong></div>
                    `
                    : '<div class="text-muted small">No matched PO details found.</div>';

                const grnHeaderHtml = grn
                    ? `
                        <div class="small mb-1"><span class="text-muted">GRN:</span> <strong>${escapeHtml(grn.grn_id || 'N/A')}</strong></div>
                        <div class="small mb-1"><span class="text-muted">Received:</span> ${escapeHtml(grn.received_date || 'N/A')}</div>
                        <div class="small mb-1"><span class="text-muted">Ordered Qty:</span> ${Number.parseFloat(grn.ordered_qty_total || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                        <div class="small"><span class="text-muted">Received Qty:</span> ${Number.parseFloat(grn.received_qty_total || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                    `
                    : '<div class="text-muted small">No matched GRN details found.</div>';

                const grnItemsHtml = items.length
                    ? `
                        <table class="table table-sm table-bordered align-middle mt-2 mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end">PO Qty</th>
                                    <th class="text-end">Received Qty</th>
                                    <th class="text-center">Match</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${items.map((item) => {
                                    const poQty = Number.parseFloat(item.po_qty || 0);
                                    const recQty = Number.parseFloat(item.received_qty || 0);
                                    const matched = Math.abs(poQty - recQty) <= 0.0001;
                                    return `
                                        <tr>
                                            <td>${escapeHtml(item.item_name || 'Item')}</td>
                                            <td class="text-end">${poQty.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                            <td class="text-end">${recQty.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                            <td class="text-center"><span class="badge ${matched ? 'bg-success' : 'bg-warning text-dark'}">${matched ? 'Matched' : 'Variance'}</span></td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    `
                    : '<div class="text-muted small mt-2">No GRN line items found.</div>';

                return `
                    <div class="row g-3 mt-1">
                        <div class="col-md-6"><div class="border rounded-3 p-2 h-100">${orderHtml}</div></div>
                        <div class="col-md-6"><div class="border rounded-3 p-2 h-100">${grnHeaderHtml}</div></div>
                    </div>
                    ${grnItemsHtml}
                `;
            }

            async function loadInvoicePreview() {
                if (!invoicePreviewCard || !invoicePreviewBody) return;

                const invoiceId = invoiceEl?.value || '';
                if (!invoiceId) {
                    invoicePreviewCard.classList.add('d-none');
                    invoicePreviewBody.innerHTML = '<div class="text-muted small">Select an invoice to view details.</div>';
                    return;
                }

                invoicePreviewCard.classList.remove('d-none');
                invoicePreviewBody.innerHTML = '<div class="text-muted small"><i class="fas fa-spinner fa-spin me-2"></i>Loading invoice preview...</div>';

                try {
                    const response = await fetch(`${invoicePreviewBaseUrl}/${invoiceId}/preview`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const payload = await response.json();
                    const invoice = payload?.invoice || {};
                    const currencySymbol = invoice.currency_symbol || invoice.currency_code || 'KES';
                    const sourceType = String(invoice.source_type || 'PO').toUpperCase();
                    const attachmentsHtml = renderAttachments(payload?.attachments || []);

                    const sourceDetailsHtml = sourceType === 'CONTRACT'
                        ? renderContractDetails(payload?.contract || {}, currencySymbol)
                        : renderPoDetails(payload?.po || {}, currencySymbol);

                    invoicePreviewBody.innerHTML = `
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div>
                                <strong>[${escapeHtml(sourceType)}] ${escapeHtml(invoice.invoice_number || '')}</strong>
                                <div class="small text-muted">Invoice Date: ${escapeHtml(invoice.invoice_date || 'N/A')} | Due Date: ${escapeHtml(invoice.due_date || 'N/A')}</div>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted">Balance</div>
                                <div><strong>${formatMoney(currencySymbol, invoice.balance || 0)}</strong></div>
                                ${invoice.view_url ? `<a class="small" href="${escapeHtml(invoice.view_url)}" target="_blank" rel="noopener">Open invoice</a>` : ''}
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-3"><div class="small text-muted">Before Tax</div><div><strong>${formatMoney(currencySymbol, invoice.before_tax || 0)}</strong></div></div>
                            <div class="col-md-3"><div class="small text-muted">Tax (${Number.parseFloat(invoice.tax_percentage || 0).toFixed(2)}%)</div><div><strong>${formatMoney(currencySymbol, invoice.tax_amount || 0)}</strong></div></div>
                            <div class="col-md-3"><div class="small text-muted">Total</div><div><strong>${formatMoney(currencySymbol, invoice.total_amount || 0)}</strong></div></div>
                            <div class="col-md-3"><div class="small text-muted">Already Paid</div><div><strong>${formatMoney(currencySymbol, invoice.amount_paid || 0)}</strong></div></div>
                        </div>
                        <div class="mb-2">
                            <div class="small text-muted mb-1">Attachments</div>
                            ${attachmentsHtml}
                        </div>
                        <div>
                            <div class="small text-muted mb-1">${sourceType === 'CONTRACT' ? 'Milestones and Checklist' : 'Matched PO and GRN'}</div>
                            ${sourceDetailsHtml}
                        </div>
                    `;
                } catch (error) {
                    invoicePreviewBody.innerHTML = `<div class="text-danger small">Failed to load invoice preview. ${escapeHtml(error.message)}</div>`;
                }
            }

            function setRequired(el, on) {
                if (!el) return;
                el[on ? 'setAttribute' : 'removeAttribute']('required', 'required');
            }

            function setReadonly(el, on) {
                if (!el) return;
                el[on ? 'setAttribute' : 'removeAttribute']('readonly', 'readonly');
            }

            function resetSubmitButton() {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-save me-1"></i> Save Voucher';
                }
            }

            function updateVisibility() {
                const type = paymentTypeEl?.value || '';

                // Hide all sections first
                amountRow.style.display = 'none';
                scheduleRow.style.display = 'none';
                formButtons.style.display = 'none';

                // Reset required and readonly
                setRequired(payAmountEl, false);
                setRequired(schedAmountEl, false);
                setRequired(startDateEl, false);
                setRequired(freqEl, false);
                setReadonly(payAmountEl, false);

                if (type === 'Full' || type === 'Partial') {
                    amountRow.style.display = 'flex';
                    setRequired(payAmountEl, true);
                    if (type === 'Full') {
                        const amt = getSelectedInvoiceAmount();
                        payAmountEl.value = amt ? amt.toFixed(2) : '';
                        setReadonly(payAmountEl, true);
                    }
                    formButtons.style.display = 'block';
                } else if (type === 'Scheduled') {
                    scheduleRow.style.display = 'flex';
                    setRequired(schedAmountEl, true);
                    setRequired(startDateEl, true);
                    setRequired(freqEl, true);
                    const amt = getSelectedInvoiceAmount();
                    schedAmountEl.value = amt ? amt.toFixed(2) : '';
                    payAmountEl.value = schedAmountEl.value;
                    formButtons.style.display = 'block';
                }
            }

            if (schedAmountEl) {
                schedAmountEl.addEventListener('input', () => {
                    if (payAmountEl) payAmountEl.value = schedAmountEl.value;
                });
            }

            if (invoiceEl) {
                invoiceEl.addEventListener('change', () => {
                    const type = paymentTypeEl?.value || '';
                    const amt = getSelectedInvoiceAmount();
                    if (type === 'Full') {
                        payAmountEl.value = amt ? amt.toFixed(2) : '';
                        setReadonly(payAmountEl, true);
                    } else if (type === 'Scheduled') {
                        schedAmountEl.value = amt ? amt.toFixed(2) : '';
                        payAmountEl.value = schedAmountEl.value;
                    }
                    loadInvoicePreview();
                });
            }

            if (paymentTypeEl) {
                paymentTypeEl.addEventListener('change', updateVisibility);
            }

            if (form && submitButton) {
                form.addEventListener('submit', (event) => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        resetSubmitButton();
                        form.classList.add('was-validated');
                    } else {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
                    }
                });
            }

            // Reset button on page load or error
            document.addEventListener('DOMContentLoaded', () => {
                updateVisibility();
                resetSubmitButton();
                form.classList.add('needs-validation');
                loadInvoicePreview();
            });
        })();
    </script>
@endsection
