@extends('layouts.app')
@section('title', 'Proposal Review List')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">📋 Proposal Review Queue</h4>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Policy Number</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Sum Assured</th>
                <th>Status</th>
                <th>Submitted On</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($proposals as $p)
                <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{ $p->PolicyNumber }}</td>
                    <td>{{ $p->customer->FullName }}</td>
                    <td>{{ $p->product->Description }}</td>
                    <td>{{ number_format($p->SumAssured, 2) }}</td>
                    <td>
                    <span class="badge bg-{{ $p->Status->badgeColor() }}">
                        {{ $p->Status->label() }}
                    </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($p->IssuedDate)->format('d/m/Y') }}</td>
                    <td>
                        @if($p->Status === 'Proposal')
                            <a href="{{ route('bancassurance.policies.review', $p->Id) }}"
                               class="btn btn-sm btn-primary">📤 Submit</a>
                        @else
                            <a href="{{ route('bancassurance.policies.review', $p->Id) }}"
                               class="btn btn-sm btn-warning">📂 View</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No proposals found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
