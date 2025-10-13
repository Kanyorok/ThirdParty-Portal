{{-- resources/views/assets/acq/cwip/lines/edit.blade.php --}}
@extends('layouts.app')
@section('title','Edit CWIP Line • '.$proj->ProjectCode)

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted">
        Edit CWIP Line — <span class="fw-semibold">{{ $proj->ProjectCode }}</span>
      </h6>
      <div>
        <a href="{{ route('assets.acq.cwip-projects.show',$proj->Id) }}" class="btn btn-outline-secondary btn-sm">Back to Project</a>
      </div>
    </div>

    <div class="card-body p-3">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Read-only reference info --}}
      <div class="row g-3 mb-2">
        <div class="col-md-2">
          <label class="form-label">Line ID</label>
          <input class="form-control form-control-sm" value="{{ $row->Id }}" readonly>
        </div>
        <div class="col-md-3">
          <label class="form-label">Reference</label>
          <input class="form-control form-control-sm" value="{{ $row->RefType ?: 'MANUAL' }} {{ $row->RefID ?: '' }}" readonly>
        </div>
        <div class="col-md-2">
          <label class="form-label">Status</label>
          <input class="form-control form-control-sm" value="{{ $row->Status }}" readonly>
        </div>
        <div class="col-md-2">
          <label class="form-label">Total (auto)</label>
          <input id="TotalCost" class="form-control form-control-sm" value="{{ number_format((float)$row->Quantity*(float)$row->UnitCost + (float)$row->TaxAmount,2,'.','') }}" readonly>
        </div>
      </div>

      <form method="post" action="{{ route('assets.acq.cwip-projects.lines.update',[$proj->Id,$row->Id]) }}">
        @csrf @method('PUT')

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Description</label>
            <input name="Description" value="{{ old('Description',$row->Description) }}" class="form-control form-control-sm" required>
          </div>

          <div class="col-md-2">
            <label class="form-label">Quantity</label>
            <input type="number" step="0.0001" name="Quantity" id="Quantity" value="{{ old('Quantity',$row->Quantity) }}" class="form-control form-control-sm" required>
          </div>

          <div class="col-md-2">
            <label class="form-label">Unit Cost</label>
            <input type="number" step="0.01" name="UnitCost" id="UnitCost" value="{{ old('UnitCost',$row->UnitCost) }}" class="form-control form-control-sm" required>
          </div>

          <div class="col-md-2">
            <label class="form-label">Tax Amount</label>
            <input type="number" step="0.01" name="TaxAmount" id="TaxAmount" value="{{ old('TaxAmount',$row->TaxAmount) }}" class="form-control form-control-sm">
          </div>

          <div class="col-md-3">
            <label class="form-label">Received Date</label>
            <input type="date" name="ReceivedDate" value="{{ old('ReceivedDate',$row->ReceivedDate) }}" class="form-control form-control-sm">
          </div>

          <div class="col-md-3">
            <label class="form-label">Class (override)</label>
            <select name="ClassID" class="form-select form-select-sm">
              <option value="">—</option>
              @foreach($classes as $c)
                <option value="{{ $c->Id }}" @selected(old('ClassID',$row->ClassID)==$c->Id)>{{ $c->Name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Location (override)</label>
            <select name="LocationID" class="form-select form-select-sm">
              <option value="">—</option>
              @foreach($locations as $l)
                <option value="{{ $l->Id }}" @selected(old('LocationID',$row->LocationID)==$l->Id)>{{ $l->Code }} — {{ $l->Site }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Book</label>
            <select name="BookID" class="form-select form-select-sm">
              <option value="">—</option>
              @foreach($books as $b)
                <option value="{{ $b->Id }}" @selected(old('BookID',$row->BookID)==$b->Id)>{{ $b->Name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="IsCapitalizable" id="IsCapitalizable"
                     @checked(old('IsCapitalizable', (bool)$row->IsCapitalizable))>
              <label class="form-check-label" for="IsCapitalizable">Capitalizable</label>
            </div>
          </div>

          <div class="col-md-12">
            <label class="form-label">Notes</label>
            <input name="Notes" value="{{ old('Notes',$row->Notes) }}" class="form-control form-control-sm">
          </div>
        </div>

        <div class="mt-3">
          <a href="{{ route('assets.acq.cwip-projects.show',$proj->Id) }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- tiny helper to live-calc total --}}
<script>
  (function(){
    const q = document.getElementById('Quantity');
    const u = document.getElementById('UnitCost');
    const t = document.getElementById('TaxAmount');
    const total = document.getElementById('TotalCost');
    function recalc(){
      const qty = parseFloat(q.value||0), unit = parseFloat(u.value||0), tax = parseFloat(t.value||0);
      total.value = (qty*unit + tax).toFixed(2);
    }
    [q,u,t].forEach(el => el && el.addEventListener('input', recalc));
  })();
</script>
@endsection
