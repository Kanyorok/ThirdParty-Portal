@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header bg-info text-white rounded-top-4 d-flex justify-content-between align-items-center">
      <h4 class="mb-0">📁 Item Sub Category List</h4>
      <a href="{{ route('itemsubcategory.create') }}" class="btn btn-sm btn-light">➕ Add New</a>
    </div>
    <div class="card-body">

      <table class="table table-striped table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>SubCategory Code</th>
            <th>SubCategory Name</th>
            <th>Parent Category</th>
            <th>Description</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
         @foreach($items as $key => $item)
          <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $item->SubCategoryCode }}</td>
            <td>{{ $item->SubCategoryName }}</td>
            <td>{{ $item->ParentCategory }}</td>
            <td>{{ $item->Description }}</td>
            <td>{{ $item->Status }}</td>
            <td>{{ $item->Actions }}</td>
            <td>
              <a href="{{ route('itemsubcategory.show', $item->Id) }}">View</a> |
              <a href="{{ route('itemsubcategory.edit', $item->Id) }}">Edit</a> |
              <a href="#" onclick="confirmDelete('{{ $item->Id }}')">Delete</a>

              <form id="delete-form-{{ $item->Id }}" action="{{ route('itemsubcategory.destroy', $item->Id) }}" method="POST" style="display:none;">
               @csrf
               @method('DELETE')
              </form>

              <script>
              function confirmDelete(Id) {
              if (confirm('⚠️ Are you sure you want to delete this item?')) {
               document.getElementById('delete-form-' + Id).submit();
               }
               }
               </script>
            </tr>
            @endforeach   
        </tbody>
      </table>

    </div>
  </div>
</div>

@endsection