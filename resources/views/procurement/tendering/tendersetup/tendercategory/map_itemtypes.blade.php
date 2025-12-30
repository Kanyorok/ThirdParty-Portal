@extends('layouts.app')

@section('title','Map Item Types')

@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Map Item Types — {{ $category->TenderCategory }} ({{ $category->CategoryCode ?? 'CAT' }})</h4>
    <a href="{{ route('tendercategory.index') }}" class="btn btn-secondary">Back</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <form method="POST" action="{{ route('tendercategory.itemtypes.update', $category->Id) }}">
    @csrf
    <div class="card">
      <div class="card-body">
        <p class="text-muted">Select the Item Types allowed for this Tender Category. This limits what items can be added to tenders in this category.</p>
        <div class="row">
          @foreach($allTypes as $t)
            <div class="col-md-4 mb-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="item_type_ids[]" id="type{{ $t->Id }}" value="{{ $t->Id }}" {{ in_array($t->Id, $selected) ? 'checked' : '' }}>
                <label class="form-check-label" for="type{{ $t->Id }}">
                  <span class="fw-semibold">{{ $t->TypeName }}</span>
                  @if(!$t->Active)
                    <span class="badge bg-warning text-dark">Inactive</span>
                  @endif
                </label>
              </div>
            </div>
          @endforeach
        </div>
      </div>
      <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary">Save Mapping</button>
        <a href="{{ route('tendercategory.index') }}" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </div>
  </form>
</div>
@endsection
