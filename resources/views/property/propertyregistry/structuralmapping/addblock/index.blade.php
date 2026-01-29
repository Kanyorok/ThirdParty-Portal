@extends('layouts.app')
@section('title', 'Property Blocks')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <small>This screen displays a list of all Blocks per properties.</small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('addblock.bulkCreate') }}" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-upload me-1"></i> Bulk Upload Blocks
            </a>

            <a href="{{ route('addblock.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add Block
            </a>
        </div>
    </div>


    <p class="text-muted">
        <small>This screen displays a list of all registered property blocks.</small>
    </p>

    @if($blocks->count())
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="propertyblocks" class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th>Property</th>
                            <th>Block Name</th>
                            <th>Description</th>
                            <th style="width: 20%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($blocks as $block)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $block->property->PropertyName ?? '-' }}</td>
                                <td>{{ $block->BlockName ?? '-' }}</td>
                                <td>{{ $block->Description ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('addblock.edit', $block->Id) }}"
                                           class="btn btn-sm btn-warning"
                                           title="Edit Block">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        @if($block->floor()->exists())
                                            <button class="btn btn-sm btn-secondary" title="Block in Use">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('addblock.destroy', $block->Id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this block?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Block">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i> No property blocks registered yet.
        </div>
    @endif
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#propertyblocks').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
