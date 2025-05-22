@extends('layouts.app')

@section('title', 'Committee Details')

@section('content')
    <div class="container mt-4">
    
        <div class="card shadow-sm">
            
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">Committee Details</h4>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tr>
                        <th>Committee ID</th>
                        <td>{{ $committee->CommitteeID }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $committee->Name }}</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>{{ $committee->Type }}</td>
                    </tr>
                    <tr>
                        <th>Notes</th>
                        <td>{{ $committee->Notes }}</td>
                    </tr>
                    <tr>
                        <th>Created On</th>
                        <td>{{ \Carbon\Carbon::parse($committee->CreatedOn)->format('d-M-Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Created By</th>
                        <td>{{ $committee->CreatedBy }}</td>
                    </tr>
                </table>

                <div class="mt-4">
                    <a href="{{ route('hrms.committees.index') }}" class="btn btn-secondary">
                        ← Back to List
                    </a>
                    <a href="{{ route('hrms.committees.edit', $committee->CommitteeID) }}" class="btn btn-primary">
                        ✏️ Edit
                    </a>
                    <form action="{{ route('hrms.committees.destroy', $committee->CommitteeID) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this committee?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger">🗑️ Delete</button>
                    </form>

                    <div class="card-actions float-end">
                        <button class="btn btn-primary ms-2 click-summary-data" type="button"
                                data-click_url="{{ route('employeescommittee.create') }}"
                                data-summary_title="Add a User">
                            <i class="fas fa-plus-circle"></i> Appoint a Member
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($committee->employees && $committee->employees->count())
    <h5 class="mb-3">Committee Members</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Appointed On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($committee->employees as $index => $employee)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $employee->full_name }}</td>
                        <td>{{ $employee->EmployeeID }}</td>
                        <td>{{ $employee->Email }}</td>
                        <td>{{ $employee->Phone }}</td>
                        <td>
                            {{ optional($employee->pivot)->CreatedOn 
                                ? \Carbon\Carbon::parse($employee->pivot->CreatedOn)->format('d-M-Y H:i') 
                                : '—' }}
                        </td>
                        <td>
                            <form action="{{ route('employeescommittee.remove') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="committee_id" value="{{ $committee->Id }}">
                                <input type="hidden" name="employee_id" value="{{ $employee->Id }}">
                                <button type="submit" class="btn btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="text-muted mt-4">No members have been appointed to this committee yet.</p>
@endif

@endsection
