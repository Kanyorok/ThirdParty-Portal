@extends('layouts.app')
@section('title', 'Loan Security Registry')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center mb-1">
        <h4 class="text-info mb-0"><i class="fas fa-lock"></i> Loan Security / Collateral Registry</h4>
        <a href="{{ route('legal.securities.create') }}" class="btn btn-info"><i class="fas fa-plus me-1"></i> Register Security</a>
    </div>
    <div class="card-body">
        <p class="text-muted">List of registered loan securities and collateral items.</p>
        <table class="table table-hover table-sm align-middle text-centre"
               style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <thead>
                <tr>
                    <th>Security Type</th>
                    <th>Owner Name</th>
                    <th>Loan A/C</th>
                    <th>Status</th>
                    <th>Institution</th>
                    <th>Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($securities->count())
                @foreach ($securities as $sec)
                    <tr>
                        <td>{{ $sec->SecurityType }}</td>
                        <td>{{ $sec->OwnerName }}</td>
                        <td>{{ $sec->LoanAccountNumber }}</td>
                        <td><span class="text-success">{{ $sec->SecurityStatus }}</span></td>
                        <td>{{ $sec->Institution }}</td>
                        <td>{{ number_format($sec->Value, 2) }}</td>
                        <td>
                            {{-- <a href="{{ route('legal.securities.edit', $sec->ID) }}" class="btn btn-sm btn-warning">✏️ Edit</a> --}}
                            <a href="{{ route('legal.securities.show', $sec->Id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('legal.securities.edit', $sec->Id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>

                            <button type="button"
                                class="btn btn-sm btn-danger custom-delete-btn"
                                 data-bs-toggle="modal"
                                data-bs-target="#customDeleteConfirmModal"
                                data-name="{{$sec->SecurityType}}"    {{-- Pass item name --}}
                                data-route="{{ route('legal.securities.destroy', $sec->Id) }}">
                                <i  class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-muted">
                            <div class="text-centre p-4 border rounded-3 bg-light">
                                <p class="mb-3 text-muted fs-5">
                                    <i class="fas fa-info-circle me-2 text-info"></i>
                                    <i>No securities registered.</i>
                                </p>
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
