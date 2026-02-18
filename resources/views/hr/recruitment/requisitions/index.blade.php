@extends('layouts.app')

@section('title', 'Job Requisitions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Job Requisitions</h2>
        <a class="btn btn-primary" href="{{ route('hr.recruitment.requisitions.create') }}">+ New Requisition</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">All</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->Id }}" @selected(request('department_id') == $dept->Id)>{{ $dept->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->Id }}" @selected(request('branch_id') == $branch->Id)>{{ $branch->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach($statusList as $status)
                            <option value="{{ $status }}" @selected(request('status') == $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Department</th>
                            <th>Branch</th>
                            <th>Vacancies</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requisitions as $req)
                            <tr>
                                <td>{{ $req->Code }}</td>
                                <td>{{ $req->Title }}</td>
                                <td>{{ $req->department?->Name ?? '-' }}</td>
                                <td>{{ $req->branch?->Name ?? '-' }}</td>
                                <td>{{ $req->Vacancies }}</td>
                                <td>{{ $req->Status }}</td>
                                <td>{{ $req->RequestedOn ? \Carbon\Carbon::parse($req->RequestedOn)->format('Y-m-d') : '-' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.recruitment.requisitions.edit', $req->Id) }}">Edit</a>
                                    @if($req->Status === 'Approved')
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.recruitment.openings.create', ['requisition_id' => $req->Id]) }}">Create Opening</a>
                                        <form method="POST" action="{{ route('hr.recruitment.requisitions.close', $req->Id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Close</button>
                                        </form>
                                    @endif
                                    @if($req->Status === 'Submitted')
                                        <form method="POST" action="{{ route('hr.recruitment.requisitions.approve', $req->Id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('hr.recruitment.requisitions.reject', $req->Id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No requisitions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3">
        {{ $requisitions->links() }}
    </div>
</div>
@endsection
