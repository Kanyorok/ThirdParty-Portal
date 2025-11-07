@extends('layouts.app')

@section('title', 'Item Sub Category')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Tender Types</h4>
        <a href="{{ route('tendertype.create') }}" class="btn btn-sm btn-success">+ New Type</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type Code</th>
                    <th>Tender Type</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenderTypes as $index => $type)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $type->TypeCode }}</td>
                    <td>{{ $type->TenderType }}</td>
                    <td>{{ $type->Description }}</td>
                    <td>
                        <a href="{{ route('tendertype.edit', $type->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('tendertype.destroy', $type->Id) }}" method="POST" class="d-inline">
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
                    <td colspan="5" class="text-center">No tender types found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
