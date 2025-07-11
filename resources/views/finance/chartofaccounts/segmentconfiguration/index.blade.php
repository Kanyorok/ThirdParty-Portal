@extends('layouts.app')
@section('title', 'COA Segments')
@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📋 COA Segments List</h4>

    <a href="{{ route('segments.create') }}" class="btn btn-primary mb-3">➕ Add New Segment</a>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Segment Type</th>
                <th>Segment Code</th>
                <th>Segment Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($segments as $index => $segment)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $segment->SegmentType }}</td>
                <td>{{ $segment->SegmentCode }}</td>
                <td>{{ $segment->SegmentName }}</td>
                <td>
                    @if($segment->IsActive)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('segment.edit', $segment->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <!-- Delete button can be added here if needed -->
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
