@extends('layouts.app')
@section('title', 'Tender Criteria')
@section('content')

<div class="container mt-4">
    <h4 class="mb-3">📑 {{$tender->TenderNo}} Criteria Form</h4>

    <form action="{{route('tender-criteria.store')}}" method="POST" enctype="multipart/form-data" id="sectionCriteriaForm">
    @csrf
    @method('POST')

    <input type="hidden" name="TenderId" value="{{ $TenderId }}">

    <table class="table">
        @foreach ($tenderSections as $section)
            <tr class="table-secondary section-row">
                <td class="fw-bold" colspan="2">{{ $section->sections->SectionName }}</td>
                <td>
                    <input type="number"
                           class="form-control"
                           name="weights[{{ $section->sections->id }}]"
                           value="{{ number_format($section->Weight, 2) }}"
                           step="0.01" min="0" max="100" required>
                           
                        <!-- Hidden field to actually submit the value -->
                        <input type="hidden"name="weights[{{ $section->sections->id }}]" value="{{ number_format($section->Weight, 2) }}">
                </td>
            </tr>

            @foreach ($section->criteria as $criteria)
                <tr class="criteria-row">
                    <td>
                        <input type="checkbox"
                               name="criterias[{{ $section->sections->id }}][]"
                               value="{{ $criteria->id }}"
                               {{ $criteria->isChecked ? 'checked' : '' }}>
                    </td>
                    <td colspan="2">{{ $criteria->CriteriaName }}</td>
                </tr>
            @endforeach
        @endforeach
    </table>

   <a href="{{route('tenderevaluations.index')}}"><button type="button" class="btn btn-secondary">Back</button></a> 
    <button type="submit" class="btn btn-primary">Save Criteria</button>
</form>
</div>

<!-- Inline JavaScript to enforce 100% weight -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('sectionCriteriaForm');
    const totalWeightDisplay = document.getElementById('totalWeight');
    const submitBtn = document.getElementById('saveCriteriaBtn');

    function updateTotal() {
        let total = 0;
        const rows = form.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const checkbox = row.querySelector('input[type="checkbox"]');
            const weightInput = row.querySelector('input[type="number"]');

            if (checkbox && checkbox.checked && weightInput) {
                weightInput.disabled = false;
                total += parseFloat(weightInput.value) || 0;
            } else if (weightInput) {
                weightInput.disabled = true;
            }
        });

        totalWeightDisplay.textContent = total.toFixed(2);
        return total;
    }

    // Attach listeners
    form.querySelectorAll('tbody tr').forEach(row => {
        const checkbox = row.querySelector('input[type="checkbox"]');
        const weightInput = row.querySelector('input[type="number"]');

        if (checkbox) checkbox.addEventListener('change', updateTotal);
        if (weightInput) weightInput.addEventListener('input', updateTotal);
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
