@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="card mb-4">
<div class="card-header bg-primary text-white">➕ Add Evaluation Section</div>
<div class="card-body">
<form id="sectionForm">
<div class="mb-3">
<label class="form-label">Section Name</label>
<input type="text" class="form-control" placeholder="e.g. Technical, Financial, Legal">
</div>
<div class="mb-3">
<label class="form-label">Description</label>
<textarea class="form-control" rows="2" placeholder="Describe the purpose of this section"></textarea>
</div>
<button type="submit" class="btn btn-success">Save Section</button>
</form>
</div>
</div>
@endsection