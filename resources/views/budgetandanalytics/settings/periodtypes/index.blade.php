@extends('layouts.app')
@section('title', 'Budget Period Types')
@section('content')

    <div class="card p-4">
        <h5>📆 Budget Period Types</h5>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class='mb-3 text-end'>
            <a href="{{ route('periodtypes.create') }}" class="btn btn-sm btn-primary ">➕ Add New Period Type</a>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Period Type</th>
                <th>Code</th>
                <th>Active?</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($types as $index => $type)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $type->PeriodType }}</td>
                    <td>{{ $type->Code }}</td>
                    <td>{{ $type->IsActive ? '✅ Yes' : '❌ No' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{route('periodtypes.edit', $type->Id)}}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{route('periodtypes.destroy', $type->Id)}}" method="POST"
                                  style="display: inline-flexbox"
                                  onsubmit="return confirm('Are you sure you want to delete this Period Type?');">
                                @method('DELETE')
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No budget period types found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

@endsection
