@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Disbursements — {{ $medical_fund->FundName }}</h4>
            <div class="d-flex gap-2">
            {{-- Pass the model directly so route-model-binding provides the correct parameter regardless of attribute name casing --}}
            <a href="{{ route('bancassurance.medicalfunds.disbursements.create', $medical_fund) }}" class="btn btn-primary">New Disbursement</a>
            <a href="{{ route('bancassurance.medicalfunds.edit', $medical_fund) }}" class="btn btn-outline-secondary">Back to Fund</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>Total Disbursed</div>
                <div class="fw-bold">{{ number_format((float)($totals['sum'] ?? 0),2) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($disbursements->count())
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Beneficiary</th>
                                <th>Purpose</th>
                                <th class="text-end">Amount</th>
                                <th>Approved By</th>
                                <th>Approved On</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($disbursements as $i => $d)
                            <tr>
                                <td>{{ $disbursements->firstItem() + $i }}</td>
                                <td>{{ optional($d->DisbursementDate)->format('Y-m-d') }}</td>
                                <td>{{ optional($d->beneficiary)->FullName ?? '—' }}</td>
                                <td>{{ $d->Purpose ?? '—' }}</td>
                                <td class="text-end">{{ number_format((float)$d->Amount,2) }}</td>
                                <td>{{ $d->ApprovedBy ?? '—' }}</td>
                                <td>{{ optional($d->ApprovedOn)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('bancassurance.disbursements.edit', $d->ID) }}">Edit </a>
                                        <form action="{{ route('bancassurance.disbursements.destroy', $d->ID) }}" method="POST" onsubmit="return confirm('Delete this disbursement?');">
                                        @csrf
                                        @method('DELETE')
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
                <div class="p-4 text-center text-muted">No disbursements recorded.</div>
            @endif
        </div>
        @if($disbursements->hasPages())
            <div class="card-footer">{{ $disbursements->links() }}</div>
        @endif
    </div>
</div>
@endsection
