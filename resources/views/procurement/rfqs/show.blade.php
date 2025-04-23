@extends('layouts.app')
@section('title', 'RFQ Details')
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

    <h5>Requisition Items Details:</h5>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rfq->RequisitionItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
