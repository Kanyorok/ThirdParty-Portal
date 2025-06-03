@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="card mb-4">
<div class="card-header bg-info text-white">➕ Add Criteria to Section: <strong>Technical</strong></div>
<div class="card-body">
<form id="criteriaForm">
<div class="mb-3">
<label class="form-label">Criterion Name</label>
<input type="text" class="form-control" placeholder="e.g. Experience, Compliance, Methodology">
</div>
<div class="mb-3">
<label class="form-label">Description</label>
<textarea class="form-control" rows="2" placeholder="What will be evaluated?"></textarea>
</div>
<button type="submit" class="btn btn-success">Save Criterion</button>
</form>
</div>
</div>
@endsection