@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')
<body class="bg-light p-4">

  <div class="container bg-white shadow-sm rounded p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>📋 Item Master List</h4>
      <a href="{{ route('itemmaster.create') }}" class="btn btn-success">➕ Add New Item</a>
    </div>

    <div class="mb-3">
      <input type="text" class="form-control" placeholder="🔍 Search by Item Code, Name, or Category">
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Item Code</th>
            <th>Bar Code</th>
            <th>Item Name</th>
            <th>Item Type</th>
            <th>Category</th>
            <th>Subcategory</th>
            <th>UOM</th>
            <th>Inventory Type</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>ITM-001</td>
            <td>1234567890123</td>
            <td>Toner Cartridge</td>
            <td>Stock</td>
            <td>Office Supplies</td>
            <td>Ink & Toners</td>
            <td>pcs</td>
            <td>Consumable</td>
            <td>
              <a href="#" class="btn btn-sm btn-primary">🔍 View</a>
              <a href="#" class="btn btn-sm btn-secondary">✏️ Edit</a>
            </td>
          </tr>
          <tr>
            <td>2</td>
            <td>ITM-002</td>
            <td>9876543210987</td>
            <td>A4 Paper (Ream)</td>
            <td>Stock</td>
            <td>Stationery</td>
            <td>Paper Products</td>
            <td>pcs</td>
            <td>Consumable</td>
            <td>
              <a href="#" class="btn btn-sm btn-primary">🔍 View</a>
              <a href="#" class="btn btn-sm btn-secondary">✏️ Edit</a>
            </td>
          </tr>
          <!-- Add more rows as needed -->
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection