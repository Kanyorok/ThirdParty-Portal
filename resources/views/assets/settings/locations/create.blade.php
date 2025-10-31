@extends('layouts.app')
@section('title','New Asset Location')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Create Location</h6></div>
    <div class="card-body p-3">
      @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

      <form method="post" action="{{ route('assets.settings.locations.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-3"><label class="form-label">Code</label><input name="Code" value="{{ old('Code') }}" class="form-control form-control-sm" required></div>
          <div class="col-md-4"><label class="form-label">Site</label><input name="Site" value="{{ old('Site') }}" class="form-control form-control-sm" required></div>
          <div class="col-md-3"><label class="form-label">Building</label><input name="Building" value="{{ old('Building') }}" class="form-control form-control-sm"></div>
          <div class="col-md-1"><label class="form-label">Floor</label><input name="Floor" value="{{ old('Floor') }}" class="form-control form-control-sm"></div>
          <div class="col-md-1"><label class="form-label">Room</label><input name="Room" value="{{ old('Room') }}" class="form-control form-control-sm"></div>
          <div class="col-md-6">
            <label class="form-label">Parent Location (optional)</label>
            <select name="ParentID" class="form-select form-select-sm">
              <option value="">— None —</option>
              @foreach($parents as $p)
                <option value="{{ $p->Id }}" @selected(old('ParentID')==$p->Id)>{{ $p->Code }} — {{ $p->Site }} {{ $p->Building ? '• '.$p->Building : '' }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2 d-flex align-items-end"><div class="form-check">
            <input class="form-check-input" type="checkbox" name="IsActive" id="IsActive" @checked(old('IsActive',true))><label class="form-check-label" for="IsActive">Active</label>
          </div></div>
        </div>
        <div class="mt-3">
          <a href="{{ route('assets.settings.locations.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
