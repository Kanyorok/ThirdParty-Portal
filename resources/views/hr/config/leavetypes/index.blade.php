@extends('layouts.app')

@section('title', 'Leave Types')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Leave Types</h2>
        <a class="btn btn-primary" href="{{ route('hr.config.leavetypes.create') }}">+ New Leave Type</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form class="row g-3 mb-3" method="GET">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Any</option>
                        @foreach(['Pending','Approved','Rejected'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Annual Days</th>
                            <th>Carry Forward</th>
                            <th>Requires Attachment</th>
                            <th>Paid</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                            <tr>
                                <td>{{ $type->Code }}</td>
                                <td>{{ $type->Name }}</td>
                                <td>{{ $type->AnnualEntitlementDays }}</td>
                                <td>{{ $type->AllowCarryForward ? 'Yes' : 'No' }}</td>
                                <td>{{ $type->RequiresAttachment ? 'Yes' : 'No' }}</td>
                                <td>{{ $type->IsPaid ? 'Yes' : 'No' }}</td>
                                <td>{{ $type->Status }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.config.leavetypes.edit', $type->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.config.leavetypes.destroy', $type->Id) }}" method="POST" onsubmit="return confirm('Deactivate this leave type?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                    </form>
                                    @if($type->Status !== 'Approved')
                                        <form class="d-inline" action="{{ route('hr.config.leavetypes.approve', $type->Id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit">Approve</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center">No leave types found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $types->links() }}
        </div>
    </div>
</div>
@endsection
