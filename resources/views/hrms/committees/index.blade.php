@extends('layouts.app')

@section('title', 'Committees')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Committee List</h4>
                <a href="{{ route('hrms.committees.create') }}" class="btn btn-success btn-sm">+ New Committee</a>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($committees->isEmpty())
                    <div class="alert alert-info">No committees found.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Committee ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Notes</th>
                                <th>Created On</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($committees as $index => $committee)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $committee->CommitteeID }}</td>
                                    <td>{{ $committee->Name }}</td>
                                    <td>{{ $committee->Type }}</td>
                                    <td>{{ $committee->Notes }}</td>
                                    <td>{{ \Carbon\Carbon::parse($committee->CreatedOn)->format('d-M-Y') }}</td>
                                    <td>{{ $committee->CreatedBy }}</td>
                                    <td>

                                        <a href="{{ route('hrms.committees.show', $committee->CommitteeID) }}" class="btn btn-sm btn-info">View</a>

                                        <a href="{{ route('hrms.committees.edit', $committee->CommitteeID) }}" class="btn btn-sm btn-primary">Edit</a>

                                        <form action="{{ route('hrms.committees.destroy', $committee->CommitteeID) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this committee?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
