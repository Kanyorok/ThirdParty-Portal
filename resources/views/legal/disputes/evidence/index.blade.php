@extends('layouts.app')
@section('title', 'Evidence for ' . $case->CaseTitle)

@section('content')
<div class="card p-4 shadow rounded-4 border-0 mb-0">
    <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center mb-1">
        <h4 class="text-info mb-0"><i class="fas fa-folder-open text-warning"></i> Evidence(s)</h4>
        <a href="{{ route('legal.cases.evidence.create', $case->Id) }}" class="btn btn-info">
            <i class="fas fa-plus me-1"></i> Link New Evidence
        </a>
    </div>

    <div class="card-body">
        <p class="text-muted">A list of all evidence linked to this legal case.</p>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle text-centre"
                style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                <thead>
                    <tr>
                        <th>Evidence Title</th>
                        <th>Description</th>
                        <th>DMS Doc ID</th>
                        <th>External Link</th>
                        <th>Uploaded On</th>
                        <td>Is Active</td>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($evidence->count())
                        @foreach ($evidence as $item)
                            <tr>
                                <td>{{ $item->EvidenceTitle }}</td>
                                <td>{{ $item->Description }}</td>
                                <td>{{ $item->DMSDocumentID ?? '-' }}</td>
                                <td>
                                    @if($item->ExternalLink)
                                        <a href="{{ $item->ExternalLink }}" target="_blank">View</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->UploadedOn)->format('d M Y H:i') }}</td>
                                <td>
                                    @if($item->IsActive === 'Active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('legal.cases.evidence.show',[$case->Id, $item->Id ]) }}"
                                    class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('legal.cases.evidence.edit', [$case->Id, $item->Id]) }}"
                                    class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                    <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{ $item->EvidenceTitle }}"
                                        data-route="{{ route('legal.cases.evidence.destroy', [$case->Id, $item->Id]) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="p-0">
                                <div class="text-center p-4 border rounded-3 bg-light">
                                    <p class="mb-3 text-muted fs-5">
                                        <i class="fas fa-info-circle me-2 text-info"></i>
                                        <i>No evidence linked to this case yet.</i>
                                    </p>
                                    <a  href="{{ route('legal.cases.evidence.create', $case->Id) }}"  class="btn btn-info px-4 py-2">
                                        <i class="fas fa-plus-circle me-2"></i> Link Evidence
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
