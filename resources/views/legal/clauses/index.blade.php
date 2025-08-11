@extends('layouts.app')
@section('title', 'Clause Library')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>🧾 Clause / Template Library</h4>
        <a href="{{ route('legal.clauses.create') }}" class="btn btn-primary">➕ Add Clause</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Version</th>
                <th>Standard</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Quality Management Policy</td>
                <td>Policy</td>
                <td>1.0</td>
                <td>ISO 9001</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <tr>
                <td>Environmental Safety Manual</td>
                <td>Manual</td>
                <td>2.3</td>
                <td>ISO 14001</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <tr>
                <td>Information Security Procedure</td>
                <td>Procedure</td>
                <td>3.1</td>
                <td>ISO 27001</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <tr>
                <td>Workplace Safety Checklist</td>
                <td>Checklist</td>
                <td>1.5</td>
                <td>OSHA</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <tr>
                <td>Supplier Compliance Guidelines</td>
                <td>Guideline</td>
                <td>4.0</td>
                <td>ISO 45001</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>

            {{-- @foreach ($clauses as $clause)
                <tr>
                    <td>{{ $clause->Title }}</td>
                    <td>{{ $clause->ClauseType }}</td>
                    <td>{{ $clause->Version }}</td>
                    <td>{{ $clause->IsStandard ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('legal.clauses.edit', $clause->ID) }}" class="btn btn-sm btn-info">✏️ Edit</a>
                        <form action="{{ route('legal.clauses.destroy', $clause->ID) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Archive this clause?')">🗑️ Archive</button>
                        </form>
                    </td>
                </tr>
            @endforeach --}}
        </tbody>
    </table>
</div>
@endsection
