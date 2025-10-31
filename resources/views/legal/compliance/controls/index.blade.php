@extends('layouts.app')
@section('title','Compliance Controls')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">📂 Compliance Controls</h4>
            <a href="{{ route('legal.compliance.controls.create') }}" class="btn btn-sm btn-primary">➕ Add Control</a>
        </div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-bordered table-striped">
            <thead class="table-light">
            <tr>
                <th>Title</th>
                <th>Area</th>
                <th>Type</th>
                <th>Owner</th>
                <th>Status</th>
                <th style="width:120px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($controls as $control)
                <tr>
                    <td>{{ $control->Title }}</td>
                    <td>{{ $control->area->Name ?? '-' }}</td>
                    <td>{{ $control->controlType->Name ?? '-' }}</td>
                    <td>{{ \DB::table('t_Users')->where('Id',$control->OwnerID)->value('Name') }}</td>
                    <td>{{ $control->IsActive ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <a href="{{ route('legal.compliance.controls.show',$control->Id) }}"
                           class="btn btn-sm btn-info">🔍</a>
                        <a href="{{ route('legal.compliance.controls.edit',$control->Id) }}"
                           class="btn btn-sm btn-warning">✏️</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No controls found</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
