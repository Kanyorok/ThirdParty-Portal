@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Beneficiaries — {{ $medical_fund->FundName }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('bancassurance.medicalfunds.beneficiaries.create',$medical_fund->Id) }}" class="btn btn-primary">Add Beneficiary</a>
            <a href="{{ route('bancassurance.medicalfunds.edit', $medical_fund->Id) }}" class="btn btn-outline-secondary">Back to Fund</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="card">
        <div class="card-body p-0">
            @if($beneficiaries->count())
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Relationship</th>
                                <th>DOB</th>
                                <th>National ID</th>
                                <th>Contact</th>
                                <th>Active</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($beneficiaries as $i => $b)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $b->FullName }}</td>
                                    <td>{{ $b->Relationship ?? '—' }}</td>
                                    <td>{{ optional($b->DateOfBirth)->format('Y-m-d') ?: '—' }}</td>
                                    <td>{{ $b->NationalID ?? '—' }}</td>
                                    <td>{{ $b->Contact ?? '—' }}</td>
                                    <td>{!! $b->IsActive ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('bancassurance.medicalfunds.beneficiaries.edit', $b->Id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form action="{{ route('bancassurance.medicalfunds.beneficiaries.destroy', $b->Id) }}" method="POST" onsubmit="return confirm('Remove beneficiary?');">
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
                <div class="p-4 text-center text-muted">No beneficiaries yet.</div>
            @endif
        </div>
        @if($beneficiaries->hasPages())
            <div class="card-footer">{{ $beneficiaries->links() }}</div>
        @endif
    </div>
</div>
@endsection
