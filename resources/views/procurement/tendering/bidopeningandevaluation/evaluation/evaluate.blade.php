@extends('layouts.app')
@section('title', 'Tender Criteria')
@section('content')

<div class="container mt-4">
    <h4 class="mb-3">📑 {{ $tender->TenderNo }} For Supplier: {{$supplier}}</h4>

    <form action="{{ route('store-criteria-scores') }}" method="POST" enctype="multipart/form-data" id="sectionCriteriaForm">
        @csrf
        <input type="hidden" name="TenderId" value="{{ $TenderId }}">

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Section / Criteria</th>
                    <th>Weight / Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tenderSections as $section)
                    <!-- Section Row -->
                    <tr class="table-secondary section-row">
                        <td class="fw-bold">{{ $section->sections->SectionName }}</td>
                        <td>
                            <input type="number"
                                   class="form-control" readonly
                                   name="weights[{{ $section->sections->id }}]"
                                   value="{{ number_format($section->Weight, 2) }}"
                                   step="0.01" min="0" max="100" required>
                            <input type="hidden"
                                   class="form-control"
                                   name="weights[{{ $section->sections->id }}]"
                                   value="{{ number_format($section->Weight, 2) }}"
                                   step="0.01" min="0" max="100" required>
                        </td>
                    </tr>

                    <!-- Criteria Rows -->
                    @foreach ($section->criteria as $criteria)
                        @php
                            $checked = $criteria->isChecked ?? false;
                        @endphp
                        <tr class="criteria-row" style="display:{{ $checked ? 'table-row' : 'none' }};">
                            <td>
                                <div class="form-check">
                                    <input 
                                        type="hidden"
                                        name="selected_criteria[]"
                                        value="{{ $criteria->id }}"
                                        id="criteriaCheck{{ $criteria->id }}"
                                        {{ $checked ? 'checked' : '' }}>
                                    <label class="form-check-label" for="criteriaCheck{{ $criteria->id }}">
                                        {{ $criteria->CriteriaName }}
                                    </label>
                                </div>
                            </td>
                            <td>
                                <input type="number"
                                    class="form-control"
                                    name="scores[{{ $criteria->id }}]"
                                    value="0"
                                    step="1"
                                    min="0"
                                    max="10"
                                    id="scoreInput{{ $criteria->id }}">
                                <input type="hidden"
                                    name="section_ids[{{ $criteria->id }}]"
                                    value="{{ $section->sections->id }}">
                            </td>
                        </tr>
                    @endforeach

                @endforeach
            </tbody>
        </table>

        <div class="mt-3 d-flex justify-content-between">
            <a href="{{ route('tenderevaluations.index') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary" id="saveCriteriaBtn">Save Criteria</button>
        </div>
    </form>
</div>


@endsection
