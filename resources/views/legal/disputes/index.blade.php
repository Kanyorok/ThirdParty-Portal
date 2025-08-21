@extends('layouts.app')
@section('title', 'Legal Case Registry')

@section('content')
<div class="card p-4 shadow rounded-4 border-0 mb-0">
    <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-info mb-0"><i class="fas fa-balance-scale"></i> Legal Case Registry</h4>
        <a href="{{ route('legal.cases.create') }}" class="btn btn-info"><i class="fas fa-plus me-1"></i> New Legal Case</a>
    </div>

    <div class="card-body">
        <p class="text-muted">A centralized record of all legal cases, their details, and statuses.</p>
         @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <table class="table table-hover table-sm align-middle text-centre"
            style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <thead>
                <tr>
                    <th>Case Title</th>
                    <th>Court</th>
                    <th>Filing Date</th>
                    <th>Opposing Party</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($cases->count())
                @foreach($cases as $case)
                    <tr>
                        <td>{{ $case->CaseTitle }}</td>
                        <td>{{ $case->CourtName }}</td>
                        <td>{{ \Carbon\Carbon::parse($case->FilingDate)->format('d m Y') }}</td>
                        <td>{{ $case->OpposingParty }}</td>
                        <td>{{ $case->Status }}</td>
                        <td>
                            <a href="{{ route('legal.cases.evidence.index', $case->Id) }}" class="btn btn-sm btn-dark">📂 Evidence</a>
                            <a href="{{ route('legal.cases.show', $case->Id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('legal.cases.edit', $case->Id) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                            <button type="button"
                                class="btn btn-sm btn-danger custom-delete-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#customDeleteConfirmModal"
                                data-name="{{$case->CaseTitle}}"    {{-- Pass item name --}}
                                data-route="{{ route('taxruleconfig.destroy', $case->Id) }}">
                                <i  class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                @else
                    <tr>
                        <td colspan="6" class="p-0">
                            <div class="text-centre p-4 border rounded-3 bg-light">
                                <p class="mb-3 text-muted fs-5">
                                    <i class="fas fa-info-circle me-2 text-info"></i>
                                    <i>No cases found.</i>
                                </p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
