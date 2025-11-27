@extends('layouts.app')

@section('title', 'Add Fleet Model')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container py-4">

    <!-- Fleet Model List Header + Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Fleet Model List</h4>
        <a href="{{ route('fleetmodel.create') }}" class="btn btn-primary">Add New</a>
    </div>
    <p>
        <i class="fas fa-info-circle"></i>
        <span class="text-info" data-bs-toggle="tooltip" title="This indicates how many vehicles are associated with each fleet brand."></span>
        <i>Help Notes: The Usage Column shows the number of vehicles under the car model</i>
    </p>
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
                            <th>Usage</th>
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

                            {{-- ✅ Show usage count --}}
                            <td>
                                @if($model->vehicles_count > 0)
                                <span class="badge bg-info text-dark">
                                    In Use ({{ $model->vehicles_count }})
                                </span>
                                @else
                                <span class="badge bg-secondary">Not In Use</span>
                                @endif
                            </td>

                            {{-- ✅ Actions --}}
                            <td>
                                <a href="{{ route('fleetmodel.show', $model->Id) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('fleetmodel.edit', $model->Id) }}" class="btn btn-sm btn-warning">Edit</a>

                                @if($model->vehicles_count > 0)
                                {{-- 🔒 Locked delete button --}}
                                <button class="btn btn-sm btn-danger" disabled
                                    title="Cannot delete — this model is in use by {{ $model->vehicles_count }} vehicle(s)">
                                    Delete
                                </button>
                                @else
                                {{-- 🗑️ Allow delete if not in use --}}
                                <form action="{{ route('fleetmodel.destroy', $model->Id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this fleet model?')">
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