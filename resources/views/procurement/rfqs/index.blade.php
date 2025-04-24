@extends('layouts.app')

@section('title', 'RFQs List')

@section('content')
<div class="container">
    <h3>All RFQs</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('rfqs.create') }}" class="btn btn-primary mb-3">+ New RFQ</a>

    @if($rfqs->count())
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tender Number</th>
                    <th>Tender Title</th>
                    <th>Item Category</th>
                    <th>Suppliers</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rfqs as $rfq)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $rfq->tender->TenderNumber ?? '-' }}</td>
                        <td>{{ $rfq->tender->Title ?? '-' }}</td>
                        <td>{{ $rfq->category->Name ?? '-' }}</td>
                        <td>
                            @php
                                $supplierNames = \App\Models\Procurement\Supplier::whereIn('Id', $rfq->Suppliers)->pluck('SupplierName')->toArray();
                            @endphp
                            <ul>
                                @foreach($supplierNames as $name)
                                    <li>{{ $name }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>{{ $rfq->CreatedAt ? \Carbon\Carbon::parse($rfq->CreatedAt)->format('d M Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('rfqs.show', $rfq->Id) }}" class="btn btn-sm btn-info">View</a>
                            {{-- Add edit/delete buttons if needed --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No RFQs created yet.</p>
    @endif
</div>
@endsection
