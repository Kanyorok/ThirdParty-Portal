@extends('layouts.app')
@section('title', 'Customer List')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">👥 Registered Customers</h4>

    <!-- 🔍 Search Filter Form -->
    <form method="GET" action="{{ route('bancassurance.customers.index') }}" class="row mb-3">
        <div class="col-md-3">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, ID or phone">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary" type="submit">
                🔍 Filter
            </button>
        </div>
    </form>

    <!-- ✅ Feedback -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- 📋 Customer Table -->
    <table class="table table-bordered table-hover">
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
                    <td>{{ $customer->Phone }}</td>
                    <td>{{ $customer->Email }}</td>
                    <td>{{ $customer->DateOfBirth }}</td>
                    <td>
                        <a href="{{ route('bancassurance.customers.portfolio', $customer->Id) }}" class="btn btn-sm btn-info">📄 View Portfolio</a>
                        <a href="{{ route('bancassurance.customers.communication.index', $customer->Id) }}" class="btn btn-sm btn-secondary">
    🕓 Communication History
</a>
                        <a href="{{ route('bancassurance.customers.beneficiaries.create', $customer->Id) }}" class="btn btn-sm btn-primary">➕ Add Beneficiary</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No customers found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
