@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="container-fluid py-4">
    {{-- Page Title / Breadcrumb --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Employees</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Employees</li>
            </ol>
        </nav>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <div class="d-flex align-items-center">
                <span class="me-2">👥</span>
                <h5 class="mb-0">Employee List</h5>
            </div>
            <a href="{{ route('hr.employees.create') }}" class="btn btn-primary rounded-pill">
                + New Employee
            </a>
        </div>

        <div class="card-body">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Filters --}}
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="Emp. No, Name, Email">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->Id }}"
                                @selected(request('branch_id') == $branch->Id)>
                                {{ $branch->Name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept->Id }}"
                                @selected(request('department_id') == $dept->Id)>
                                {{ $dept->Name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statusList as $status)
                            <option value="{{ $status }}" @selected(request('status') == $status)>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Per Page</label>
                    <select name="per_page" class="form-select">
                        @foreach([10,25,50,100] as $size)
                            <option value="{{ $size }}" @selected(request('per_page',20) == $size)>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        Apply Filters
                    </button>
                    <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary flex-fill">
                        Reset
                    </a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                @if($employees->count())
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Employee No</th>
                            <th>Name</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Grade</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($employees as $index => $emp)
                            <tr>
                                <td>{{ $employees->firstItem() + $index }}</td>
                                <td>{{ $emp->EmployeeNo }}</td>
                                <td>{{ $emp->FirstName }} {{ $emp->LastName }}</td>
                                <td>{{ $emp->branch->Name ?? '-' }}</td>
                                <td>{{ $emp->department->Name ?? '-' }}</td>
                                <td>{{ $emp->grade->Name ?? '-' }}</td>
                                <td>{{ $emp->role->Name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $emp->Status === 'Active' ? 'success' : 'secondary' }}">
                                        {{ $emp->Status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('hr.employees.show', $emp->Id) }}"
                                       class="btn btn-sm btn-outline-secondary">View</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $employees->withQueryString()->links() }}
                    </div>
                @else
                    <div class="py-5 text-center text-muted">
                        <div class="mb-2 fs-1">ℹ️</div>
                        <p class="mb-3">No employees have been registered yet.</p>
                        <a href="{{ route('hr.employees.create') }}" class="btn btn-primary rounded-pill">
                            + Add Employee
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
