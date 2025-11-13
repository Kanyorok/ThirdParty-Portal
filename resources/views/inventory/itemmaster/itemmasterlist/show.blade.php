@extends('layouts.app')

@section('title', 'Item Details')

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

<div class="container bg-white shadow-sm rounded p-4">
    <h4>📦 Item Details: {{ $item->ItemName }}</h4>

    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Item Code:</strong> {{ $item->ItemCode }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Bar Code:</strong> {{ $item->BarCode }}</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Item Type:</strong> {{ $item->itemType?->TypeName ?? 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Category:</strong> {{ $item->category?->Name ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Parent Category:</strong> {{ $item->category->parent?->Name ?? 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Unit of Measure (UOM):</strong> {{ $item->uom?->Code ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Inventory Type:</strong> {{ $item->inventoryType?->Type ?? 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Item Image:</strong></p>
            @if($item->image)
                <img src="data:{{ $item->image->MIMEType }};base64,{{ $item->image->Image }}" alt="Item Image"
                     style="max-width:200px;">
            @endif

        </div>
    </div>

    <div class="row mb-3">

        <div class="col-md-6">
            <p><strong>Item Description:</strong> {{ $item->ItemDescription ?? 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <h6 class="fw-bold mb-2">📄 Documents</h6>
            @forelse($item->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
            @empty
                <p class="text-muted mb-0">No documents uploaded.</p>
            @endforelse
        </div>
        <div class="col-md-6">
            <p class="card-text"><strong>Status:</strong> {{ $item->status->Description ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <a href="{{ route('itemmaster.index') }}" class="btn btn-secondary">Back</a>

        <a href="{{ route('itemmasterlist.edit', $item->Id) }}" class="btn btn-warning">Edit Item</a>
        @if ($item->inUse())
            <button class="btn btn-info" disabled title="Item is in use and cannot be deleted">
                <i class="bi bi-lock"></i> Item In Use
            </button>
        @else
            <form action="{{ route('itemmasterlist.destroy', $item->Id) }}" method="POST"
                onsubmit="return confirm('⚠️ Are you sure you want to delete this Item?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Delete Item
                </button>
            </form>
        @endif


    </div>
</div>

@endsection
@section('scripts')
 @include('snippets.actions.preview-files')
@endsection
