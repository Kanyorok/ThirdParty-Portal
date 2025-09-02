@extends('layouts.app')
@section('title', 'Insurance Product Riders')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
    <h4>Riders & Add-ons</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

    <div class="mb-3 text-end">
        <a href="{{ route('bancassurance.riders.create') }}" class="btn btn-primary">Add Rider</a>
    </div>
        <table id='InsuranceProductRider'class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Provider</th>
                    <th>Product</th>
                    <th>Rider Name</th>
                    <th>Description</th>
                    <th>Additional Premium</th>
                    <th>Optional?</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($riders as $rider)
                <tr>
                    <td>{{ $rider->Id }}</td>
                    <td>{{ $rider->provider->Name ?? '-'}}</td>
                    <td>{{ $rider->product->Name ?? '-'}}</td>
                    <td>{{ $rider->RiderName ?? '-'}}</td>
                    <td>{{ $rider->Description ?? '-' }}</td>
                    <td>{{ number_format($rider->AdditionalPremium, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $rider->IsOptional ? 'info' : 'secondary' }}">
                            {{ $rider->IsOptional ? 'Yes' : 'No' }}
                        </span>
                        </td>
                        <td>
                        <span class="badge bg-{{ $rider->IsActive ? 'success' : 'danger' }}">
                            {{ $rider->IsActive ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                     <a href="{{ route('bancassurance.riders.edit', $rider->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('bancassurance.riders.destroy', $rider->Id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure you want to delete this Rider  ?');">Delete
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
        $('#InsuranceProductRider').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
