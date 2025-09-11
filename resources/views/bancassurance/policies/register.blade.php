@extends('layouts.app')
@section('title', 'Policy Register')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <h4>Issued Policies Register</h4>

<table id='register' class="table table-bordered mt-3">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Policy Number</th>
            <th>Customer</th>
            <th>Insurer</th>
            <th>Status</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($policies as $policy)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $policy->PolicyNumber ?? '-' }}</td>
            <td>{{ $policy->customer->FullName ?? '-'  }}</td>
            <td>{{ $policy->insurer->Name ?? '-'  }}</td>
            <td><span class="badge bg-success">{{ $policy->Status->Label() }}</span></td>
            <td>{{ \Carbon\Carbon::parse($policy->PolicyStartDate)->format('d/m/Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d/m/Y') }}</td>
            <td><a href="{{ route('bancassurance.policies.show', $policy->Id) }}" class="btn btn-sm btn-outline-info">View</a></td>
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
        $('#register').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
