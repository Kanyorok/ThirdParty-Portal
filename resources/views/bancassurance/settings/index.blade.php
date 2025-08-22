@extends('layouts.app')
@section('title', 'Settings Management')

@section('content')
<div class="container mt-4">
    <h4>⚙️ Settings Management</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Filter Dropdown -->
    <form method="GET" class="mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <label for="codeid" class="form-label">Filter by Setting Type</label>
                <select name="codeid" id="codeid" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Select Setting Type --</option>
                    @foreach($codeTypes as $type)
                        <option value="{{ $type }}" {{ request('codeid') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('bancassurance.settings.create') }}" class="btn btn-primary">➕ Add New</a>
            </div>
        </div>
    </form>

    @if($codeDetails->isEmpty())
        <p class="text-muted mt-3">No records found for selected type.</p>
    @else
        <table class="table table-bordered table-hover mt-3">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Display Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
<tbody>
    @foreach($codeDetails as $detail)
    <tr>
        <td>{{ $detail->ID }}</td>
        <td>{{ $detail->Description }}</td>
        <td>{{ $detail->DisplayOrder ?? '-' }}</td>
        <td>
            <span class="badge bg-{{ $detail->IsActive ? 'success' : 'secondary' }}">
                {{ $detail->IsActive ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td>
            <a href="{{ route('bancassurance.settings.edit', $detail->ID) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
        </td>
    </tr>
    @endforeach
</tbody>
        </table>
    @endif
</div>
@endsection
