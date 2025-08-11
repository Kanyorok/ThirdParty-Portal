@extends('layouts.app')
@section('title', 'Add Fleet Make/Brand')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container py-4">

    <!-- Fleet Make/Brand List Header + Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Fleet Brand List</h4>
        <a href="{{ route('fleetmake.create') }}" class="btn btn-primary">Add New</a>
    </div>

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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fleetMakes as $make)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $make->BrandID }}</td>
                            <td>{{ $make->BrandName }}</td>
                            <td>
                                <a href="{{ route('fleetmake.show', $make->Id) }}"
                                      class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('fleetmake.edit', $make->Id) }}"
                                      class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('fleetmake.destroy', $make->Id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this fleet make/brand?')">Delete</button>
                                </form>
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
            $(document).ready(function () {
                @if(!$fleetMakes->isEmpty())
                $('#fleetMakesTable').DataTable({
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
    @endsection