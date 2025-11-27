@extends('layouts.app')

@section('title', 'Fleet Makes / Brands')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Fleet Brand List</h4>
        <a href="{{ route('fleetmake.create') }}" class="btn btn-primary">Add New</a>

    </div>
    <p>
        <i class="fas fa-info-circle"></i>
        <span class="text-info" data-bs-toggle="tooltip" title="This indicates how many vehicles are associated with each fleet brand."></span>
        <i>Help Notes: The Usage Column shows the number of vehicles under the car make</i>
    </p>

    <!-- Fleet Brand Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="fleetMakesTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Brand ID</th>
                            <th>Fleet Brand</th>
                            <th>Usage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fleetMakes as $make)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $make->BrandID }}</td>
                            <td>{{ $make->BrandName }}</td>

                            {{-- ✅ Show usage count --}}
                            <td>
                                @if($make->vehicles_count > 0)
                                <span class="badge bg-info text-dark">
                                    In Use ({{ $make->vehicles_count }})
                                </span>
                                @else
                                <span class="badge bg-secondary">Not In Use</span>
                                @endif
                            </td>

                            {{-- ✅ Actions --}}
                            <td>
                                <a href="{{ route('fleetmake.show', $make->Id) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('fleetmake.edit', $make->Id) }}" class="btn btn-sm btn-warning">Edit</a>

                                @if($make->vehicles_count > 0)
                                {{-- 🔒 Locked delete button --}}
                                <button class="btn btn-sm btn-danger" disabled
                                    title="Cannot delete — this brand is in use by {{ $make->vehicles_count }} vehicle(s)">
                                    Delete
                                </button>
                                @else
                                {{-- 🗑️ Allow delete if not in use --}}
                                <form action="{{ route('fleetmake.destroy', $make->Id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this fleet make/brand?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        @if(!$fleetMakes->isEmpty())
        $('#fleetMakesTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                emptyTable: "No fleet brands found"
            }
        });
        @endif
    });
</script>
@endsection
@endsection