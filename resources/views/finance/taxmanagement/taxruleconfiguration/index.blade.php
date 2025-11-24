@extends('layouts.app')
@section('title', 'Tax Rules Management')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="card-header bg-light px-3 py-2 d-flex justify-content-between mb-1">
            <h4 class="text-info mb-0"><i class="fas fa-receipt"></i> Tax Rules Management</h4>
            <div>
                <a href="{{ route('taxruleconfig.create') }}" class="btn btn-info">
                    <i class="fas fa-plus me-1"></i> Add Tax Rule
                </a>
        </div>
        </div>

        <div class="card-body">
            <p class="text-muted">Manage tax rules for your organization.</p>

            <table class="table table-hover table-sm align-middle text-center"
                   style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Tax Type</th>
                    <th>Jurisdiction</th>
                    <th>Rate (%)</th>
                    <th>Applies To</th>
                    <th>Threshold</th>
                    <th>Effective From</th>
                    <th>Effective To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @if ($taxRule->count())
                    @foreach($taxRule as $rule)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $rule->taxType->TaxTypeName ?? '-' }}</td>
                            <td>{{ $rule->jurisdiction->JurisdictionName ?? '-' }}</td>
                            <td>{{ $rule->Rate }}</td>
                            <td>{{ $rule->AppliesTo }}</td>
                            <td>{{ $rule->ThresholdAmount }}</td>
                            <td>{{ $rule->EffectiveFrom }}</td>
                            <td>{{ $rule->EffectiveTo }}</td>
                            <td>
                                @if ($rule->Status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="#" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('taxruleconfig.edit', $rule->Id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{ $rule->taxType->TaxTypeName }}"
                                        data-route="{{ route('taxruleconfig.destroy', $rule->Id) }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="p-0">
                            <div class="text-center p-4 border rounded-3 bg-light">
                                <p class="mb-3 text-muted fs-5">
                                    <i class="fas fa-info-circle me-2 text-info"></i>
                                    <i>No Tax Rules found.</i>
                                </p>
                                <a href="{{ route('taxruleconfig.create') }}" class="btn btn-info px-4 py-2">
                                    <i class="fas fa-plus-circle me-2"></i> Add Tax Rule
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
