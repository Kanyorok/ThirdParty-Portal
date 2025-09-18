@extends('layouts.app')
@section('title', 'RFQ Criteria')
@section('content')

    <div class="container mt-4">
        <h4 class="mb-3">📑 {{ $rfq->RFQNumber }} Criteria Form</h4>

        <form action="{{ route('rfqcriterias.store') }}" method="POST" enctype="multipart/form-data"
              id="rfqCriteriaForm">
            @csrf

            <input type="hidden" name="rfq_id" value="{{ $rfq->Id }}">

            <table class="table">
                @foreach ($rfqSections as $section)
                    <tr class="table-secondary section-row">
                        <td class="fw-bold" colspan="2">{{ $section->section->SectionName }}</td>
                        <td>
                            <input type="number"
                                   class="form-control"
                                   name="weights[{{ $section->section->Id }}]"
                                   value="{{ number_format($section->Weight, 2) }}"
                                   step="0.01" min="0" max="100" required>
                        </td>
                    </tr>

                    @foreach ($section->section->criteria as $criteria)
                        <tr class="criteria-row">
                            <td>
                                <input type="checkbox"
                                       name="criterias[{{ $section->section->Id }}][]"
                                       value="{{ $criteria->Id }}"
                                    {{ $criteria->isChecked ? 'checked' : '' }}>
                            </td>
                            <td colspan="2">{{ $criteria->CriteriaName }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </table>

            <a href="{{ route('rfqcriteriasetup.evaluations') }}">
                <button type="button" class="btn btn-secondary">Back</button>
            </a>
            <button type="submit" class="btn btn-primary">Save Criteria</button>
        </form>
    </div>

    <!-- Inline JavaScript to enforce 100% weight -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('rfqCriteriaForm');

            function updateTotal() {
                let total = 0;
                form.querySelectorAll('input[type="number"]').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                return total;
            }

            form.addEventListener('submit', function (e) {
                const total = updateTotal();
                if (total.toFixed(2) !== '100.00') {
                    e.preventDefault();
                    alert('Total weight must be exactly 100.00%. Current total: ' + total.toFixed(2) + '%');
                }
            });
        });
    </script>

@endsection
