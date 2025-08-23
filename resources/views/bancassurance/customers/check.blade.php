@extends('layouts.app')
@section('title', 'Customer List')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Registered Customers</h4>
    <a href="{{ route('bancassurance.customers.create',) }}" class="btn btn-success mb-3">Register New Customer</a>
    <table id="customerregistry" class="table table-bordered table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>National ID</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Date of Birth</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($customers as $i => $customer)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $customer->FullName }}</td>
                    <td>{{ $customer->NationalID }}</td>
                    <td>{{ $customer->PhoneNumber }}</td>
                    <td>{{ $customer->Email }}</td>
                    <td>{{ $customer->DateOfBirth? \Carbon\Carbon::parse($customer->DateOfBirth)->format('d/m/Y') : '-'  }}</td>
                     <td>
                     
                    <a href="{{ route('bancassurance.customers.show', $customer->Id) }}" class="btn btn-sm btn-info">view</a>
                    <a href="{{ route('bancassurance.customers.edit', $customer->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('bancassurance.customers.destroy', $customer->Id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure you want to delete this Customer?');">Delete
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
            $('#customerregistry').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
