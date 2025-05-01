@extends('layouts.app')
@section('title', 'RFQ Responses')
@section('content')
<div class="container">
    <h3>RFQ Response List</h3>

    <div class="container mt-3">
        
        <a href="{{ route('rfqresponses.create') }}" class="btn btn-primary mb-2">Create RFQ Response</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>RFQ Response Number</th>
                    <th>RFQ Number</th>
                    <th>Supplier Name</th>
                    <th>Items Quoted</th>
                    <th>Total Price</th>
                    <th>Days to Delivery</th>
                    <th>Delivery Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rfqResponses as $response)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $response->RFQResponseNumber }}</td>
                    <td>{{ $response->RFQNumber }}</td>
                    <td>{{ $response->SupplierName }}</td>
                    <td>
                        @php
                            $requisitionItems = json_decode($response->RequisitionItems, true); // Decode JSON to array
                        @endphp

                        @if (is_array($requisitionItems))
                            @foreach($requisitionItems as $item)
                                <li>{{ $item['name'] }} — Price Quoted {{ $item['quotedprice'] }} — Total Payable(Tax 16%) {{ $item['totalpayable'] }}</li>
                            @endforeach
                        @else
                            <p>No items found</p>
                        @endif
                    </td>
                    <td>{{$response->Currency}}{{ $response->TotalPayable}}</td>
                    <td>
    @php
        $deliveryDate = \Carbon\Carbon::parse($response->CreatedOn)->addDays((int) $response->DurationDays)->startOfDay();
        $today = \Carbon\Carbon::now()->startOfDay();
        $daysRemaining = $today->diffInDays($deliveryDate, false);
    @endphp

    @if ($daysRemaining > 0)
        {{ $daysRemaining }} day{{ $daysRemaining > 1 ? 's' : '' }} remaining
    @elseif ($daysRemaining === 0)
        Delivery is today
    @else
        Delivered {{ abs($daysRemaining) }} day{{ abs($daysRemaining) > 1 ? 's' : '' }} ago
    @endif
</td>

<td>{{ $deliveryDate->format('d M Y') }}</td
                    <td>
                        
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>
@endsection