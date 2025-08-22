@extends('layouts.app')
@section('title', 'insurancetypemanagement')
@section('content')
  <div class="header-text"><i class="fas fa-plus-circle icon"></i> Register New Insurance Type</div>
  <form>
    <!-- Insurance Type Name -->
    <div class="mb-3">
      <label for="typeName" class="form-label">Insurance Type Name</label>
      <input type="text" class="form-control" id="typeName" placeholder="e.g. Property, Health, Life" required>
    </div>

    <!-- Description -->
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea class="form-control" id="description" rows="4" placeholder="Brief description of the insurance type..." required></textarea>
    </div>

    <!-- Status -->
    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" required>
        <option selected disabled>Choose status</option>
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
      </select>
    </div>

    <!-- Action Buttons -->
    <div class="text-center">
      <button type="submit" class="btn btn-success">Save Insurance Type</button>
      <a href="index.html" class="btn btn-outline-secondary ms-3">
        <i class="fas fa-arrow-left"></i> Back to List
      </a>
    </div>
  </form>
</div>

@endsection