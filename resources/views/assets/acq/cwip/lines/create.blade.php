@extends('layouts.app')
@section('title','Add CWIP Line')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Add Line — {{ $proj->ProjectCode }}</h6></div>
    <div class="card-body p-3">
      @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
      <form method="post" action="{{ route('assets.acq.cwip-projects.lines.store',$proj->Id) }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Ref Type</label>
            <select name="RefType" class="form-select form-select-sm">
              <option value="">(Manual)</option>
              @foreach(['PO','GRN'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Linked Doc</label>
            <select name="RefID" class="form-select form-select-sm">
              <option value="">—</option>
              @foreach($proc as $p)<option value="{{ $p->Id }}">{{ $p->DocType }} {{ $p->DocNo }} — {{ $p->SupplierName }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-5"><label class="form-label">Description</label><input name="Description" class="form-control form-control-sm" required></div>
          <div class="col-md-2"><label class="form-label">Quantity</label><input type="number" step="0.0001" name="Quantity" value="1" class="form-control form-control-sm" required></div>
          <div class="col-md-2"><label class="form-label">Unit Cost</label><input type="number" step="0.01" name="UnitCost" class="form-control form-control-sm" required></div>
          <div class="col-md-2"><label class="form-label">Tax</label><input type="number" step="0.01" name="TaxAmount" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Received Date</label><input type="date" name="ReceivedDate" class="form-control form-control-sm"></div>
          <div class="col-md-3">
            <label class="form-label">Class</label>
            <select name="ClassID" class="form-select form-select-sm"><option value="">—</option>@foreach($classes as $c)<option value="{{ $c->Id }}">{{ $c->Name }}</option>@endforeach</select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Location</label>
            <select name="LocationID" class="form-select form-select-sm"><option value="">—</option>@foreach($locations as $l)<option value="{{ $l->Id }}">{{ $l->Code }} — {{ $l->Site }}</option>@endforeach</select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Book</label>
            <select name="BookID" class="form-select form-select-sm"><option value="">—</option>@foreach($books as $b)<option value="{{ $b->Id }}">{{ $b->Name }}</option>@endforeach</select>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="IsCapitalizable" id="ic" checked>
              <label class="form-check-label" for="ic">Capitalizable</label>
            </div>
          </div>
          <div class="col-md-12"><label class="form-label">Notes</label><input name="Notes" class="form-control form-control-sm"></div>
        </div>
        <div class="mt-3">
          <a href="{{ route('assets.acq.cwip-projects.show',$proj->Id) }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
