@extends('layouts.app')

@section('content')
<div class="container">
    <h3>RFQ Details</h3>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Tender Number:</strong> {{ $rfq->tender->TenderNumber }}</p>
            <p><strong>Tender Title:</strong> {{ $rfq->tender->Title }}</p>
            <p><strong>Item Category:</strong> {{ $rfq->category->Name }}</p>
        </div>
    </div>

    <h5>Suppliers Contacted:</h5>
    <ul>
        @foreach($suppliers as $supplier)
            <li>{{ $supplier->SupplierName }} — {{ $supplier->ContactEmail }}</li>
        @endforeach
    </ul>
</div>
@endsection
