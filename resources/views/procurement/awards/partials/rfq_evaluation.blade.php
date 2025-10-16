<!-- RFQ Quotation Comparison -->
<h6 class="fw-bold mb-3">💰 Quotation Comparison</h6>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted">Total Quotes</h6>
                <h4 class="text-primary mb-0">{{ count($suppliers) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted">Responsive</h6>
                <h4 class="text-success mb-0">{{ collect($suppliers)->where('is_responsive', true)->count() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted">Lowest Quote</h6>
                <h4 class="text-warning mb-0">
                    @php
                        $lowestQuote = collect($suppliers)->where('is_responsive', true)->min('quoted_amount');
                    @endphp
                    {{ $lowestQuote ? 'KES ' . number_format($lowestQuote) : 'N/A' }}
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted">Avg Quote</h6>
                <h4 class="text-info mb-0">
                    @php
                        $avgQuote = collect($suppliers)->where('is_responsive', true)->avg('quoted_amount');
                    @endphp
                    {{ $avgQuote ? 'KES ' . number_format($avgQuote) : 'N/A' }}
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Quotation Comparison Table -->
<div class="table-responsive mb-4">
    <table class="table table-bordered align-middle">
        <thead class="table-success text-center">
            <tr>
                <th width="5%">#</th>
                <th width="25%">Supplier Name</th>
                <th width="15%">Quoted Amount</th>
                <th width="15%">Delivery Time</th>
                <th width="15%">Payment Terms</th>
                <th width="10%">Responsive?</th>
                @if(!isset($existingAward))
                    <th width="15%">Select Winner</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $index => $supplier)
                @php
                    $isLowest = $supplier['quoted_amount'] == collect($suppliers)->where('is_responsive', true)->min('quoted_amount') && $supplier['is_responsive'];
                @endphp
                <tr class="{{ $isLowest ? 'table-warning' : '' }}">
                    <td class="text-center">
                        {{ $index + 1 }}
                        @if($isLowest)
                            <i class="fas fa-star text-warning" title="Lowest Quote"></i>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-success text-white me-2" style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                {{ strtoupper(substr($supplier['name'], 0, 2)) }}
                            </div>
                            <div>
                                <strong>{{ $supplier['name'] }}</strong>
                                @if($isLowest)
                                    <br><small class="badge bg-warning text-dark">Lowest Bid</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <strong class="{{ $supplier['quoted_amount'] > 0 ? 'text-dark' : 'text-muted' }}">
                            {{ $supplier['quoted_amount'] > 0 ? 'KES ' . number_format($supplier['quoted_amount']) : 'N/A' }}
                        </strong>
                    </td>
                    <td class="text-center">{{ $supplier['delivery_time'] ?: 'N/A' }}</td>
                    <td class="text-center">{{ $supplier['payment_terms'] ?: 'N/A' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $supplier['is_responsive'] ? 'bg-success' : 'bg-danger' }}">
                            {{ $supplier['is_responsive'] ? '✅ Yes' : '❌ No' }}
                        </span>
                    </td>
                    @if(!isset($existingAward))
                        <td class="text-center">
                            @if($supplier['is_responsive'])
                                <input type="radio" name="winning_supplier_id" 
                                       value="{{ $supplier['id'] }}" 
                                       {{ $isLowest ? 'checked' : '' }}
                                       class="form-check-input"
                                       style="transform: scale(1.2);">
                                @if($isLowest)
                                    <input type="hidden" name="awarded_amount" value="{{ $supplier['quoted_amount'] }}">
                                @endif
                            @else
                                <input type="radio" disabled title="Non-responsive supplier" class="form-check-input">
                                <small class="text-muted d-block">Non-responsive</small>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if(collect($suppliers)->where('is_responsive', true)->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>No responsive suppliers found.</strong> 
        Please ensure quotations have been submitted and responsiveness check has been completed.
    </div>
@endif
