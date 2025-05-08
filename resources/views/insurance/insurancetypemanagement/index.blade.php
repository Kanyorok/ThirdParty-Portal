@extends('layouts.app')
@section('title', 'insurancetypemanagement')
@section('content')
<div class="container mt-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h4>Supported Insurance Types</h4>
<a href="{{ route('insurancetypemanagement.create') }}" class="btn btn-sm btn-success">+ ADD New insurance type </a>
</div>

<!-- Main Content -->
<div class="container">
  <!-- Header Section -->
  <div class="header-text">Explore Our Supported Insurance Types</div>
  <p class="text-muted">We offer a wide range of insurance products to protect your assets and health. Please find the available insurance types below:</p>

  <!-- Insurance Type Cards -->
  <div class="row">

    <!-- Property Insurance Card -->
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-home icon"></i> Property Insurance
        </div>
        <div class="card-body">
          <p>Property insurance covers damage or loss to your property due to various risks, including fire, theft, and natural disasters.</p>
          <a href="property.html" class="btn btn-outline-primary">Learn More</a>
        </div>
      </div>
    </div>

    <!-- Motor Insurance Card -->
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-car icon"></i> Motor Insurance
        </div>
        <div class="card-body">
          <p>Motor insurance provides protection for your vehicle against accidents, theft, and damages caused by natural events or third parties.</p>
          <a href="motor.html" class="btn btn-outline-primary">Learn More</a>
        </div>
      </div>
    </div>

    <!-- Health Insurance Card -->
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-heartbeat icon"></i> Health Insurance
        </div>
        <div class="card-body">
          <p>Health insurance helps cover the cost of medical expenses, including hospital stays, medications, and preventive care.</p>
          <a href="health.html" class="btn btn-outline-primary">Learn More</a>
        </div>
      </div>
    </div>

    <!-- Life Insurance Card -->
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-user-shield icon"></i> Life Insurance
        </div>
        <div class="card-body">
          <p>Life insurance provides financial support to your family and dependents in the event of your passing. It ensures their financial security.</p>
          <a href="life.html" class="btn btn-outline-primary">Learn More</a>
        </div>
      </div>
    </div>

    <!-- Other Insurance Types -->
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-umbrella icon"></i> Other Insurance
        </div>
        <div class="card-body">
          <p>We offer various other types of insurance products to suit your needs, such as travel, pet, and business insurance.</p>
          <a href="other.html" class="btn btn-outline-primary">Learn More</a>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection