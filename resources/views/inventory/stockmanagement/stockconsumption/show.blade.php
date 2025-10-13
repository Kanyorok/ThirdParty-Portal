@extends('layouts.app')

@section('title', 'View Stock Consumption')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Stock Consumption Details</h4>
        <a href="{{ route('stockconsumption.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body row">
            <div class="col-md-6 mb-3">
                <strong>Consumption No:</strong> {{ $consumption->ConsumptionNo }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Item:</strong> {{ $consumption->stockItem?->ItemName ?? 'N/A' }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Quantity:</strong> {{ $consumption->Quantity }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Unit of Measure:</strong> {{ $consumption->uom?->Name ?? 'N/A' }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Branch:</strong> {{ $consumption->branch?->Name ?? 'N/A' }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Issued To:</strong> {{ $consumption->issued_to_name }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Issued By:</strong> {{ $consumption->issuedBy?->Name ?? 'N/A' }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Issued On:</strong> {{ \Carbon\Carbon::parse($consumption->IssuedOn)->format('m/d/Y') }}
            </div>

            <div class="col-md-12 mb-3">
                <strong>Remarks:</strong> {{ $consumption->Remarks ?? 'N/A' }}
            </div>
        </div>
    </div>
</div>
@endsection
