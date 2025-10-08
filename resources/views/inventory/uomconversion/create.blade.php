@extends('layouts.app')

@section('title', 'Unit of Measure & Conversion Setup')

@section('content')
<div class="container mt-4">

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Add UOM Conversion for Item</div>
    <div class="card-body">
      <form action="{{ route('uomconversion.store') }}" method="POST">
        @csrf

        {{-- Item Selection --}}
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Item</label>
            <select name="Item" id="item_id" class="form-select" required>
              <option value="">-- Select Item --</option>
              @foreach($items as $item)
                <option value="{{ $item->Id }}" 
                        data-uom-id="{{ $item->uom?->Id }}" 
                        data-uom-name="{{ $item->uom?->Name }}">
                    {{ $item->ItemCode }} - {{ $item->ItemName }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Base UOM (ID is hidden, Name is displayed) --}}
          <div class="col-md-4">
            <label class="form-label">Base UOM</label>
            <input type="hidden" id="base_uom_id" name="UOM">
            <input type="text" id="base_uom_name" class="form-control" readonly>
          </div>

          {{-- Alternate UOM --}}
          <div class="col-md-4">
            <label class="form-label">Alternate UOM</label>
            <select name="AlternateUOM" class="form-select" required>
              <option value="">-- Select Alternate UOM --</option>
              @foreach($alternateUoms as $uom)
                <option value="{{ $uom->Id }}">{{ $uom->Name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Conversion Factor & Remarks --}}
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Conversion Factor</label>
            <input type="number" name="ConversionFactor" step="0.01" class="form-control" required placeholder="e.g. 12">
          </div>
          <div class="col-md-4">
            <label class="form-label">Remarks</label>
            <input type="text" name="Remarks" class="form-control" placeholder="Optional">
          </div>
          <div class="col-md-4 d-flex align-items-end justify-content-end">
            <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">💾 Save Mapping</button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('item_id').addEventListener('change', function () {
    let selectedOption = this.options[this.selectedIndex];
    let uomId = selectedOption.getAttribute('data-uom-id') || '';
    let uomName = selectedOption.getAttribute('data-uom-name') || '';

    document.getElementById('base_uom_id').value = uomId;  // ID saved to DB
    document.getElementById('base_uom_name').value = uomName; // Displayed only
});
</script>
@endpush
