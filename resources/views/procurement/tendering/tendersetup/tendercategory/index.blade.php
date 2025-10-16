@extends('layouts.app')

@section('title', 'Tender Category')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Tender Categories</h4>
        <a href="{{ route('tendercategory.create') }}" class="btn btn-sm btn-success">+ New Category</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>CategoryCode</th>
                    <th>Tender Category</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenderCategories as $index => $type)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $type->CategoryCode }}</td>
                    <td>{{ $type->TenderCategory }}</td>
                    <td>{{ $type->Description }}</td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('tendercategory.edit', $type->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="{{ route('tendercategory.itemtypes', $type->Id) }}" class="btn btn-sm btn-outline-primary">Map Item Types</a>
                        <form action="{{ route('tendercategory.destroy', $type->Id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No categories found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
