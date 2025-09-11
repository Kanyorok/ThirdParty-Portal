@extends('layouts.app')
@section('title', 'Customer List')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
    <table id="Customerregistry" class="table table-bordered table-hover">
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
                    <td>{{ $customer->FullName ?? '-'}}</td>
                    <td>{{ $customer->NationalID ?? '-'}}</td>
                    <td>{{ $customer->PhoneNumber ?? '-'}}</td>
                    <td>{{ $customer->Email }}</td>
                    <td>{{ $customer->DateOfBirth ? \Carbon\Carbon::parse($customer->DateOfBirth)->format('d/m/Y') : '-'  }}</td>
                    <td>
                        <a href="{{ route('bancassurance.customers.portfolio', $customer->Id) }}" class="btn btn-sm btn-info">View Portfolio</a> 
                        <a href="{{ route('bancassurance.customers.communication.index') }}" class="btn btn-sm btn-secondary">Add Communication</a>
                        <a href="{{ route('bancassurance.customers.beneficiaries.create') }}" class="btn btn-sm btn-primary">Add Beneficiary</a>
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
            $('#Customerregistry').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
