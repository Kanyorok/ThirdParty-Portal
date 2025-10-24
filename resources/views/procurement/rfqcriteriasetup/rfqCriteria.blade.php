@extends('layouts.app')
@section('title', 'RFQ Criteria')
@section('content')

    <div class="container mt-4">
        <h4 class="mb-3">📑 {{ $rfq->RFQNumber }} Criteria Form</h4>

        <form action="{{ route('rfqcriterias.store') }}"
              method="POST" enctype="multipart/form-data" id="rfqCriteriaForm">
            @csrf

            <input type="hidden" name="rfq_id" value="{{ $rfq->Id }}">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>Total Weight: </strong>
                    <span id="totalWeightDisplay" class="badge bg-secondary">0.00%</span>
                </div>
                <small id="weightHint" class="text-muted">Ensure the total weight equals 100.00%</small>
            </div>
            <table class="table" id="sectionsTable">
                @foreach ($rfqSections as $section)
                    <tbody data-section-id="{{ $section->section->Id }}">
                    <tr class="table-secondary section-row">
                        <td class="fw-bold" colspan="2">{{ $section->section->SectionName }}</td>
                        <td class="d-flex gap-2">
                            <input type="number"
                                   class="form-control section-weight-input"
                                   name="weights[{{ $section->section->Id }}]"
                                   value="{{ number_format($section->Weight, 2) }}"
                                   step="0.01" min="0" max="100" required>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-section-btn"
                                    title="Remove Section">&times;
                            </button>
                        </td>
                    </tr>

                    @foreach ($section->section->criteria as $criteria)
                        <tr class="criteria-row">
                            <td style="width:40px;">
                                <input type="checkbox"
                                       name="criterias[{{ $section->section->Id }}][]"
                                       value="{{ $criteria->Id }}"
                                    {{ $criteria->isChecked ? 'checked' : '' }}>
                            </td>
                            <td colspan="2">{{ $criteria->CriteriaName }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                @endforeach
            </table>
            <div id="removedSectionsContainer"></div>
            <input type="hidden" name="client_normalized" id="clientNormalizedFlag" value="0">

            <a href="{{ route('rfqcriteriasetup.evaluations') }}">
                <button type="button" class="btn btn-secondary">Back</button>
            </a>
            <button type="submit" class="btn btn-primary">Save Criteria</button>
        </form>
    </div>

    <!-- Enhanced JavaScript for dynamic total, section removal, and normalization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('rfqCriteriaForm');
            const totalDisplay = document.getElementById('totalWeightDisplay');
            const weightHint = document.getElementById('weightHint');
            const removedSectionsContainer = document.getElementById('removedSectionsContainer');
            const sectionTable = document.getElementById('sectionsTable');

            function calcTotal() {
                let total = 0;
                form.querySelectorAll('.section-weight-input').forEach(input => {
                    if (!input.closest('tbody').classList.contains('removed')) {
                        const v = parseFloat(input.value);
                        if (!isNaN(v)) total += v;
                    }
                });
                return total;
            }

            function refreshTotalUI() {
                const total = calcTotal();
                totalDisplay.textContent = total.toFixed(2) + '%';
                if (Math.abs(total - 100) < 0.001) {
                    totalDisplay.classList.remove('bg-danger', 'bg-warning');
                    totalDisplay.classList.add('bg-success');
                    weightHint.textContent = 'Total is valid (100.00%).';
                    weightHint.classList.remove('text-danger');
                    weightHint.classList.add('text-success');
                } else if (total > 100) {
                    totalDisplay.classList.remove('bg-success', 'bg-warning');
                    totalDisplay.classList.add('bg-danger');
                    weightHint.textContent = 'Total exceeds 100%. Adjust weights.';
                    weightHint.classList.add('text-danger');
                } else {
                    totalDisplay.classList.remove('bg-success', 'bg-danger');
                    totalDisplay.classList.add('bg-warning');
                    weightHint.textContent = 'Total less than 100%. Adjust or will be normalized.';
                    weightHint.classList.remove('text-danger');
                }
            }

            function addHiddenRemoved(sectionId) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'removed_sections[]';
                input.value = sectionId;
                removedSectionsContainer.appendChild(input);
            }

            // Delegate remove button clicks
            sectionTable.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-section-btn');
                if (!btn) return;
                const tbody = btn.closest('tbody');
                const sectionId = tbody.getAttribute('data-section-id');
                if (confirm('Remove this entire section and its criteria?')) {
                    tbody.classList.add('removed');
                    tbody.style.display = 'none';
                    addHiddenRemoved(sectionId);
                    refreshTotalUI();
                }
            });

            // Listen for weight input changes
            form.addEventListener('input', function (e) {
                if (e.target.classList.contains('section-weight-input')) {
                    refreshTotalUI();
                }
            });

            function normalizeWeights() {
                const inputs = Array.from(form.querySelectorAll('.section-weight-input')).filter(inp => !inp.closest('tbody').classList.contains('removed'));
                const currentTotal = inputs.reduce((sum, inp) => sum + (parseFloat(inp.value) || 0), 0);
                if (currentTotal <= 0) return; // avoid divide by zero
                const scale = 100 / currentTotal;
                let runningTotal = 0;
                inputs.forEach((inp, idx) => {
                    let newVal = (parseFloat(inp.value) || 0) * scale;
                    if (idx === inputs.length - 1) {
                        // Assign remainder to last to ensure exact 100.00 after rounding
                        newVal = 100 - runningTotal;
                    } else {
                        newVal = parseFloat(newVal.toFixed(2));
                        runningTotal += newVal;
                    }
                    inp.value = newVal.toFixed(2);
                });
            }

            form.addEventListener('submit', function (e) {
                const total = calcTotal();
                if (Math.abs(total - 100) < 0.001) {
                    // Already fine
                    return;
                }
                e.preventDefault();
                if (confirm('Current total is ' + total.toFixed(2) + '%. Normalize automatically to 100% and continue?')) {
                    normalizeWeights();
                    refreshTotalUI();
                    // Re-validate
                    const newTotal = calcTotal();
                    if (Math.abs(newTotal - 100) < 0.01) {
                        form.submit();
                    } else {
                        alert('Normalization failed to reach 100%. Please adjust manually.');
                    }
                } else {
                    alert('Please adjust weights to total 100%.');
                }
            });

            // Initial calc; if not 100 auto-normalize to avoid confusing totals like 200%
            refreshTotalUI();
            const initialTotal = calcTotal();
            if (Math.abs(initialTotal - 100) > 0.01) {
                normalizeWeights();
                refreshTotalUI();
                document.getElementById('clientNormalizedFlag').value = '1';
                // Optional subtle hint (avoid blocking alert)
                weightHint.textContent += ' (Auto-normalized from ' + initialTotal.toFixed(2) + '%)';
            }
        });
    </script>

@endsection
