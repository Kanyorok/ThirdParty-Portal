@extends('layouts.app')
@section('title', 'Tax Rules Management')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                📄 Tax Rules
            </div>

            
            <div class="card-body table-responsive">
                <p class="text-muted mt-0">
                    Manage tax rules for your organization.
                </p>

                <div class="text-end mt-0">
                    <a href="{{ route('taxruleconfig.create') }}" class="btn btn-primary">➕ Add Tax Rule</a>
                </div>
                @if ($taxRule->count())
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped">
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
                                <th style="white-space: nowrap; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                    <td style="white-space: nowrap; text-align: center;">
                                        <a href="{{ route('taxruleconfig.edit', $rule->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <button type="button"
                                            class="btn btn-sm btn-danger custom-delete-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{$rule->taxType->TaxTypeName}}"    {{-- Pass item name --}}
                                            data-route="{{ route('taxruleconfig.destroy', $rule->Id) }}"> {{-- Pass delete route --}}
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
                        No Tax Rules found
                    </div>
                @endif
            </div>
        </div>
    </div>
@include('components.modals.delete-confirm')
@endsection
                