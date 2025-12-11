@extends('layouts.app')

@section('title', 'KPI Library')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">KPI Library</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.kpi.library.create') }}">+ New KPI</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Default Weight</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->Code }}</td>
                                <td>{{ $item->Name }}</td>
                                <td>{{ $item->Category }}</td>
                                <td>{{ $item->Unit }}</td>
                                <td>{{ $item->DefaultWeight }}</td>
                                <td>{{ $item->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.kpi.library.edit', $item->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.config.kpi.library.destroy', $item->Id) }}" method="POST" onsubmit="return confirm('Deactivate this KPI?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No KPI items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
