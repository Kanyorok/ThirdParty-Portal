@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="card mb-4">
<div class="card-header bg-light">📁 Evaluation Section Master</div>
<div class="card-body">
<table class="table table-bordered">
<thead class="table-secondary">
<tr>
<th>#</th>
<th>Section Name</th>
<th>Description</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<tr>
<td>1</td>
<td>Technical</td>
<td>Assesses technical capacity and qualifications</td>
<td>
<a href="#" class="btn btn-sm btn-outline-primary">Add Criteria</a>
<a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
</td>
</tr>
<!-- More rows -->
</tbody>
</table>
</div>
</div>
@endsection