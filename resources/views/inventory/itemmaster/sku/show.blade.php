@extends('layouts.app')

@section('title', 'SKU Details')

@section('content')

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card shadow rounded-4">
        <div class="card-header text-dark rounded-top-4" style="background-color: #add8e6;">
            <h4 class="mb-0">📦 Stock Item Details</h4>
        </div>

        <div class="container bg-white shadow-sm rounded p-4">

            <div class="row mb-3">
                <div class="col-md-3">
                    <p><strong>SKU Code:</strong> {{ $item->SKUCode }}</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Item:</strong> {{ optional($item->item)->ItemName ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Unit Cost:</strong> {{ number_format($item->UnitCost, 2) }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <p><strong>Current Qty:</strong> {{ $item->CurrentQty }}</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Min Stock Level:</strong> {{ $item->Min }}</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Reorder Qty:</strong> {{ $item->Reorder }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <p><strong>Branch:</strong> {{ optional($item->branch)->Name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Store:</strong> {{ optional($item->store)->StoreName ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Last Received:</strong>
                        {{ $item->LastReceived ? \Carbon\Carbon::parse($item->LastReceived)->format('d M Y') : 'N/A' }}
                    </p>
                </div>
                <div class="col-md-3">
                    <p>
                        <strong>Status:</strong>
                        {!! $item->Status
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-danger">Inactive</span>' !!}
                    </p>
                </div>
            </div>


            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('sku.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
