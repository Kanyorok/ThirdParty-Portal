@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Section & Criteria</h2>

        <form method="POST" action="{{ route('prequalification.sections.update', $section) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Section Name</label>
                <input type="text" name="SectionName" class="form-control"
                       value="{{ old('SectionName', $section->SectionName) }}" required>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="Description"
                          class="form-control">{{ old('Description', $section->Description) }}</textarea>
            </div>

            <div class="mb-3">
                <label>Status</label>
                <select name="IsActive" class="form-select">
                    <option value="1" {{ old('IsActive', $section->IsActive) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('IsActive', $section->IsActive) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <hr>
            <h4>Criteria</h4>
            <table class="table" id="criteria-table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th width="5%"></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($section->criteria as $i => $criteria)
                    <tr>
                        <td>
                            <input type="hidden" name="criteria[{{ $i }}][Id]" value="{{ $criteria->Id }}">
                            <input type="text" name="criteria[{{ $i }}][CriteriaName]" class="form-control"
                                   value="{{ old("criteria.$i.CriteriaName", $criteria->CriteriaName) }}" required>
                        </td>
                        <td>
                            <input type="text" name="criteria[{{ $i }}][Description]" class="form-control"
                                   value="{{ old("criteria.$i.Description", $criteria->Description) }}">
                        </td>
                        <td>
                            <select name="criteria[{{ $i }}][IsActive]" class="form-select">
                                <option
                                    value="1" {{ old("criteria.$i.IsActive", $criteria->IsActive) == 1 ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option
                                    value="0" {{ old("criteria.$i.IsActive", $criteria->IsActive) == 0 ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-existing"
                                    data-id="{{ $criteria->Id }}">X
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <button type="button" class="btn btn-secondary" id="add-criteria">Add Criteria</button>
            <hr>

            <input type="hidden" name="criteria_remove" id="criteria_remove" value="">

            <button type="submit" class="btn btn-primary">Update Section & Criteria</button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('add-criteria').addEventListener('click', function () {
            const tableBody = document.querySelector('#criteria-table tbody');
            const index = tableBody.children.length;
            const row = document.createElement('tr');

            row.innerHTML = `
            <td>
                <input type="text" name="criteria[${index}][CriteriaName]" class="form-control" required>
            </td>
            <td>
                <input type="text" name="criteria[${index}][Description]" class="form-control">
            </td>
            <td>
                <select name="criteria[${index}][IsActive]" class="form-select">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-criteria">X</button>
            </td>
        `;

            tableBody.appendChild(row);
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-criteria')) {
                e.target.closest('tr').remove();
            }

            if (e.target.classList.contains('remove-existing')) {
                const id = e.target.dataset.id;
                let removeField = document.getElementById('criteria_remove');
                let ids = removeField.value ? removeField.value.split(',') : [];
                ids.push(id);
                removeField.value = ids.join(',');
                e.target.closest('tr').remove();
            }
        });
    </script>
@endpush
