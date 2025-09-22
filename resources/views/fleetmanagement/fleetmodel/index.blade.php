@extends('layouts.app')
@section('title', 'Add Fleet Model')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="container py-4">

        <!-- Fleet Make/Brand List Header + Add Button -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Fleet Model List</h4>
            <a href="{{ route('fleetmodel.create') }}" class="btn btn-primary">Add New</a>
        </div>

        <!-- Fleet Model Table -->
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table id="fleetModelsTable" class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Model ID</th>
                            <th>Fleet Model</th>
                            <th>Fleet Make/Brand</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($fleetModels as $model)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $model->ModelID }}</td>
                                <td>{{ $model->ModelName }}</td>
                                <td>{{ $model->brand->BrandName ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('fleetmodel.show', $model->Id) }}"
                                       class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('fleetmodel.edit', $model->Id) }}"
                                       class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('fleetmodel.destroy', $model->Id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this fleet model?')">
                                            Delete
                                        </button>
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
                @if(!$fleetModels->isEmpty())
                $('#fleetModelsTable').DataTable({
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
