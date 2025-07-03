@extends('layouts.app')
@section('title', 'Setup Evaluation Structure')
@section('content')

<div class="card">
    <div class="card-header bg-primary text-white">Evaluation Sections & Criteria Setup</div>
    <div class="card-body">

        <form id="setupForm" method="post" action="{{ route('preqcriteria.store') }}">
            @csrf

            <div class="mb-3">
                <label for="round_id" class="form-label">Prequalification Round</label>
                <select id="round_id" name="round_id" class="form-select">
                    <option selected disabled>Select Round</option>
                    @foreach($rounds as $round)
                        <option value="{{ $round->Id }}">{{ $round->Title }}</option>
                    @endforeach
                </select>
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Select</th>
                        <th>Evaluation Section</th>
                        <th>Section Weight%</th>
                        <th>Criteria List</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sections as $section)
                        <tr>
                            <td>
                                <input type="checkbox" name="sections[]" value="{{ $section->id }}" class="form-check-input">
                            </td>
                            <td>{{ $section->SectionName }}</td>
                            <td>
                                <input type="number" name="weights[{{ $section->id }}]" class="form-control">
                            <td>
                                @if ($section->criteria->count() > 0)
                                    <ul class="mb-0">
                                        @foreach ($section->criteria as $criteria)
                                            <li>{{ $criteria->CriteriaName }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <a href="{{ route('sections.index', ['section_id' => $section->id]) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        ⚙️ Setup Criteria on Section
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="text-end">
                <button type="submit" class="btn btn-success">Save Sections</button>
            </div>
        </form>

    </div>
</div>


@section('scripts')
<script>
document.getElementById('setupForm').addEventListener('submit', function (e) {
    let totalWeight = 0;
    const checkboxes = document.querySelectorAll('input[name="sections[]"]:checked');

    checkboxes.forEach(function (checkbox) {
        const sectionId = checkbox.value;
        const weightInput = document.querySelector(`input[name="weights[${sectionId}]"]`);
        const weight = parseFloat(weightInput.value) || 0;
        totalWeight += weight;
    });

    if (totalWeight !== 100) {
        e.preventDefault();
        alert('The total weight of selected sections must equal 100%. Currently it adds up to ' + totalWeight + '%.');
    }
});
</script>
@endsection

@endsection
