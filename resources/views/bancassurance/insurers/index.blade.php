@extends('layouts.app')
@section('title', 'Insurance Providers')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
    <div class="mb-3 text-end">
        <a href="{{ route('bancassurance.insurers.create') }}" class="btn btn-primary">Add Provider</a>
    </div>
        <table id='InsuranceProvider' class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Provider Number</th>
                    <th>Name</th>
                    <th>Country</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($providers as $provider)
                <tr>
                    <td>{{ $provider->Id }}</td>
                    <td>{{$provider->InsuranceProviderNO ?? '-'}}</td>
                    <td>{{ $provider->Name ?? '-'}}</td>
                    <td>{{ $provider->Country ?? '-' }}</td>
                    <td>{{ $provider->ContactPerson ?? '-' }}</td>
                    <td>{{ $provider->Email ?? '-' }}</td>
                    <td>{{ $provider->Phone ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $provider->IsActive ? 'success' : 'secondary' }}">
                            {{ $provider->IsActive ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('bancassurance.insurers.edit', $provider->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="{{ route('bancassurance.insurers.products', $provider->Id) }}" class="btn btn-sm btn-info">View Products</a>
                    <form action="{{ route('bancassurance.insurers.destroy', $provider->Id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure you want to delete this Insurance Provider?');">Delete
                        </button>
                    </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#InsuranceProvider').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
