@extends('layouts.app')
@section('title', 'insurancepolicymanagement')
@section('content')
<div class="container mt-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h4>Policy Registration Dashboard</h4>
<a href="{{ route('insurancepolicymanagement.create') }}" class="btn btn-sm btn-success">+ ADD  New Insurance Policy
Provider</a>
</div>

<!-- Main Content -->
<div class="container">

  <!-- Header Section -->
  <div class="header-text">Welcome to Insurance Policy Registration</div>
  <p class="text-muted">You can register new insurance policies or view all registered policies here.</p>

  <!-- Card for Registering New Policy -->
  <div class="card">
    <div class="card-header">
      <i class="fas fa-plus-circle icon"></i> Add New Insurance Policy
    </div>
    <div class="card-body">
      <p>Click the button below to register a new insurance policy.</p>
      <a href="create.html" class="btn btn-primary">Register New Policy</a>
    </div>
  </div>

  <!-- Card for Viewing Registered Policies -->
  <div class="card">
    <div class="card-header">
      <i class="fas fa-eye icon"></i> View Registered Policies
    </div>
    <div class="card-body">
      <p>Click the button below to view all the insurance policies that have been registered.</p>
      <a href="view.html" class="btn btn-outline-secondary">View Policies</a>
    </div>
  </div>

</div
@endsection

