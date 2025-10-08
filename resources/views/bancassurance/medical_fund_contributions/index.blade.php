@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Contributions — {{ $medical_fund->FundName }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('bancassurance.medicalfunds.create',$medical_fund->Id) }}" class="btn btn-primary">New Contribution</a>
            <a href="{{ route('bancassurance.medicalfunds.index', $medical_fund->Id) }}" class="btn btn-outline-secondary">Back to Fund</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>Total Contributions</div>
                <div class="fw-bold">{{ number_format((float)($totals['sum'] ?? 0),2) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($contributions->count())
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Contributor Type</th>
                                <th>Contributor ID</th>
                                <th class="text-end">Amount</th>
                                <th>Notes</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($contributions as $i => $c)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ optional($c->ContributionDate)->format('d/m/Y') }}</td>
                                <td>{{ $c->ContributorType ?? '-' }}</td>
                                <td>{{ $c->ContributorID ?? '—' }}</td>
                                <td class="text-end">{{ number_format((float)$c->Amount,2) }}</td>
                                <td>{{ $c->Notes ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('bancassurance.medicalfunds.edit',$c->Id) }}">Edit</a>
                                        <form action="{{ route('bancassurance.medicalfunds.destroy',$c->Id) }}" method="POST" onsubmit="return confirm('Delete this contribution?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-muted">No contributions recorded.</div>
            @endif
        </div>
        @if($contributions->hasPages())
            <div class="card-footer">{{ $contributions->links() }}</div>
        @endif
    </div>
</div>
@endsection
