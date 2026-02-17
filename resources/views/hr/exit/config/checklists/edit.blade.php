@extends('layouts.app')

@section('title', 'Edit Checklist Template')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Checklist Template</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.exit-checklists.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form action="{{ route('hr.config.exit-checklists.update', $template->Id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $template->Name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description', $template->Description) }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" @checked(old('IsActive', $template->IsActive))>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.config.exit-checklists.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Template</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0">Checklist Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Department</th>
                            <th>Sequence</th>
                            <th>Mandatory</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->ItemName }}</td>
                                <td>{{ $item->clearanceDepartment?->Name ?? '-' }}</td>
                                <td>{{ $item->Sequence }}</td>
                                <td>{{ $item->IsMandatory ? 'Yes' : 'No' }}</td>
                                <td>
                                    <form action="{{ route('hr.config.exit-checklists.items.destroy', [$template->Id, $item->Id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No checklist items yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Add Checklist Item</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('hr.config.exit-checklists.items.store', $template->Id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Item Name *</label>
                        <input type="text" name="ItemName" class="form-control" value="{{ old('ItemName') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Clearance Department</label>
                        <select name="ClearanceDepartmentID" class="form-select">
                            <option value="">None</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->Id }}" @selected(old('ClearanceDepartmentID') == $dept->Id)>
                                    {{ $dept->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sequence</label>
                        <input type="number" name="Sequence" class="form-control" value="{{ old('Sequence', 1) }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsMandatory" value="1" @checked(old('IsMandatory', true))>
                            <label class="form-check-label">Mandatory</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
