@extends('layouts.app')
@section('title', 'Tax Jurisdictions')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <!-- Header -->
        <div class="card-header bg-light px-3 py-2 d-flex justify-content-between mb-1">
            <h4 class="text-info mb-0"><i class="fas fa-globe"></i> Tax Jurisdictions</h4>
            <div>
                <a href="{{ route('taxjurisdiction.create') }}" class="btn btn-info">
                    <i class="fas fa-plus me-1"></i> Add Jurisdiction
                </a>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body">
            <p class="text-muted">Manage tax jurisdictions for your organization.</p>

            <table class="table table-hover table-sm align-middle text-center"
                   style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Jurisdiction</th>
                    <th>Currency</th>
                    <th>Tax Authority</th>
                    <th>Status</th>
                    <th style="white-space: nowrap;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @if ($taxJurisdictions->count())
                    @foreach($taxJurisdictions as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->JurisdictionName ?? '-' }}</td>
                            <td>{{ $item->currency->Code ?? '-' }}</td>
                            <td>{{ $item->TaxAuthority ?? '-' }}</td>
                            <td>
                                @if ($item->Status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td style="white-space: nowrap;">
                                <a href="{{ route('taxjurisdiction.edit', $item->Id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{ $item->JurisdictionName }}"
                                        data-route="{{ route('taxjurisdiction.destroy', $item->Id) }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="p-0">
                            <div class="text-center p-4 border rounded-3 bg-light">
                                <p class="mb-3 text-muted fs-5">
                                    <i class="fas fa-info-circle me-2 text-info"></i>
                                    <i>No Tax Jurisdictions found.</i>
                                </p>
                                <a href="{{ route('taxjurisdiction.create') }}" class="btn btn-info px-4 py-2">
                                    <i class="fas fa-plus-circle me-2"></i> Add Jurisdiction
                                </a>
                            </div>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

@include('components.modals.delete-confirm')
@endsection
