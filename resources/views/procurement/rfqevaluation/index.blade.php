@extends('layouts.app')

@section('title', 'Supplier Evaluations')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Supplier Evaluations</h2>
        <a href="{{ route('evaluations.create') }}" class="btn btn-success">+ Create Evaluation</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Committee Member</th>
                    <th>RFQ No</th>
                    <th>Supplier</th>
                    <th>Total Quoted</th>
                    <th>Delivery Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($rfqEvaluations as $index => $evaluation)
                @foreach ($evaluation->evaluations as $supplierEvaluation)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $evaluation->CommitteeMemberName }}</td>
                        <td>{{ $evaluation->rfq->RFQNumber ?? 'N/A' }}</td>
                        <td>{{ $supplierEvaluation->evaluation->SupplierId ?? 'N/A' }}</td>
                        <td>{{ number_format($supplierEvaluation->QuotedPrice ?? 0, 2) }}</td>
                        <td>{{ $supplierEvaluation->DeliveryTime }} Days</td>
                        <td>
                            Actions
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" class="text-center">No evaluations found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
