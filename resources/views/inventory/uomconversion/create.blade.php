@extends('layouts.app')

@section('title', 'Unit of Measure & Conversion Setup')

@section('content')
<div class="container mt-4">

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Add UOM Conversion for Item</div>
    <div class="card-body">
      <form action="{{ route('uomconversion.store') }}" method="POST">
        @csrf

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Item <span class="text-danger">*</span></label>
            <select name="Item" id="item_id" class="form-select @error('Item') is-invalid @enderror" required>
              <option value="">-- Select Item --</option>
              @foreach($items as $item)
                    <option value="{{ $item->Id }}"
                            data-uom-id="{{ $item->uom?->Id }}"
                        data-uom-name="{{ $item->uom?->Name }}">
                    {{ $item->ItemCode }} - {{ $item->ItemName }}
                </option>
              @endforeach
            </select>
            @error('Item')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Base UOM</label>
            <input type="hidden" id="base_uom_id" name="UOM">
            <input type="text" id="base_uom_name" class="form-control" readonly>
          </div>

          <div class="col-md-4">
            <label class="form-label">Alternate UOM <span class="text-danger">*</span></label>
            <select name="AlternateUOM" class="form-select @error('AlternateUOM') is-invalid @enderror" required>
              <option value="">-- Select Alternate UOM --</option>
              @foreach($alternateUoms as $uom)
                <option value="{{ $uom->Id }}">{{ $uom->Name }}</option>
              @endforeach
            </select>
            @error('AlternateUOM')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Conversion Factor <span class="text-danger">*</span></label>
            <input type="number" name="ConversionFactor" step="0.01" class="form-control @error('ConversionFactor') is-invalid @enderror" required placeholder="e.g. 12">
            @error('ConversionFactor')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Remarks</label>
            <input type="text" name="Remarks" class="form-control @error('Remarks') is-invalid @enderror" placeholder="Optional">
            @error('Remarks')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-4 d-flex align-items-end justify-content-end">
              <button type="submit" class="btn btn-success" id="submitBtn">💾 Save Mapping</button>
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

    document.getElementById('base_uom_id').value = uomId;  
    document.getElementById('base_uom_name').value = uomName;
});

document.querySelector('form').addEventListener('submit', function(e) {
    let isValid = true;
    const submitBtn = document.getElementById('submitBtn');

    document.querySelectorAll('.invalid-feedback.client-error').forEach(el => el.remove());
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const itemField = document.getElementById('item_id');
    if (!itemField.value.trim()) {
        showValidationError(itemField, 'Item is required.');
        isValid = false;
    }

    const alternateUomField = document.querySelector('select[name="AlternateUOM"]');
    if (!alternateUomField.value.trim()) {
        showValidationError(alternateUomField, 'Alternate UOM is required.');
        isValid = false;
    }

    const conversionFactorField = document.querySelector('input[name="ConversionFactor"]');
    if (!conversionFactorField.value.trim()) {
        showValidationError(conversionFactorField, 'Conversion Factor is required.');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
        return false;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...';
});

function showValidationError(field, message) {
    field.classList.add('is-invalid');

    let errorDiv = field.parentNode.querySelector('.invalid-feedback.client-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback client-error d-block';
        field.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}
</script>
@endpush
