@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')

<body class="bg-light p-4">

  <div class="container bg-white shadow-sm rounded p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>📂 Item Category List</h4>
      <a href="{{ route('itemcategory.create') }}" class="btn btn-success">➕ Add New Category</a>
    </div>

    <div class="mb-3">
      <input type="text" class="form-control" placeholder="🔍 Search by Category Code or Name">
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Category Code</th>
            <th>Category Name</th>
            <th>Description</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($items as $key => $item)
          <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $item->CategoryCode }}</td>
            <td>{{ $item->Name }}</td>
            <td>{{ $item->Description }}</td>
            <td>
              <span class="badge {{ $item->Status ? 'bg-success' : 'bg-warning' }}">
              {{ $item->Status ? 'Active' : 'Inactive' }}
              </span>
</td>

            <td>
              <a href="{{ route('itemcategory.show', $item->id) }}">View</a> |
              <a href="{{ route('itemcategory.edit', $item->id) }}">Edit</a> |
              <a href="#" onclick="confirmDelete('{{ $item->id}}')">Delete</a>

              <form id="delete-form-{{ $item->id}}" action="{{ route('itemcategory.destroy', $item->id) }}" method="POST" style="display:none;">
               @csrf
               @method('DELETE')
              </form>

              <script>
              function confirmDelete(id) {
              if (confirm('⚠️ Are you sure you want to delete this item?')) {
               document.getElementById('delete-form-' + id).submit();
               }
               }
               </script>

            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>


@endsection
