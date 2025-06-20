@extends('layouts.app')

@section('title', 'Budget Planning Methods')

@section('content')
    <div class="card p-4">
        <div class="card-header bg-dark text-white mb-0">
            📊 Budget Planning Methods
        </div>


    @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card-body mb-0">
            <p class="text-muted">Planning methods are used to define how budgets are created and managed within the
                system. Each method can have a unique name, description, and active status.</p>

            <div class="mb-3 text-end">
                <a href="{{ route('planningmethods.create') }}" class="btn btn-sm btn-primary">➕ Add New</a>
            </div>

        <table class="table table-bordered table-striped">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Method Name</th>
                <th>Description</th>
                <th>Is Active?</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($methods as $index => $method)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $method->MethodName }}</td>
                    <td>{{ $method->Description ?? '—' }}</td>
                    <td>{{ $method->IsActive ? 'Yes' : 'No' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{route('planningmethods.edit', $method->Id)}}"
                               class="btn btn-sm btn-warning">Edit</a>

                            <form action="{{route('planningmethods.destroy', $method->Id)}}" method="POST"
                                  style="display: inline-flexbox"
                                  onsubmit="return confirm('Are you sure you want to delete this Planning Method?');">
                                @method('DELETE')
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No budget planning methods found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
