@extends('layouts.app')

@section('title', 'Redundancies')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Redundancy Records</h2>
        <a class="btn btn-primary" href="{{ route('hr.exit.redundancies.create') }}">+ New Redundancy</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-3" method="GET">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['Draft','Approved','Closed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Ref No</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Union Notified</th>
                            <th>Labour Office Notified</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($redundancies as $redundancy)
                            <tr>
                                <td>{{ $redundancy->RefNo }}</td>
                                <td>{{ $redundancy->Reason ?? '-' }}</td>
                                <td>{{ $redundancy->Status }}</td>
                                <td>{{ $redundancy->UnionNotified ? 'Yes' : 'No' }}</td>
                                <td>{{ $redundancy->LabourOfficeNotified ? 'Yes' : 'No' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.exit.redundancies.show', $redundancy->Id) }}">View</a>
                                    @if($redundancy->Status !== 'Approved')
                                        <form action="{{ route('hr.exit.redundancies.approve', $redundancy->Id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit">Approve</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No redundancy records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $redundancies->links() }}
        </div>
    </div>
</div>
@endsection
