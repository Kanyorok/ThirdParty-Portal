@extends('layouts.app')
@section('title', 'provider management')
@section('content')
<div class="container mt-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h4>Insurance Policies</h4>
<a href="{{ route('providermanagement.create') }}" class="btn btn-sm btn-success">+ New Policy</a>
</div>
 
    <div class="table-responsive">
<table class="table table-bordered table-striped">
<thead class="table-light">
<tr>
<th>#</th>
<th>Policy No</th>
<th>Name</th>
<th>Type</th>
<th>Provider</th>
<th>Premium</th>
<th>Start</th>
<th>End</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<!-- Sample Row -->
<tr>
<td>1</td>
<td>POL-00123</td>
<td>Asset Cover</td>
<td>Asset</td>
<td>APA Insurance</td>
<td>KES 25,000</td>
<td>2025-01-01</td>
<td>2025-12-31</td>
<td><span class="badge bg-success">Active</span></td>
<td>
<button class="btn btn-sm btn-primary">Edit</button>
<button class="btn btn-sm btn-danger">Delete</button>
</td>
</tr>
<!-- Repeat rows dynamically -->
</tbody>
</table>
</div>
</div>

@endsection