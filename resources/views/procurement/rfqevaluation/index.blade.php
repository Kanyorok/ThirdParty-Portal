@extends('layouts.app')

@section('title', 'Supplier Evaluations')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2></h2>
        <a href="{{ route('evaluations.create') }}" class="btn btn-success">+ Create Evaluation</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Committee Member</th>
                    <th>RFQ No</th>
                    <th>Supplier</th>
                    <th>Total Quoted</th>
                    <th>Delivery Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @php $rowIndex = 1; @endphp
            @forelse ($rfqEvaluations as $index => $evaluation)
                @php
                    $grouped = $evaluation->evaluations->groupBy('SupplierId');
                @endphp

                @foreach ($grouped as $supplierId => $evalGroup)
                    @php
                        $supplier = $evalGroup->first()->supplier;
                        $response = \App\Models\Procurement\RFQResponse::where('SupplierId', $supplierId)
                            ->where('RFQId', $evaluation->RFQId)
                            ->first();
                    @endphp
                    <tr>
                        <td>{{ $rowIndex++ }}</td>
                        <td>{{ $evaluation->CommitteeMemberName }}</td>
                        <td>{{ $evaluation->rfq->RFQNumber ?? 'N/A' }}</td>
                        <td>{{ $supplier->SupplierName ?? 'N/A' }}</td>
                        <td>{{ number_format($response->TotalPayable ?? 0, 2) }}</td>
                        <td>{{ $response->DurationDays ?? '-' }} Days</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#viewModal-{{ $evaluation->Id }}">
                                View
                            </button>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" class="text-center">No evaluations found.</td>
                </tr>
            @endforelse
            </tbody>

        </table>
    </div>
</div>
<!-- Evaluation Details Modal -->
<div class="modal fade" id="viewModal-{{ $evaluation->Id }}" tabindex="-1"
     aria-labelledby="viewModalLabel-{{ $evaluation->Id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel-{{ $evaluation->Id }}">
                    Evaluation Details for RFQ #{{ $evaluation->rfq->RFQNumber ?? 'N/A' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- RFQ & Committee Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Committee Member:</strong> {{ $evaluation->CommitteeMemberName }}</p>
                        <p><strong>RFQ Number:</strong> {{ $evaluation->rfq->RFQNumber ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>RFQ Comment:</strong> {{ $evaluation->RFQComment ?? 'None' }}</p>
                        <p><strong>Confirmed:</strong> {{ $evaluation->Confirmation ? '✅ Yes' : '❌ No' }}</p>
                    </div>
                </div>

                <hr>

                <!-- Supplier Evaluations -->
                @php
                    $groupedBySupplier = $evaluation->evaluations->groupBy('SupplierId');
                @endphp

                @foreach ($groupedBySupplier as $supplierId => $evalGroup)
                    @php
                        $supplier = $evalGroup->first()->supplier;
                        $response = \App\Models\Procurement\RFQResponse::with('items.uom')
                            ->where('SupplierId', $supplierId)
                            ->where('RFQId', $evaluation->RFQId)
                            ->first();

                        $groupedBySection = $evalGroup->groupBy(fn($e) => $e->rfqCriteria?->section?->SectionName ?? 'Uncategorized');
                    @endphp

                    <div class="card mb-4">
                        <div class="card-header bg-light fw-bold">
                            Supplier: {{ $supplier->SupplierName ?? 'N/A' }}
                        </div>
                        <div class="card-body">
                            <!-- Quote and Delivery Info -->
                            <p><strong>Total Quoted:</strong> KES {{ number_format($response->TotalPayable ?? 0, 2) }}
                            </p>
                            <p><strong>Delivery Time:</strong> {{ $response->DurationDays ?? 'N/A' }} Days</p>

                            <!-- Quoted Items -->
                            @if($response && $response->items->count())
                                <h6 class="mt-3">Quoted Items</h6>
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>UOM</th>
                                        <th>Quantity</th>
                                        <th>Quoted Price</th>
                                        <th>Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($response->items as $item)
                                        <tr>
                                            <td>{{ $item->ItemName ?? 'N/A' }}</td>
                                            <td>{{ $item->uom->Name ?? 'N/A' }}</td>
                                            <td>{{ $item->Quantity }}</td>
                                            <td>{{ number_format($item->QuotedPrice ?? 0, 2) }}</td>
                                            <td>{{ number_format($item->TotalPayable ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif

                            <!-- Evaluation Table (Grouped by Section) -->
                            <h6 class="mt-4">Evaluation Breakdown</h6>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                <tr>
                                    <th>Section</th>
                                    <th>Criteria</th>
                                    <th>Maximum Score</th>
                                    <th>Score</th>
                                    <th>Comments</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $totalScore = 0; @endphp

                                @foreach($groupedBySection as $section => $criteriaList)
                                    @php
                                        $firstEntry = $criteriaList->first();

                                        $sectionName = $firstEntry->rfqCriteriaUnscoped?->section?->SectionName ?? 'Uncategorized';
                                        $sectionWeight = $firstEntry->rfqCriteriaUnscoped?->weightedSection?->Weight ?? 'N/A';
                                    @endphp

                                    <tr class="table-secondary fw-bold">
                                        <td colspan="5">
                                            {{ $sectionName }}
                                            <span class="text-muted">(Section Weight: {{ $sectionWeight }}%)</span>
                                        </td>
                                    </tr>

                                    @foreach ($criteriaList as $entry)
                                        <tr>
                                            <td></td>
                                            <td>{{ $entry->criteria->CriteriaName ?? 'N/A' }}</td>
                                            <td>10</td>
                                            <td>{{ $entry->Score }}</td>
                                            <td>{{ $entry->Comments ?? '-' }}</td>
                                        </tr>
                                        @php $totalScore += $entry->Score; @endphp
                                    @endforeach
                                @endforeach

                                <tr class="fw-bold bg-light">
                                    <td colspan="3" class="text-end">Total Score</td>
                                    <td>{{ $totalScore }}</td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
