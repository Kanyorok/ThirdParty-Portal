@extends('layouts.app')
@section('title','Numbering Rule')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="fas fa-hashtag me-2"></i> Numbering Rule</h6>
      <div>
        <a href="{{ route('assets.settings.numbering-rules.edit',$row->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
        <a href="{{ route('assets.settings.numbering-rules.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
      </div>
    </div>
    <div class="card-body p-3">
      <dl class="row mb-0">
        <dt class="col-md-3">Prefix</dt><dd class="col-md-9">{{ $row->Prefix ?? '—' }}</dd>
        <dt class="col-md-3">Separator</dt><dd class="col-md-9">{{ $row->Separator }}</dd>
        <dt class="col-md-3">Padding</dt><dd class="col-md-9">{{ $row->Padding }}</dd>
        <dt class="col-md-3">Next Number</dt><dd class="col-md-9">{{ $row->NextNumber }}</dd>
        <dt class="col-md-3">Sample Preview</dt><dd class="col-md-9">{{ $row->SamplePreview }}</dd>
        <dt class="col-md-3">Active</dt><dd class="col-md-9">{{ $row->IsActive ? 'Yes' : 'No' }}</dd>
      </dl>
    </div>
  </div>
</div>
@endsection
