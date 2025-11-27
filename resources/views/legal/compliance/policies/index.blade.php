@extends('layouts.app')
@section('title','Policies')

@section('content')
<div class="card shadow rounded-4 p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>📘 Compliance Policies</h4>
        <a href="{{ route('legal.compliance.policies.create') }}" class="btn btn-sm btn-primary">➕ Add Policy</a>
    </div>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <table class="table table-bordered">
        <thead>
            <tr><th>Title</th><th>Category</th><th>Effective Date</th><th>Version</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @foreach($policies as $p)
            <tr>
                <td>{{ $p->Title }}</td>
                <td>{{ $p->CategoryID }}</td>
                <td>{{ $p->EffectiveDate }}</td>
                <td>{{ $p->Version }}</td>
                <td>{{ $p->IsActive ? 'Active':'Retired' }}</td>
                <td>
                    <a href="{{ route('legal.compliance.policies.show',$p->Id) }}" class="btn btn-sm btn-info">🔍 View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
