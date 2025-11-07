@extends('layouts.app')
@section('title','New Numbering Rule')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Create Numbering Rule</h6></div>
    <div class="card-body p-3">
      @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
      <form method="post" action="{{ route('assets.settings.numbering-rules.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-3"><label class="form-label">Prefix (optional)</label><input name="Prefix" value="{{ old('Prefix','AST') }}" class="form-control form-control-sm"></div>
          <div class="col-md-2"><label class="form-label">Separator</label><input name="Separator" value="{{ old('Separator','-') }}" class="form-control form-control-sm" required></div>
          <div class="col-md-2"><label class="form-label">Padding</label><input type="number" name="Padding" value="{{ old('Padding',5) }}" min="1" max="12" class="form-control form-control-sm" required></div>
          <div class="col-md-2"><label class="form-label">Next Number</label><input type="number" name="NextNumber" value="{{ old('NextNumber',1) }}" min="1" class="form-control form-control-sm" required></div>
          <div class="col-md-3"><label class="form-label">Sample Preview (auto if blank)</label><input name="SamplePreview" value="{{ old('SamplePreview') }}" class="form-control form-control-sm"></div>
          <div class="col-md-2 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="IsActive" id="act" @checked(old('IsActive',true))>
              <label for="act" class="form-check-label">Active</label>
            </div>
          </div>
        </div>
        <div class="mt-3">
          <a href="{{ route('assets.settings.numbering-rules.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
