@extends('layouts.app')
@section('title','Edit • '.$asset->AssetCode)

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Edit Asset</h6></div>
    <div class="card-body p-3">
      @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
      <form method="post" action="{{ route('assets.master.register.update',$asset->Id) }}">
        @csrf @method('PUT')
        <div class="row g-3">
          <div class="col-md-3"><label class="form-label">Asset Code</label><input name="AssetCode" value="{{ old('AssetCode',$asset->AssetCode) }}" class="form-control form-control-sm" required></div>
          <div class="col-md-5"><label class="form-label">Asset Name</label><input name="AssetName" value="{{ old('AssetName',$asset->AssetName) }}" class="form-control form-control-sm" required></div>
          <div class="col-md-4">
            <label class="form-label">Class</label>
            <select name="ClassID" class="form-select form-select-sm" required>
              @foreach($classes as $c)<option value="{{ $c->Id }}" @selected(old('ClassID',$asset->ClassID)==$c->Id)>{{ $c->Name }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Location</label>
            <select name="LocationID" class="form-select form-select-sm">
              <option value="">—</option>
              @foreach($locations as $l)<option value="{{ $l->Id }}" @selected(old('LocationID',$asset->LocationID)==$l->Id)>{{ $l->Code }} — {{ $l->Site }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-3"><label class="form-label">Acquisition Date</label><input type="date" name="AcquisitionDate" value="{{ old('AcquisitionDate',$asset->AcquisitionDate) }}" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Capitalization Date</label><input type="date" name="CapitalizationDate" value="{{ old('CapitalizationDate',$asset->CapitalizationDate) }}" class="form-control form-control-sm"></div>
          <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="Status" class="form-select form-select-sm">
              @foreach(['Active','Inactive','Disposed'] as $s)<option value="{{ $s }}" @selected(old('Status',$asset->Status)==$s)>{{ $s }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-3"><label class="form-label">Manufacturer</label><input name="Manufacturer" value="{{ old('Manufacturer',$asset->Manufacturer) }}" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Model</label><input name="Model" value="{{ old('Model',$asset->Model) }}" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Serial</label><input name="SerialNumber" value="{{ old('SerialNumber',$asset->SerialNumber) }}" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Tag No.</label><input name="TagNo" value="{{ old('TagNo',$asset->TagNo) }}" class="form-control form-control-sm"></div>
          <div class="col-md-12"><label class="form-label">Notes</label><textarea name="Notes" rows="2" class="form-control form-control-sm">{{ old('Notes',$asset->Notes) }}</textarea></div>
        </div>
        <div class="mt-3">
          <a href="{{ route('assets.master.register.show',$asset->Id) }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
