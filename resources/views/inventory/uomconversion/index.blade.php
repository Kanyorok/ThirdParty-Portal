@extends('layouts.app')
@section('title', 'UOM Conversion Mapping Management')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
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
                                    <a href="{{ route('uomconversion.show', $conversion->Id) }}"
                                       class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('uomconversion.edit', $conversion->Id) }}"
                                       class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('uomconversion.destroy', $conversion->Id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this UOM conversion?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
