@extends('layouts.app')
@section('title', 'Award RFQ')
@section('content')

    <div class="container mt-4">
        @if($existingAward)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                This RFQ has already been awarded to
                <strong>{{ $existingAward->winningSupplier->SupplierName }}</strong>
                on {{ $existingAward->AwardDate->format('d M Y') }}.
                Status: <span
                    class="badge {{ $existingAward->status_badge['class'] }}">{{ $existingAward->status_badge['text'] }}</span>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                🏆 Award RFQ: {{ $tender->TenderNo }} – {{ $tender->Title }}
            </div>
            <div class="card-body">
                @if($suppliers->isEmpty())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No responsive suppliers found for this RFQ.
                    </div>
                @else
                    <form action="{{ route('procawards.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tender_id" value="{{ $tender->Id }}">

                        <!-- RFQ Summary -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Created By</label>
                                <input type="text" class="form-control" value="{{ $tender->createdBy->Name ?? 'N/A' }}"
                                       readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Submission Deadline</label>
                                <input type="text" class="form-control"
                                       value="{{ $tender->SubmissionDeadline->format('d M Y') }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Responsive Quotes</label>
                                <input type="text" class="form-control" value="{{ $suppliers->count() }}" readonly>
                            </div>
                        </div>

                        <h5 class="mb-3">📋 Compare Quotations</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Supplier</th>
                                    <th>Quoted Amount</th>
                                    <th>Delivery Time</th>
                                    <th>Payment Terms</th>
                                    <th>Responsive?</th>
                                    @if(!$existingAward)
                                        <th>Select</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($suppliers as $index => $supplier)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $supplier['name'] }}</td>
                                        <td>KES {{ number_format($supplier['quoted_amount']) }}</td>
                                        <td>{{ $supplier['delivery_time'] }}</td>
                                        <td>{{ $supplier['payment_terms'] }}</td>
                                        <td class="text-center">
                                                <span
                                                    class="badge {{ $supplier['is_responsive'] ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $supplier['is_responsive'] ? 'Yes' : 'No' }}
                                                </span>
                                        </td>
                                        @if(!$existingAward)
                                            <td class="text-center">
                                                <input type="radio" name="winning_supplier_id"
                                                       value="{{ $supplier['id'] }}"
                                                    {{ $index === 0 ? 'checked' : '' }}
                                                    {{ !$supplier['is_responsive'] ? 'disabled' : '' }}>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(!$existingAward)
                            <!-- Award Details -->
                            <div class="mb-3">
                                <label class="form-label">Award Justification <span class="text-danger">*</span></label>
                                <textarea name="award_justification" class="form-control" rows="3" required
                                          placeholder="Provide justification for selecting this supplier...">{{ old('award_justification', 'Most competitive responsive quotation with acceptable terms and conditions.') }}</textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Contract Value</label>
                                    <input type="number" name="awarded_amount" class="form-control" step="0.01"
                                           placeholder="Final contract amount" value="{{ old('awarded_amount') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Expected Delivery Date</label>
                                    <input type="date" name="contract_start_date" class="form-control"
                                           value="{{ old('contract_start_date') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Contract End Date</label>
                                    <input type="date" name="contract_end_date" class="form-control"
                                           value="{{ old('contract_end_date') }}">
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="notify_unsuccessful"
                                       id="notifyUnsuccessful" value="1"
                                    {{ old('notify_unsuccessful', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="notifyUnsuccessful">
                                    Send regret letter to unsuccessful suppliers
                                </label>
                            </div>

                            <!-- Submit -->
                            <div class="text-end">
                                <a href="{{ route('procawards.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-success">
                                    ✅ Create Award (Pending Approval)
                                </button>
                            </div>
                        @endif
                    </form>
                @endif
            </div>
        </div>
    </div>

@endsection
