@extends('layouts.app')
@section('title', 'Stock Consumptions')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
 @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Issue Stock To Users/Departments</h4>
        <a href="{{ route('stockconsumption.create') }}" class="btn btn-primary">Add New</a>
    </div>

    <div class="table-responsive">
        <table id="consumptionTable" class="table table-bordered table-striped align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Consumption No</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>UOM</th>
                <th>Branch</th>
                <th>Issued To</th>
                <th>Issued By</th>
                <th>Issued On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consumptions as $consumption)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $consumption->ConsumptionNo }}</td>
                <td>{{ optional($consumption->item)->ItemName }}</td>
                <td>{{ $consumption->Quantity }}</td>
                <td>{{ optional($consumption->uom)->Name }}</td>
                <td>{{ optional($consumption->branch)->Name }}</td>
                <td>{{ $consumption->issued_to_name }}</td>
                <td>{{ optional($consumption->issuedBy)->Name }}</td>
                <td>{{ \Carbon\Carbon::parse($consumption->IssuedOn)->format('m/d/Y') }}</td>
                 <td>{{ $consumption->Actions }}
                                <a href="{{ route('stockconsumption.show', $consumption->Id) }}"
                                   class="btn btn-secondary btn-sm">View</a>
                                <a href="{{ route('stockconsumption.edit', $consumption->Id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <a href="#" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $consumption->Id }}')">Delete</a>
                                <form id="delete-form-{{ $consumption->Id }}"
                                      action="{{ route('stockconsumption.destroy', $consumption->Id) }}" method="POST"
                                      style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                <script>
                                    function confirmDelete(Id) {
                                        if (confirm('⚠️ Are you sure you want to delete this store?')) {
                                            document.getElementById('delete-form-' + Id).submit();
                                        }
                                    }
                                </script>
                        </tr>
            @endforeach
        </tbody>
    </table>
</div>
   @section('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                @if(!$consumptions->isEmpty())
                $('#consumptionTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    lengthChange: true,
                    language: {
                        emptyTable: ""
                    }
                });
                @endif
            });
        </script>
    @endsection

@endsection
