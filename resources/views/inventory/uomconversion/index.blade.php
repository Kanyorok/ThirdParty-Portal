@extends('layouts.app')
@section('title', 'UOM Conversion Mapping Management')
@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
{{-- Font Awesome for icons --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
@section('content')
<div class="container py-4">

    <!-- UOM Conversion List Header + Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>UOM Conversions List</h4>
        <a href="{{ route('uomconversion.create') }}" class="btn btn-primary">Add New UOM Conversion</a>
    </div>

    <!-- UOM Conversion Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="conversionsTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>UOM No</th>
                            <th>Item</th>
                            <th>Base UOM</th>
                            <th>Alternate UOM</th>
                            <th>Conversion Factor</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uomConversions as $conversion)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $conversion->UOMNo }}</td>
                            <td>{{ $conversion->item->ItemName ?? 'Null' }}</td>
                            <td>{{ $conversion->uom->Name ?? 'Null' }}</td>
                            <td>{{ $conversion->alternateUOM->Name ?? 'Null' }}</td>
                            <td>{{ $conversion->ConversionFactor ?? 'Null' }}</td>
                            <td>{{ $conversion->Remarks ?? 'Null' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('uomconversion.show', $conversion->Id) }}"
                                        class="btn btn-sm btn-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('uomconversion.edit', $conversion->Id) }}"
                                        class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('uomconversion.destroy', $conversion->Id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this UOM conversion?')"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#conversionsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                emptyTable: "No UOM conversions found"
            }
        });
    });
</script>
@endsection