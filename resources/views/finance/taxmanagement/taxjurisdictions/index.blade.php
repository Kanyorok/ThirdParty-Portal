@extends('layouts.app')
@section('title', 'Tax Jurisdictions')
@section('content')
    <div class="container mt-4">
        <div class="card p-4">
                <div class="card-header bg-dark text-white">
                    🌍 Tax Jurisdictions
                </div>

                <div class="card-body">
                    <p class="text-muted mt-0">
                        Manage tax jurisdictions for your organization.
                    </p>
                    
                    <div class="mb-3 text-end">
                        <a href="{{ route('taxjurisdiction.create') }}" class="btn btn-primary">➕ Add Jurisdiction</a>
                    </div>
                    @if ($taxJurisdictions->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Jurisdiction</th>
                                <th>Currency</th>
                                <th>Tax Authority</th>
                                <th>Status</th>
                                <th style="white-space: nowrap; text-align: center;">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                @foreach($taxJurisdictions as $item)
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->JurisdictionName ?? '-' }}</td>
                                    <td>{{ $item->currency->Code ?? '-' }}</td>
                                    <td>{{ $item->TaxAuthority ?? '-'}}</td>
                                    <td>
                                        @if ($item->Status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td style="white-space: nowrap; text-align: center;">
                                        <a href="{{ route('taxjurisdiction.edit', $item->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        {{-- <form action="{{ route('taxjurisdiction.destroy', $item->Id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this jurisdiction?')">Delete</button>
                                        </form> --}}
                                        <button type="button"
                                            class="btn btn-sm btn-danger custom-delete-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{$item->JurisdictionName}}"    {{-- Pass item name --}}
                                            data-route="{{ route('taxjurisdiction.destroy', $item->Id) }}"> {{-- Pass delete route --}}
                                            Delete
                                        </button>
                                    </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                  @else
                    <div class = "alert alert-info text-centre">
                        No Tax Jurisdictions found
                    </div>
                  @endif
            </div>
        </div>
    </div>
@include('components.modals.delete-confirm')
@endsection
