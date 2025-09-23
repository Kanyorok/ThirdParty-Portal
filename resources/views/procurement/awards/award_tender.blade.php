@extends('layouts.app')
@section('title', 'Award Tender')
@section('content')

    <div class="container mt-4">
        @if($existingAward)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                This tender has already been awarded to <strong>{{ $existingAward->winningSupplier->SupplierName }}</strong> 
                on {{ $existingAward->AwardDate->format('d/m/Y') }}.
                Status: <span class="badge {{ $existingAward->status_badge['class'] }}">{{ $existingAward->status_badge['text'] }}</span>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                🏆 Award Tender: {{ $tender->TenderNo }} – {{ $tender->Title }}
            </div>
            <div class="card-body">
                <h5 class="mb-3">📊 Final Scoring Summary</h5>
                
                @if(empty($consolidatedScores))
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No responsive bids found or evaluations not completed.
                    </div>
                @else
                    <form action="{{ route('procawards.store') }}" method="POST" id="awardForm">
                        @csrf
                        <input type="hidden" name="tender_id" value="{{ $tender->Id }}">
                        
                        <table class="table table-bordered align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>Rank</th>
                                    <th>Bidder</th>
                                    <th>Technical Score</th>
                                    <th>Financial Score</th>
                                    <th>Total Score</th>
                                    <th>Responsive?</th>
                                    @if(!$existingAward)
                                        <th>Select Winner</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($consolidatedScores as $index => $score)
                                    <tr class="{{ $index === 0 ? 'table-success' : '' }}">
                                        <td class="text-center">
                                            @if($index === 0)
                                                <span class="badge bg-warning text-dark">1st</span>
                                            @elseif($index === 1)
                                                <span class="badge bg-info">2nd</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $score['supplier']->SupplierName }}</td>
                                        <td class="text-center">{{ number_format($score['technical_score'], 1) }}%</td>
                                        <td class="text-center">{{ number_format($score['financial_score'], 1) }}%</td>
                                        <td class="text-center"><strong>{{ number_format($score['total_score'], 1) }}%</strong></td>
                                        <td class="text-center">
                                            <span class="badge {{ $score['is_responsive'] ? 'bg-success' : 'bg-danger' }}">
                                                {{ $score['is_responsive'] ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        @if(!$existingAward)
                                            <td class="text-center">
                                                <input type="radio" name="winning_supplier_id" 
                                                       value="{{ $score['supplier']->Id }}" 
                                                       {{ $index === 0 ? 'checked' : '' }}
                                                       onchange="updateScores({{ $score['supplier']->Id }}, {{ $score['technical_score'] }}, {{ $score['financial_score'] }}, {{ $score['total_score'] }})">
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if(!$existingAward)
                            <!-- Hidden fields for selected supplier scores -->
                            <input type="hidden" name="technical_score" id="technical_score" value="{{ $consolidatedScores[0]['technical_score'] ?? 0 }}">
                            <input type="hidden" name="financial_score" id="financial_score" value="{{ $consolidatedScores[0]['financial_score'] ?? 0 }}">
                            <input type="hidden" name="total_score" id="total_score" value="{{ $consolidatedScores[0]['total_score'] ?? 0 }}">

                            <div class="mb-3">
                                <label class="form-label">Award Justification <span class="text-danger">*</span></label>
                                <textarea name="award_justification" class="form-control" rows="3" required
                                          placeholder="Provide justification for this award decision...">{{ old('award_justification', 'Highest scoring responsive bidder based on technical and financial evaluation criteria.') }}</textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Awarded Amount</label>
                                    <input type="number" name="awarded_amount" class="form-control" step="0.01" 
                                           placeholder="Contract amount" value="{{ old('awarded_amount') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Contract Start Date</label>
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
                                <input class="form-check-input" type="checkbox" name="notify_unsuccessful" id="notify" value="1" 
                                       {{ old('notify_unsuccessful', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify">
                                    Notify Unsuccessful Bidders
                                </label>
                            </div>

                            <div class="text-end">
                                <a href="{{ route('procawards.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">✅ Create Award (Pending Approval)</button>
                            </div>
                        @endif
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        function updateScores(supplierId, technicalScore, financialScore, totalScore) {
            document.getElementById('technical_score').value = technicalScore;
            document.getElementById('financial_score').value = financialScore;
            document.getElementById('total_score').value = totalScore;
        }
    </script>

@endsection
