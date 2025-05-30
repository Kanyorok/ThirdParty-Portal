@extends('layouts.app')

@section('title', 'Committees')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@endsection

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
                        <table id="committeesTable" class="table table-bordered table-striped align-middle">
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

                                        <a href="{{ route('hrms.committees.show', $committee->CommitteeID) }}"
                                           class="btn btn-sm btn-primary">Edit</a>

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            @if(!$committees->isEmpty())
            $('#committeesTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                language: {
                    emptyTable: ""
                }
            });
            @endif
        });
    </script>
@endsection
