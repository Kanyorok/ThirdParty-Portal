@extends('layouts.app')
@section('title', 'Tax Type Setup')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <!-- Header -->
        <div class="card-header bg-light px-3 py-2 d-flex justify-content-between mb-1">
            <h4 class="text-info mb-0"><i class="fas fa-tags"></i> Tax Type Setup</h4>
            <div>
                <a href="{{ route('taxtypes.create') }}" class="btn btn-info">
                    <i class="fas fa-plus me-1"></i> Add Tax Type
                </a>
            </div>
    </div>

        <!-- Body -->
        <div class="card-body">
            <!-- Validation & Flash Messages -->
            @if ($errors->any())
                <div class="alert alert-danger rounded-3 shadow-sm">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger rounded-3 shadow-sm">{{ session('error') }}</div>
            @endif

            <p class="text-muted">
                Manage your tax types here. You can add, edit, or delete tax types as needed.
            </p>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle text-center">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Tax Type Name</th>
                        <th>Description</th>
                        <th style="white-space: nowrap;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($taxTypes->count())
                        @foreach($taxTypes as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->TaxTypeName ?? '-' }}</td>
                                <td>{{ $item->Description ?? '-' }}</td>
                                <td style="white-space: nowrap;">
                                    <a href="{{ route('taxtypes.edit', $item->Id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{ $item->TaxTypeName }}"
                                            data-route="{{ route('taxtypes.destroy', $item->Id) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="p-0">
                                <div class="text-center p-4 border rounded-3 bg-light">
                                    <p class="mb-3 text-muted fs-5">
                                        <i class="fas fa-info-circle me-2 text-info"></i>
                                        <i>No Tax Types found.</i>
                                    </p>
                                    <a href="{{ route('taxtypes.create') }}" class="btn btn-info px-4 py-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Tax Type
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
        </div>
    </div>
</div>

    @include('components.modals.delete-confirm')
@endsection
