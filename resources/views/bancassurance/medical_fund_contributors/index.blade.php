@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Contributors — {{ $medical_fund->FundName }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('bancassurance.medicalfunds.show', ['medical_fund' => $medical_fund->Id]) }}" class="btn btn-outline-secondary">Back to Fund</a>
            <a href="{{ route('bancassurance.medicalfunds.contributors.create', ['medical_fund' => $medical_fund->Id]) }}" class="btn btn-primary">New Contributor</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, No, Email, Phone">
                </div>
                <div class="col-md-3">
                    <label? class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['Active','Suspended','Closed'] as $st)
                            <option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">Apply</button>
                    <a href="{{ route('bancassurance.medicalfunds.contributors.index', ['medical_fund' => $medical_fund->Id]) }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($contributors->count())
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Contributor</th>
                                <th>No</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Effective</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($contributors as $i => $c)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $c->thirdParty->ThirdPartyName ?? '-'}}</td>
                                <td>{{ $c->ContributorNo ?? '—' }}</td>
                                <td>{{ $c->thirdParty->Email ?? '—' }}</td>
                                <td>{{ $c->thirdParty->Phone ?? '—' }}</td>
                                <td><span class="badge bg-{{ $c->status->Description==='Active'?'success':($c->status->Description==='Suspended'?'warning text-dark':'secondary') }}">{{ $c->status->Description }}</span></td>
                                <td>
                                    @if(!empty($c->EffectiveFrom))
                                        {{ \Illuminate\Support\Carbon::parse($c->EffectiveFrom)->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                    @if(!empty($c->EffectiveTo))
                                        — {{ \Illuminate\Support\Carbon::parse($c->EffectiveTo)->format('d M Y') }}
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('bancassurance.contributors.show', $c->Id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye me-1"></i>Open
                                        </a>
                                        <a href="{{ route('bancassurance.contributors.edit', $c->Id) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>
                                        <form action="{{ route('bancassurance.contributors.destroy', $c->Id) }}" method="POST" onsubmit="return confirm('Archive this contributor?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger">
                                                <i class="bi bi-archive me-1"></i>Archive
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-muted">No contributors found.</div>
            @endif
        </div>
        @if($contributors->hasPages())
            <div class="card-footer">{{ $contributors->links() }}</div>
        @endif
    </div>
</div>
@endsection
