@extends('layouts.app')
@section('title', 'Tender Criteria')
@section('content')

    <div class="container mt-4">
        <h4 class="mb-3">📑 {{$tender->TenderNo}} Criteria Form</h4>

        @if(isset($filteredSections) && $filteredSections->isNotEmpty())
            <div class="alert alert-warning">
                <strong>Note:</strong> The following sections are not available for configuration because the base Section record is missing: 
                <ul class="mb-0">
                    @foreach($filteredSections as $fs)
                        <li>{{ $fs }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{route('tender-criteria.store')}}" method="POST" enctype="multipart/form-data"
              id="sectionCriteriaForm">
            @csrf
            @method('POST')

            <input type="hidden" name="TenderId" value="{{ $TenderId }}">

            <table class="table">
        @foreach ($tenderSections as $section)
                    <tr class="table-secondary section-row">
            <td class="fw-bold" colspan="2">{{ $section->sections->SectionName ?? ('Section #'.$section->SectionID) }}</td>
                        <td>
                            <input type="number"
                                   class="form-control"
                   name="weights[{{ $section->sections->Id ?? $section->SectionID }}]"
                                   value="{{ number_format($section->Weight, 2) }}"
                                   step="0.01" min="0" max="100" required>
                        </td>
                    </tr>

            @foreach ($section->criteria as $criteria)
                        <tr class="criteria-row">
                            <td>
                                <input type="checkbox"
                       name="criterias[{{ $section->sections->Id ?? $section->SectionID }}][]"
                                       value="{{ $criteria->Id }}"
                                    {{ $criteria->isChecked ? 'checked' : '' }}>
                            </td>
                            <td colspan="2">{{ $criteria->CriteriaName }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </table>

            <a href="{{route('tenderevaluations.index')}}">
                <button type="button" class="btn btn-secondary">Back</button>
            </a>
            <button type="submit" class="btn btn-primary">Save Criteria</button>
        </form>
    </div>

    <!-- Inline JavaScript to enforce 100% weight (do not disable inputs) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('sectionCriteriaForm');
            const totalWeightDisplay = document.getElementById('totalWeight');
            const submitBtn = document.getElementById('saveCriteriaBtn');

            function updateTotal() {
                let total = 0;
                const weightInputs = form.querySelectorAll('tr.section-row input[type="number"]');
                weightInputs.forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                if (totalWeightDisplay) {
                    totalWeightDisplay.textContent = total.toFixed(2);
                }
                return total;
            }

            // Attach listeners
            form.querySelectorAll('tr.section-row input[type="number"]').forEach(input => {
                input.addEventListener('input', updateTotal);
            });
            form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.addEventListener('change', updateTotal);
            });

            // Validate on submit
            form.addEventListener('submit', function (e) {
                const total = updateTotal();
                if (total.toFixed(2) !== '100.00') {
                    e.preventDefault();
                    alert('Total weight must be exactly 100.00%. Current total: ' + total.toFixed(2) + '%');
                }
            });

            updateTotal();
        });
    </script>
@endsection
