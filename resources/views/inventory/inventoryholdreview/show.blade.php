@extends('layouts.app')

@section('title', 'Inventory Review Details')

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
            <h4 class="mb-0">Inventory Review Details</h4>
        </div>

        <div class="container bg-white shadow-sm rounded p-4">

            <div class="row mb-3">
                <div class="col-md-4">
                    <p><strong>Item Name:</strong> {{ $holds->item->ItemName ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Quantity:</strong> {{ $holds->Quantity }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>UOM:</strong> {{ $holds->item->uom->Code ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <p><strong>From Branch:</strong> {{ $holds->fromBranch->Name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Store:</strong> {{ $holds->store->StoreName ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Defect:</strong> {{ $holds->defectDetail->Description ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <p><strong>Condition:</strong> {{ $holds->conditionDetail->Description ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="mb-3">
                <p><strong>Review Notes:</strong></p>
                <div class="p-3 border rounded bg-light">
                    {{ $holds->Notes ?? 'None' }}
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('inventoryholdreview.index') }}" class="btn btn-warning">Back to List</a>
            </div>
        </div>
    </div>

@endsection
