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
            @foreach ($clauses as $clause)
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
            @endforeach
        </tbody>
    </table>
</div>
@endsection
