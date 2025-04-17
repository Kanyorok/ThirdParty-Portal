@extends('layouts.app')
@section('title','Tenders List')
@section('content')
<div class="container">
    <h3>All Tenders</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('tendering-process.create') }}" class="btn btn-primary mb-3">+ New Tender</a>

    @if($tenders->count())
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tender Number</th>
                    <th>Title</th>
                    <th>Procurement Mode</th>
                    <th>Estimated Value</th>
                    <th>Currency</th>
                    <th>Start Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tenders as $tender)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $tender->TenderNumber }}</td>
                        <td><a href="{{ route('tendering-process.show', $tender->Id) }}">{{ $tender->Title }}</a></td>
                        <td>{{ $tender->procurementMode->Name ?? 'N/A' }}</td>
                        <td>{{ number_format($tender->EstimatedValue, 2) }}</td>
                        <td>{{ $tender->Currency }}</td>
                        <td>{{ \Carbon\Carbon::parse($tender->StartDate)->format('d M Y') }}</td>
                        <td>
                            @if($tender->Status == 'open')
                                <span class="badge bg-warning">Open</span>
                            @elseif($tender->Status == 'closed')
                                <span class="badge bg-success">Closed</span>
                            @elseif($tender->Status == 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                            @elseif($tender->Status == 'awarded')
                                <span class="badge bg-info">Awarded</span>
                            @else
                                <span class="badge bg-secondary">Unknown</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('tendering-process.edit', $tender->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('tendering-process.destroy', $tender->Id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this tender?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No tenders found.</p>
    @endif
</div>
@endsection
