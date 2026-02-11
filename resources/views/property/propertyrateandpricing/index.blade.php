@extends('layouts.app')
@section('title', 'Property Rate & Pricing List')

@section('content')

<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <small>This screen displays a list of all property rates and pricing records.</small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('propertyrateandpricing.bulk-create') }}" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-upload me-1"></i> Bulk Upload Pricing
            </a>

            <a href="{{ route('propertyrateandpricing.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Add Pricing
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">

            @if ($pricings->isEmpty())
                <div class="alert alert-info text-center">
                    No pricing records found.
                </div>
            @else

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle" id="propertyrate">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Property</th>
                            <th>Block</th>
                            <th>Floor</th>
                            <th>Unit</th>
                            <th>Rent</th>
                            <th>Deposit</th>
                            <th>Currency</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($pricings as $row)
                        <tr>
                            <td>{{ $loop->iteration}}</td>

                            <td>{{ optional($row->property)->PropertyName ?? '-' }}</td>
                            <td>{{ optional($row->block)->BlockName ?? '-' }}</td>
                            <td>{{ optional($row->floor)->FloorLabel ?? '-' }}</td>
                            <td>{{ optional($row->unit)->UnitCode ?? '-' }}</td>

                            <td>{{ number_format($row->Rent) }}</td>
                            <td>{{ number_format($row->DepositAmount) }}</td>

                            <td>{{ $row->currency->Code ?? '-' }}</td>

                            <td>

                                <a href="{{ route('propertyrateandpricing.show', $row->Id) }}" 
                                    class="btn btn-sm btn-info text-white">
                                <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('propertyrateandpricing.edit', $row->Id) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form action="{{ route('propertyrateandpricing.destroy', $row->Id) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this record?');">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            @endif

        </div>
    </div>

</div>
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#propertyrate').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection

@endsection
