@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="card mb-4">
<div class="card-header bg-light">📊 Criteria under Section: <strong>Technical</strong></div>
<div class="card-body">
<table class="table table-striped">
<thead>
<tr>
<th>#</th>
<th>Criterion</th>
<th>Description</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<tr>
<td>1</td>
<td>Experience</td>
<td>Years and quality of relevant past projects</td>
<td>
<a href="#" class="btn btn-sm btn-outline-danger">Remove</a>
</td>
</tr>
<!-- More rows -->
</tbody>
</table>
</div>
</div>
@endsection