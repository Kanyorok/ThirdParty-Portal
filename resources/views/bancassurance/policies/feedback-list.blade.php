@extends('layouts.app')
@section('title', 'Underwriting Feedback Listing')

@section('content')
    <div class="container mt-4">
        <h4>📄 Proposals Awaiting Underwriting Feedback</h4>

        <table class="table table-bordered mt-3">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Policy #</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Insurer</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($proposals as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->PolicyNumber }}</td>
                    <td>{{ $item->CustomerName }}</td>
                    <td>{{ $item->ProductName }}</td>
                    <td>{{ $item->InsurerName }}</td>
                    <td>{{ $item->Status }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->CreatedAt)->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('bancassurance.policies.feedbackForm', $item->Id) }}"
                           class="btn btn-sm btn-primary">📝 Feedback</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">No proposals awaiting feedback.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
