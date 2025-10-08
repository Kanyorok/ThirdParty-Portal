@extends('layouts.app')

@section('title', 'Medical Funds')

@section('content')
<div class="container mt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('bancassurance.medicalfunds.create') }}" class="btn btn-primary">
            New Fund
        </a>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search Form --}}
    <form method="GET" class="mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-4 col-sm-6">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search fund name...">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary">Search</button>
                @if(request('search'))
                    <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($funds->count())
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%">#</th>
                                <th>Fund</th>
                                <th>Provider</th>
                                <th>Coverage Type</th>
                                <th class="text-end">Coverage Limit</th>
                                <th class="text-center">Active</th>
                                <th class="text-end" style="width:20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($funds as $f)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $f->FundName }}</td>
                                    <td>{{ $f->provider->Name ?? '—' }}</td>
                                    <td>{{ $f->coverages->Description ?? '—' }}</td>
                                    <td class="text-end">{{ number_format((float)($f->CoverageLimit ?? 0), 2) }}</td>
                                    <td class="text-center">
                                        @if($f->IsActive)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('bancassurance.medicalfunds.show', $f->Id) }}" class="btn btn-outline-info">View</a>
                                            <a href="{{ route('bancassurance.medicalfunds.edit', $f->Id) }}" class="btn btn-outline-primary">Edit</a>
                                            <a href="{{ route('bancassurance.medicalfunds.beneficiaries.index', $f->Id) }}" class="btn btn-outline-secondary">Beneficiaries</a>
                                            <a href="{{ route('bancassurance.medicalfunds.contributions.index', $f->Id) }}" class="btn btn-outline-secondary">Contributions</a>
                                            <a href="{{ route('bancassurance.medicalfunds.disbursements.index', $f->Id) }}" class="btn btn-outline-secondary">Disbursements</a>
                                            <form action="{{ route('bancassurance.medicalfunds.destroy', $f->Id) }}" method="POST" onsubmit="return confirm('Archive this fund?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger">Archive</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-muted">
                    No medical funds found.
                </div>
            @endif
        </div>

        @if($funds->hasPages())
            <div class="card-footer bg-light">
                {{ $funds->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
