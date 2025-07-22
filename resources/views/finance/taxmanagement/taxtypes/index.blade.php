@extends('layouts.app')
@section('title', 'Tax Type Setup')
@section('content')


<div class="container mt-4">
    <div class="card p-4">
        <div class="card-header bg-dark text-white">
            List of Tax Types
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <p class="text-muted mt-0">
                Manage your tax types here. You can add, edit, or delete tax types as needed.
            </p>
            
            <div class="mb-3 text-end">
                <a href="{{ route('taxtypes.create') }}" class="btn btn-primary">+ Add Tax Type</a>
            </div>
            
            @if($taxTypes->count()) 
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Tax Type Name</th>
                            <th>Description</th>
                            <th style="white-space: nowrap; text-align: center;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($taxTypes as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->TaxTypeName ?? '-' }}</td>
                                <td>{{ $item->Description ?? '-' }}</td>
                                <td style="white-space: nowrap; text-align: center;">
                                    {{-- Edit button --}}
                                    <a href="{{ route('taxtypes.edit', $item->Id) }}" class="btn btn-sm btn-warning">Edit</a> 
                                    {{-- Delete button with modal confirmation --}}
                                    <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{$item->TaxTypeName}}"    {{--Pass item name--}}
                                        data-route="{{ route('taxtypes.destroy', $item->Id) }}"> {{--Pass delete route--}}
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center">
                    No tax types found. Please add a new tax type.
                </div>
            @endif
        </div>
    </div>
</div>
@include('components.modals.delete-confirm')
@endsection