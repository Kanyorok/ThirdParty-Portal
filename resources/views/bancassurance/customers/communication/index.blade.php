@extends('layouts.app')
@section('title', 'Communication Log')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
    <a href="{{ route('bancassurance.customers.communication.create',) }}" class="btn btn-success mb-3">New Communication Log</a>

        <table id="Customercontacts" class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>Customer</th>
                <th>Date</th>
                <th>Type</th>
                <th>Summary</th>
                <th>Handled By</th>
                <th>Notes</th>
                <th>Actions</th>

            </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{$log->customers->thirdParty->ThirdPartyName}}</td>
                    <td>{{ \Carbon\Carbon::parse($log->ContactDate)->format('d/m/Y') }}</td>
                    <td>{{ $log->contacttypes->Description }}</td>
                    <td>{{ $log->Summary }}</td>
                    <td>{{ $log->employees->FirstName }}</td>
                    <td>{{ $log->Notes }}</td>
                    <td>
                        <a href="{{ route('bancassurance.customers.communication.edit', $log->Id) }}"
                           class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('bancassurance.customers.communication.destroy', $log->Id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this contact?');">Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
            @endforelse
            </tbody>
        </table>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#Customercontacts').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
