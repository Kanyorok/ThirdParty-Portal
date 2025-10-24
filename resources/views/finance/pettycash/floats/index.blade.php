@extends('layouts.app')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Petty Cash Floats</h4>
        <a href="{{ route('finance.pettyfloats.create') }}" class="btn btn-primary">New Float</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Code</th>
                <th>Name</th>
                <th>Currency</th>
                <th>Limit</th>
                <th>Reorder</th>
                <th>Active</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($rows as $r)
                <tr>
                    <td>{{ $r->FloatID }}</td>
                    <td>{{ $r->Code }}</td>
                    <td>{{ $r->Name }}</td>
                    <td>{{ $r->currency?->Code }}</td>
                    <td>{{ number_format($r->FloatLimit,2) }}</td>
                    <td>{{ number_format($r->ReorderLevel,2) }}</td>
                    <td><span
                            class="badge bg-{{ $r->IsActive?'success':'secondary' }}">{{ $r->IsActive?'Yes':'No' }}</span>
                    </td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary"
                           href="{{ route('finance.pettyfloats.edit',$r->FloatID) }}">Edit</a>
                        <form class="d-inline" method="POST"
                              action="{{ route('finance.pettyfloats.destroy',$r->FloatID) }}"
                              onsubmit="return confirm('Delete this float?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">No floats yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $rows->links() }}
@endsection
