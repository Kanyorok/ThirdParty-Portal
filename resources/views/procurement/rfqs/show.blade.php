@extends('layouts.app')
@section('title', 'RFQ Details')
@section('content')
<div class="container">
    <h3>RFQ Details</h3>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>RFQ Number:</strong> {{ $rfq->RFQNumber }}</p>
            <p><strong>RFQ Comments:</strong> {{ $rfq->Comments }}</p>
            <p><strong>Item Category:</strong> {{ $rfq->category->Name }}</p>
        </div>
    </div>

    <h5>Requisition Items Details:</h5>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>UOM</th>
                <th>Submission Deadline</th>
                <th>Description</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rfq->RequisitionItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ $item['unit'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($rfq->SubmissionDeadline)->format('d M Y') }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $rfq->Status}}</td>
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
