@extends('layouts.app')

@section('title', 'Supplier Evaluations')

@section('content')
  <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="mb-0">RFQ Evaluations</h2>
      <a href="{{ route('evaluations.create') }}" class="btn btn-success">+ Create Evaluation</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

      <div class="table-responsive mt-3">
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
              // Group evaluations by RFQ number, then sort groups so the RFQs with the latest responses appear first.
              $groupedByRFQ = collect($evaluationsRanked)
                ->groupBy('rfq.RFQNumber')
                ->sortByDesc(function($group) {
                  // determine latest response/evaluation id in the group (best-effort fallbacks)
                  return $group->max(function($item) {
                    return $item['response']->Id ?? $item['response']->id ?? $item['evaluation']->Id ?? $item['evaluation']->id ?? 0;
                  });
                });
          @endphp
          @forelse ($groupedByRFQ as $rfqNumber => $group)
              <tr class="table-primary fw-bold"></tr>
              <td colspan="9">
                  <div class="d-flex justify-content-between align-items-center">
                      <span>RFQ Number: {{ $rfqNumber }}</span>
                      <div class="d-flex gap-2 align-items-center">
                          @php 
                            $rfqIdForGroup = optional($group->first()['rfq'] ?? null)->Id ?? ($group->first()['rfq']->id ?? null);
                            $awardStatus = $rfqAwardStatus[$rfqIdForGroup] ?? null;
                            $isAwarded = $awardStatus['isAwarded'] ?? false;
                            $allMembersEvaluated = $awardStatus['allMembersEvaluated'] ?? false;
                            $award = $awardStatus['award'] ?? null;
                            
                            // Get top supplier (rank 1) for this RFQ
                            $sortedSuppliers = $group->sortByDesc('weightedTotal')->values();
                            $topSupplier = $sortedSuppliers->first();
                          @endphp
                          
                          {{-- Award Status/Button --}}
                          @if($isAwarded && $award)
                              @php
                                $awardedSupplierName = \App\Models\Procurement\RFQResponse::where('RFQId', $rfqIdForGroup)
                                    ->where('SupplierId', $award->SupplierId)
                                    ->with('supplier.thirdParty')
                                    ->first()
                                    ?->supplier?->thirdParty?->ThirdPartyName ?? 'Supplier';
                              @endphp
                              <span class="badge bg-success">
                                  <i class="bi bi-trophy me-1"></i>Awarded: {{ $awardedSupplierName }}
                              </span>
                          @elseif($allMembersEvaluated && $rfqIdForGroup)
                              {{-- Award Dropdown - Only show when all evaluations complete --}}
                              <div class="dropdown">
                                  <button class="btn btn-sm btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                      <i class="bi bi-award me-1"></i>Award
                                  </button>
                                  <ul class="dropdown-menu dropdown-menu-end">
                                      <li><h6 class="dropdown-header">Select Supplier to Award</h6></li>
                                      @foreach($sortedSuppliers->unique('supplierId') as $supplierEntry)
                                          <li>
                                              <form action="{{ route('evaluations.award', ['rfq' => $rfqIdForGroup, 'supplier' => $supplierEntry['supplierId']]) }}" method="POST" class="d-inline">
                                                  @csrf
                                                  <input type="hidden" name="Comments" value="Awarded via evaluation list">
                                                  <button type="submit" class="dropdown-item">
                                                      {{ $supplierEntry['supplier']?->thirdParty?->thirdParty?->ThirdPartyName ?? $supplierEntry['supplier']?->thirdParty?->thirdParty?->TradingName ?? 'Unknown' }}
                                                      <small class="text-muted">({{ $supplierEntry['weightedTotal'] }}%)</small>
                                                  </button>
                                              </form>
                                          </li>
                                      @endforeach
                                  </ul>
                              </div>
                          @else
                              {{-- Show pending status --}}
                              @if($awardStatus)
                                  <span class="badge bg-warning text-dark" title="Waiting for all committee members to complete evaluations">
                                      <i class="bi bi-clock me-1"></i>{{ $awardStatus['evaluatedCount'] ?? 0 }}/{{ $awardStatus['acceptedCount'] ?? 0 }} Evaluated
                                  </span>
                              @endif
                          @endif
                          
                          @if($rfqIdForGroup)
                              <a href="{{ route('evaluations.consolidated', ['rfq' => $rfqIdForGroup]) }}"
                                 class="btn btn-sm btn-outline-primary">
                                  Consolidated Scores
                              </a>
                          @endif
                      </div>
                  </div>
              </td>
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
                  <td>{{ $response?->supplier?->thirdParty?->thirdParty?->ThirdPartyName ?? $response?->supplier?->thirdParty?->thirdParty?->TradingName ?? $response?->SupplierName ?? 'N/A' }}</td>
                <td>{{ number_format($response->TotalPayable ?? 0, 2) }}</td>
                <td>{{ $response->DurationDays ?? '-' }} Days</td>
                <td>{{ $weightedTotal }}%</td>
                <td><strong>{{ $rankCounter }}</strong></td>
                <td>
                  <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                    data-bs-target="#viewModal-{{ $evaluation->Id }}-{{ $supplier->Id }}">
                    View
                  </button>
                  @php
                    $currentEmployeeId = optional(auth()->user())->EmployeeId ?? optional(auth()->user()?->employee)->Id;
                    $canEdit = (int)$evaluation->UserCode === (int)$currentEmployeeId;
                  @endphp
                  @if($canEdit)
                    <a href="{{ route('evaluations.edit', $evaluation->Id) }}" class="btn btn-sm btn-outline-warning">
                      Edit
                    </a>
                  @endif
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
        $response = \App\Models\Procurement\RFQResponse::with(['items.uom', 'supplier.thirdParty.thirdParty'])
            ->where('SupplierId', $supplierId)
            ->where('RFQId', $evaluation->RFQId)
            ->first();
        $groupedBySection = $evalGroup->groupBy(fn($e) => $e->rfqCriteriaUnscoped?->section?->SectionName ?? 'Uncategorized');
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
                  <p><strong>Confirmed:</strong> {{ $evaluation->Confirmation ? '✅ Yes' : '❌ No' }}</p>
                </div>
              </div>
                <hr>
              <div class="card mb-4">
                <div class="card-header bg-light fw-bold">
                    Supplier: {{ $response?->supplier?->thirdParty?->thirdParty?->ThirdPartyName ?? $response?->supplier?->thirdParty?->thirdParty?->TradingName ?? $response?->SupplierName ?? 'N/A' }}
                </div>
                <div class="card-body">
                  <p><strong>Total Quoted:</strong> KES {{ number_format($response->TotalPayable ?? 0, 2) }}</p>
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
                          $sectionId = $firstEntry->rfqCriteriaUnscoped?->SectionID ?? null;
                          $sectionWeight = $sectionId ? ($rfqSectionWeights[$evaluation->RFQId][$sectionId] ?? 0) : 0;
                          $sectionTotal = 0;
                          $maxScorePerCriteria = 10;
                          $totalMaxSectionScore = $maxScorePerCriteria * $criteriaList->count();
                        @endphp
                        <tr class="table-secondary fw-bold">
                          <td colspan="5">
                            {{ $sectionName }}
                            <span class="text-muted">(Section Weight: {{ $sectionWeight }}%)</span>
                          </td>
                        </tr>
                        @foreach ($criteriaList as $entry)
                          @php
                            $sectionTotal += $entry->Score;
                            $grandTotal += $entry->Score;
                          @endphp
                          <tr>
                            <td></td>
                              <td>{{ $entry->rfqCriteriaUnscoped?->criteria?->CriteriaName ?? 'N/A' }}</td>
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
