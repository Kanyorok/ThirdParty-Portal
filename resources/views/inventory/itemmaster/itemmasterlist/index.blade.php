@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')

<body class="bg-light p-4">

  <div class="container bg-white shadow-sm rounded p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>📋 Item Master List</h4>
      <a href="{{ route('itemmasterlist.create') }}" class="btn btn-success">➕ Add New Item</a>
    </div>

    <div class="mb-3">
      <input type="text" class="form-control" placeholder="🔍 Search by Item Code, Name, or Category">
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle" id="itemMasterListTbl">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Item Code</th>
            <th>Bar Code</th>
            <th>Item Name</th>
            <th>Item Type</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {{--
          <!-- @foreach($items as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->ItemCode }}</td>
            <td>{{ $item->BarCode }}</td>
            <td>{{ $item->ItemName }}</td>
            <td>{{ $item->ItemType }}</td>
            <td>{{ $item->category->Name }}</td>
            <td>{{ $item->subcategory->SubCategoryName }}</td>
            <td>{{ $item->UOM }}</td>
            <td>{{ $item->InventoryType }}</td>
            <td>
              <a href="{{ route('itemmasterlist.show', $item->Id) }}">View</a> |
              <a href="{{ route('itemmasterlist.edit', $item->Id) }}">Edit</a> |
              <a href="#" onclick="confirmDelete('{{ $item->Id }}')">Delete</a>

              <form id="delete-form-{{ $item->Id }}" action="{{ route('itemmasterlist.destroy', $item->Id) }}" method="POST" style="display:none;">
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

            </td>
          </tr>
          @endforeach -->--}}
        </tbody>
      </table>

    </div>
  </div>
@endsection
@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        let itemMasterListTbl = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchItemMasterListTbl();
        });

        function fetchItemMasterListTbl() {
            if (itemMasterListTbl === null) {
                itemMasterListTbl = $('#itemMasterListTbl').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    //"order": [[6, 'desc']],
                    ajax: {
                        url: getDocumentUrl(),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', orderable: false, searchable: false},
                        {data: 'ItemCode', name: 'ItemCode'},
                        {data: 'BarCode', name: 'BarCode'},
                        {data: 'ItemName', name: 'ItemName'},
                        {data: 'ItemType', name: 'ItemType'},
                        {data: 'Action', name: 'Action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no items found here"
                    }
                });

                itemMasterListTbl.on('error', function (er) {
                    nWarning("an issue occurred while loading items.");
                    console.log(er);
                });
            } else {
                itemMasterListTbl.ajax.reload();
            }
        }
    </script>
@endsection
