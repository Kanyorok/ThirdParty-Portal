@extends('layouts.app')

@section('title', 'Supplier Evaluations')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Supplier Evaluations</h2>
            <a href="{{ route('evaluations.create') }}" class="btn btn-success">+ Create Evaluation</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                <tr>
                    <th>S/N</th>
                    <th>Committee Member</th>
                    <th>RFQ No</th>
                    <th>Supplier</th>
                    <th>Total Quoted</th>
                    <th>Delivery Time</th>
                    <th>Weighted Score</th>
                    <th>Rank</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $groupedByRFQ = collect($evaluationsRanked)->groupBy('rfq.RFQNumber');
                @endphp
                @forelse ($groupedByRFQ as $rfqNumber => $group)
                    <tr class="table-primary fw-bold">
                        <td colspan="9">RFQ Number: {{ $rfqNumber }}</td>
                    </tr>

                    @php

                        $sortedGroup = $group->sortByDesc('weightedTotal')->values();
                        $rankCounter = 0;
                    @endphp

                    @foreach ($sortedGroup as $Index => $result)
                        @php
                            $evaluation = $result['evaluation'];
                            $supplier = $result['supplier'];
                            $response = $result['response'];
                            $weightedTotal = $result['weightedTotal'];
                            $rankCounter++;
                        @endphp

                        <tr>
                            <td>{{ $Index + 1 }}</td>
                            <td>{{ $evaluation->CommitteeMemberName }}</td>
                            <td>{{ $rfqNumber }}</td>
                            <td>{{ $supplier->SupplierName ?? 'N/A' }}</td>
                            <td>{{ number_format($response->TotalPayable ?? 0, 2) }}</td>
                            <td>{{ $response->DurationDays ?? '-' }} Days</td>
                            <td>{{ $weightedTotal }}%</td>
                            <td><strong>{{ $rankCounter }}</strong></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#viewModal-{{ $evaluation->Id }}-{{ $supplier->Id }}">
                                    View
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No evaluations found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Render modals separately below table --}}
    @foreach ($rfqEvaluations as $evaluation)
        @php
            $grouped = $evaluation->evaluations->groupBy('SupplierId');
        @endphp
        @foreach ($grouped as $supplierId => $evalGroup)
            @php
                $supplier = $evalGroup->first()->supplier;
                $response = \App\Models\Procurement\RFQResponse::with('items.uom')
                    ->where('SupplierId', $supplierId)
                    ->where('RFQId', $evaluation->RFQId)
                    ->first();
                $groupedBySection = $evalGroup->groupBy(fn($e) => $e->rfqCriteria?->section?->SectionName ?? 'Uncategorized');
            @endphp
                <!-- View Modal -->
            <div class="modal fade" id="viewModal-{{ $evaluation->Id }}-{{ $supplierId }}" tabindex="-1"
                 aria-labelledby="viewModalLabel-{{ $evaluation->Id }}-{{ $supplierId }}" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header d-flex justify-content-between align-items-center">
                            <h5 class="modal-title mb-0" id="viewModalLabel-{{ $evaluation->Id }}-{{ $supplierId }}">
                                Evaluation Details for RFQ #{{ $evaluation->rfq->RFQNumber ?? 'N/A' }}
                            </h5>
                            <div class="d-flex align-items-center gap-2 d-print-none">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="printModal('{{ $evaluation->Id }}-{{ $supplierId }}')">
                                    🖨️ Print
                                </button>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
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
                                    <p><strong>Confirmed:</strong> {{ $evaluation->Confirmation ? '✅ Yes' : '❌ No' }}
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <div class="card mb-4">
                                <div class="card-header bg-light fw-bold">
                                    Supplier: {{ $supplier->SupplierName ?? 'N/A' }}
                                </div>
                                <div class="card-body">
                                    <p><strong>Total Quoted:</strong>
                                        KES {{ number_format($response->TotalPayable ?? 0, 2) }}</p>
                                    <p><strong>Delivery Time:</strong> {{ $response->DurationDays ?? 'N/A' }} Days</p>
                                    @if ($response && $response->items->count())
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
                                            @foreach ($response->items as $item)
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
                                        @php
                                            $grandTotal = 0;
                                            $grandWeightedTotal = 0;
                                        @endphp
                                        @foreach ($groupedBySection as $section => $criteriaList)
                                            @php
                                                $firstEntry = $criteriaList->first();
                                                $sectionName = $firstEntry->rfqCriteriaUnscoped?->section?->SectionName ?? 'Uncategorized';
                                                $sectionWeight = $firstEntry->rfqCriteriaUnscoped?->weightedSection?->Weight ?? 0;
                                                $sectionTotal = 0;
                                                $maxScorePerCriteria = 10;
                                                $totalMaxSectionScore = $maxScorePerCriteria * $criteriaList->count();
                                            @endphp
                                            <tr class="table-secondary fw-bold">
                                                <td colspan="5">
                                                    {{ $sectionName }}
                                                    <span
                                                        class="text-muted">(Section Weight: {{ $sectionWeight }}%)</span>
                                                </td>
                                            </tr>
                                            @foreach ($criteriaList as $entry)
                                                @php
                                                    $sectionTotal += $entry->Score;
                                                    $grandTotal += $entry->Score;
                                                @endphp
                                                <tr>
                                                    <td></td>
                                                    <td>{{ $entry->criteria->CriteriaName ?? 'N/A' }}</td>
                                                    <td>{{ $maxScorePerCriteria }}</td>
                                                    <td>{{ $entry->Score }}</td>
                                                    <td>{{ $entry->Comments ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                            @php
                                                $sectionWeightedScore =
                                                    $totalMaxSectionScore > 0
                                                        ? round(($sectionTotal / $totalMaxSectionScore) * $sectionWeight, 2)
                                                        : 0;
                                                $grandWeightedTotal += $sectionWeightedScore;
                                            @endphp
                                            <tr class="fw-bold bg-light">
                                                <td colspan="3" class="text-end">Subtotal for {{ $sectionName }}</td>
                                                <td>{{ $sectionTotal }}</td>
                                                <td class="text-muted">Weighted: {{ $sectionWeightedScore }}%</td>
                                            </tr>
                                        @endforeach
                                        <tr class="fw-bold bg-secondary text-white">
                                            <td colspan="3" class="text-end">Total Score</td>
                                            <td>{{ $grandTotal }}</td>
                                            <td>Weighted Total: {{ $grandWeightedTotal }}%</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
    @push('scripts')
        <script>
            function printModal(id) {
                const modalContent = document.querySelector(`#viewModal-${id} .modal-content`);
                if (!modalContent) return;
                modalContent.querySelectorAll('.btn, .btn-close, .d-print-none').forEach(el => el.remove());
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
            <html>
                <head>
                    <title>Print Evaluation</title>
                    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .modal-content { box-shadow: none; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ccc; padding: 8px; }
                        th { background: #f8f8f8; }
                    </style>
                </head>
                <body>
                    ${modalContent.innerHTML}
                </body>
            </html>
        `);
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => printWindow.print(), 500);
            }
        </script>
    @endpush

@endsection
