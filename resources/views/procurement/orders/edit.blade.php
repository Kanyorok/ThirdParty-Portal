@extends('layouts.app')
@section('title', 'Edit Purchase Order')
@section('content')

<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Edit Purchase Order</h5>
        <a href="{{ route('purchaseOrder.show', $order->Id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('purchaseOrder.update', $order->Id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- ── Header card ── --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">Order Header</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    {{-- Order No (read-only) --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">LPO No</label>
                        <input type="text" class="form-control" value="{{ $orderInfo->OrderNo ?? $order->OrderNo ?? '--' }}" readonly>
                    </div>

                    {{-- Reference No --}}
                    <div class="col-md-3">
                        <label for="refNo" class="form-label fw-semibold">Reference No</label>
                        <input type="text" id="refNo" name="refNo" class="form-control @error('refNo') is-invalid @enderror"
                               value="{{ old('refNo', $orderInfo->ExtOrdNum ?? $order->ExtOrdNum ?? '') }}">
                        @error('refNo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Order Date --}}
                    <div class="col-md-3">
                        <label for="Date" class="form-label fw-semibold">Order Date <span class="text-danger">*</span></label>
                        <input type="date" id="Date" name="Date" required
                               class="form-control @error('Date') is-invalid @enderror"
                               value="{{ old('Date', isset($orderInfo->OrderDate) ? \Carbon\Carbon::parse($orderInfo->OrderDate)->format('Y-m-d') : ($order->OrderDate ? \Carbon\Carbon::parse($order->OrderDate)->format('Y-m-d') : '')) }}">
                        @error('Date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Priority --}}
                    <div class="col-md-3">
                        <label for="priority" class="form-label fw-semibold">Priority</label>
                        <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror">
                            <option value="">-- Select --</option>
                            @foreach(['Low', 'Medium', 'High', 'Urgent'] as $p)
                                <option value="{{ $p }}" {{ old('priority', $order->Priority) === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                        @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Supplier --}}
                    <div class="col-md-6">
                        <label for="supplier" class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
                        <select id="supplier" name="supplier" required class="form-select @error('supplier') is-invalid @enderror">
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers ?? [] as $s)
                                @php
                                    $sId   = $s->Id ?? $s->id ?? '';
                                    $sName = $s->TradingName ?? $s->ThirdPartyName ?? $s->Name ?? "Supplier #{$sId}";
                                    $currentAccountId = $orderInfo->AccountID ?? $order->AccountID ?? null;
                                @endphp
                                <option value="{{ $sId }}" {{ old('supplier', $currentAccountId) == $sId ? 'selected' : '' }}>
                                    {{ $sName }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Payment Terms --}}
                    <div class="col-md-6">
                        <label for="terms" class="form-label fw-semibold">Payment Terms <span class="text-danger">*</span></label>
                        <select id="terms" name="terms" required class="form-select @error('terms') is-invalid @enderror">
                            <option value="">-- Select Terms --</option>
                            @foreach($paymentTerms ?? [] as $term)
                                @php
                                    $termId   = $term->ID ?? $term->Id ?? '';
                                    $termDesc = $term->Description ?? $term->description ?? '';
                                    $currentTerms = $orderInfo->terms_id ?? $order->terms ?? null;
                                @endphp
                                <option value="{{ $termId }}" {{ old('terms', $currentTerms) == $termId ? 'selected' : '' }}>
                                    {{ $termDesc }}
                                </option>
                            @endforeach
                        </select>
                        @error('terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="col-md-6">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $order->Notes ?? '') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Delivery Terms --}}
                    <div class="col-md-6">
                        <label for="delivery_terms" class="form-label fw-semibold">Delivery Terms</label>
                        <textarea id="delivery_terms" name="delivery_terms" rows="2" class="form-control @error('delivery_terms') is-invalid @enderror">{{ old('delivery_terms', $order->DeliveryTerms ?? '') }}</textarea>
                        @error('delivery_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>{{-- /row --}}
            </div>{{-- /card-body --}}
        </div>{{-- /card --}}

        {{-- ── Line items (read-only display) ── --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white d-flex justify-content-between">
                <h6 class="mb-0">Line Items <small class="fw-normal">(read-only — manage lines on the detail view)</small></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Tax %</th>
                                <th class="text-end">Discount %</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lineInfo ?? [] as $i => $line)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $line->ItemName ?? ('#' . ($line->ItemID ?? '?')) }}</td>
                                <td>{{ $line->ItemDescription ?? '' }}</td>
                                <td class="text-end">{{ number_format((float)($line->Quantity ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float)($line->UnitPrice ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float)($line->Tax ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float)($line->Discount ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float)($line->LineTotal ?? 0), 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No line items found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Totals --}}
            @if(isset($orderInfo))
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-4 offset-md-8">
                        <div class="d-flex justify-content-between">
                            <span>Exclusive Total</span>
                            <strong>{{ number_format((float)($orderInfo->ExclusiveTotal ?? 0), 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Tax Amount</span>
                            <strong>{{ number_format((float)($orderInfo->TaxAmount ?? 0), 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-1">
                            <span class="fw-semibold">Inclusive Total</span>
                            <strong>{{ number_format((float)($orderInfo->InclusiveTotal ?? 0), 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>{{-- /card --}}

        {{-- ── Hidden pass-through fields for required PurchaseOrderRequest validation ── --}}
        {{--
            PurchaseOrderRequest requires itemCode[], quantity[], unitPrice[], lineTotal[] arrays.
            Since this edit form only edits the header, we re-submit the existing line data so
            validation passes. These are populated by the JS below from the rendered line rows.
        --}}
        <div id="hidden-line-inputs">
            @foreach($lineInfo ?? [] as $line)
                <input type="hidden" name="itemCode[]"   value="{{ $line->ItemID   ?? 0 }}">
                <input type="hidden" name="quantity[]"   value="{{ $line->Quantity  ?? 0 }}">
                <input type="hidden" name="unitPrice[]"  value="{{ $line->UnitPrice ?? 0 }}">
                <input type="hidden" name="tax[]"        value="{{ $line->Tax       ?? 0 }}">
                <input type="hidden" name="discount[]"   value="{{ $line->Discount  ?? 0 }}">
                <input type="hidden" name="lineTotal[]"  value="{{ $line->LineTotal ?? 0 }}">
            @endforeach
        </div>

        {{-- Action buttons --}}
        <div class="d-flex justify-content-end gap-2 mt-2">
            <a href="{{ route('purchaseOrder.show', $order->Id) }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>

    </form>
</div>

@endsection
