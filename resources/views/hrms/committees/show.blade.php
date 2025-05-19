@extends('layouts.app')

@section('title', 'Committee Details')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">Committee Details</h4>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tr>
                        <th>Committee ID</th>
                        <td>{{ $committee->CommitteeID }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $committee->Name }}</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>{{ $committee->Type }}</td>
                    </tr>
                    <tr>
                        <th>Notes</th>
                        <td>{{ $committee->Notes }}</td>
                    </tr>
                    <tr>
                        <th>Created On</th>
                        <td>{{ \Carbon\Carbon::parse($committee->CreatedOn)->format('d-M-Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Created By</th>
                        <td>{{ $committee->CreatedBy }}</td>
                    </tr>
                </table>

                <div class="mt-4">
                    <a href="{{ route('committees.index') }}" class="btn btn-secondary">
                        ← Back to List
                    </a>
                    <a href="{{ route('committees.edit', $committee->CommitteeID) }}" class="btn btn-primary">
                        ✏️ Edit
                    </a>
                    <form action="{{ route('committees.destroy', $committee->CommitteeID) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this committee?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger">🗑️ Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
