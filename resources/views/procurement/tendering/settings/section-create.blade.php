@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Section & Criteria</h2>

    <form method="POST" action="{{ route('prequalification.sections.store') }}">
        @csrf

        <div class="mb-3">
            <label>Section Name</label>
            <input type="text" name="SectionName" class="form-control" value="{{ old('SectionName') }}" required>
            @error('SectionName') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="Description" class="form-control">{{ old('Description') }}</textarea>
        </div>

        <hr>
        <h4>Criteria</h4>
        <table class="table" id="criteria-table">
            <thead>
                <tr>
                    <th>Criteria Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th width="5%"></th>
                </tr>
            </thead>
            <tbody>
                <!-- Criteria rows will be added dynamically -->
            </tbody>
        </table>

        <button type="button" class="btn btn-secondary" id="add-criteria">Add Criteria</button>
        <hr>

        <button type="submit" class="btn btn-primary">Save Section & Criteria</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('add-criteria').addEventListener('click', function() {
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

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-criteria')) {
            e.target.closest('tr').remove();
        }
    });
</script>
@endpush