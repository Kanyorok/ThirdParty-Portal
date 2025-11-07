@extends('layouts.app')
@section('title', 'Create New Store')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
@section('content')
    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header text-dark rounded-top-4 d-flex justify-content-between align-items-center"
                 style="background-color: #add8e6;">
                <h4 class="mb-0">Stores List</h4>
                <a href="{{ route('stores.create') }}" class="btn btn-success">➕ Add New Store</a>
            </div>
            <div class="card-body">

                <table id="storesTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Store ID</th>
                        <th>Store Name</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($stores as $key => $store)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $store->StoreID }}</td>
                            <td>{{ $store->StoreName }}</td>
                            <td>{{ $store->branch->Name }}</td>
                            <td>
                                <span class="badge {{ $store->Status ? 'bg-success' : 'bg-warning' }}">
                                    {{ $store->Status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('stores.show', $store->Id) }}"
                                       class="btn btn-sm btn-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('stores.edit', $store->Id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete('{{ $store->Id }}')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $store->Id }}"
                                          action="{{ route('stores.destroy', $store->Id) }}" method="POST"
                                          style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#storesTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                language: {
                    emptyTable: "No stores found"
                }
            });
        });

        function confirmDelete(Id) {
            if (confirm('⚠️ Are you sure you want to delete this store?')) {
                document.getElementById('delete-form-' + Id).submit();
            }
        }
    </script>
@endsection