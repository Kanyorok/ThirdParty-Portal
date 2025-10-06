@extends('layouts.app')

@section('title', 'Insurance Providers')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">

    <!-- Header Action Button -->
    <div class="mb-3 text-end">
        <a href="{{ route('bancassurance.insurers.create') }}" class="btn btn-primary">
            Add Provider
        </a>
    </div>

    <!-- Info Note -->
    <p>
        <small>The list below is of the available insurance providers</small>
    </p>

    <!-- Providers Table -->
    <table id="InsuranceProvider" class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Provider Number</th>
                <th>Name</th>
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
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $provider->InsuranceProviderNO ?? '-' }}</td>
                    <td>{{ $provider->Name ?? '-' }}</td>
                    <td>{{ $provider->ContactPerson ?? '-' }}</td>
                    <td>{{ $provider->Email ?? '-' }}</td>
                    <td>{{ $provider->Phone ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $provider->IsActive ? 'success' : 'secondary' }}">
                            {{ $provider->IsActive ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('bancassurance.insurers.products', $provider->Id) }}" 
                           class="btn btn-sm btn-info">
                            View Products
                        </a>

                        <a href="{{ route('bancassurance.insurers.edit', $provider->Id) }}" 
                           class="btn btn-sm btn-warning">
                            Edit
                        </a>

                        @if($provider->getProductByProvider()->exists())
                            <button class="btn btn-sm btn-secondary" disabled>
                                <i class="bi bi-lock"></i> In Use
                            </button>
                        @else
                            <form action="{{ route('bancassurance.insurers.destroy', $provider->Id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this Provider?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

<!-- Scripts -->
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
