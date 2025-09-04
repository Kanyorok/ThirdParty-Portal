@extends('layouts.app')
@section('title', '')
@section('content')
<div class="container mt-4" style="max-width: 1000px;">
    <h4 class="mb-3">Property Details</h4>
  
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6"> {{-- smaller gap & font --}}
                
                <div class="col">
                    <strong>Property Name</strong>
                    <p class="mb-1">{{ $property->PropertyName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Property Code</strong>
                    <p class="mb-1">{{ $property->PropertyCode ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Property Type</strong>
                    <p class="mb-1">{{ $property->type->PropertyTypeName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Property Category</strong>
                    <p class="mb-1">{{ $property->propertyCategory->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Owner</strong>
                    <p class="mb-1">{{ $property->Owner ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Acquisition Date</strong>
                    <p class="mb-1">{{ $property->AcquisitionDate ? \Carbon\Carbon::parse($property->AcquisitionDate)->format('d M Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Country</strong>
                    <p class="mb-1">{{ $property->Country ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Town/City</strong>
                    <p class="mb-1">{{ $property->propertyLocality->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Area/Locality</strong>
                    <p class="mb-1">{{ $property->AreaLocality ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Property Description</strong>
                    <p class="mb-1">{{ $property->PropertyDescription ?? '-' }}</p>
                </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Audit Information</h6>
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">
                <div class="col">
                    <strong>Created By</strong>
                    <p class="mb-1">{{ $property->createdByUser->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Created On</strong>
                    <p class="mb-1">{{ $property->CreatedOn ? \Carbon\Carbon::parse($property->CreatedOn)->format('d M Y H:i') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Modified By</strong>
                    <p class="mb-1">{{ $property->modifiedByUser->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Modified On</strong>
                    <p class="mb-1">{{ $property->ModifiedOn ? \Carbon\Carbon::parse($property->ModifiedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer text-end py-2">
            <a href="{{ route('PropertyRegistry.index') }}" class="btn btn-sm btn-secondary">⬅ Back</a>
        </div>
    </div>
</div>
@endsection


