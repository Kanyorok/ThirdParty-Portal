@extends('layouts.app')

@section('title', 'Edit Item Price')

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
    <div class="container mt-4">
        <h4 class="mb-3">Edit Price</h4>

        <form method="POST" action="{{ route('pricemanagement.update', $price->Id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">SKUID<span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="PriceID" value="{{ old('PriceID', $price->PriceID) }}"
                       readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Item<span class="text-danger">*</span></label>
                <select class="form-select" name="ItemID" disabled>
                    <option disabled>Select Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->Id }}" {{ $item->Id == $price->ItemID ? 'selected' : '' }}>
                            {{ $item->ItemID ?? $item->ItemName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">UOM<span class="text-danger">*</span></label>
                <select class="form-select" name="UOM_display" disabled>
                    <option disabled>Select UOM</option>
                    @foreach($uoms as $uom)
                        <option value="{{ $uom->Id }}" {{ $uom->Id == $price->UOM ? 'selected' : '' }}>
                            {{ $uom->Code }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="UOM" value="{{ $price->UOM }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Estimated Price<span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" name="ActualPrice"
                       value="{{ old('ActualPrice', $price->ActualPrice) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Currency<span class="text-danger">*</span></label>
                <select class="form-select" name="CurrencyCode" required>
                    <option disabled>-- Select Currency --</option>
                    @foreach($currencies ?? [] as $currenc)
                        <option value="{{ $currenc->Id }}" {{ $currenc->Id == old('CurrencyCode', $price->CurrencyCode) ? 'selected' : '' }}>
                            {{ $currenc->Code }} - {{ $currenc->Name }}
                        </option>
                        @endforeach
                    </select>
            </div>
            <button type="submit" class="btn btn-success"
                    onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Update Price
            </button>
            <a href="{{ route('pricemanagement.index') }}" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
@endsection
