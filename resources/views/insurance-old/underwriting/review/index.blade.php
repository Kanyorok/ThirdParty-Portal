@extends('layouts.app')
@section('title', 'Submitted Proposals')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📑 Submitted Proposals</h4>

        @if ($proposals->count() > 0)
            <table class="table table-bordered table-striped table-sm align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Client Name</th>
                    <th>Product</th>
                    <th>Proposal Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($proposals as $proposal)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $proposal->ClientName }}</td>
                        <td>{{ $proposal->ProductName }}</td>
                        <td>{{ \Carbon\Carbon::parse($proposal->CreatedAt)->format('Y-m-d') }}</td>
                        <td>
                            <span class="badge
                                @if($proposal->Status == 'Submitted') bg-warning
                                @elseif($proposal->Status == 'Approved') bg-success
                                @elseif($proposal->Status == 'Rejected') bg-danger
                                @elseif($proposal->Status == 'Returned') bg-secondary
                                @else bg-light text-dark
                                @endif">
                                {{ $proposal->Status }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('underwriting.proposals.review', ['id' => $proposal->Id]) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-eye"></i> Review
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">
                No submitted proposals found.
            </div>
        @endif
    </div>
@endsection
