@extends('layouts.app')

@section('title', 'Training Certificates')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Training Certificates</h2>
        <a class="btn btn-outline-primary" href="{{ route('crm.training.certificate-templates.index') }}">Manage Templates</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach($statusList as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Client ID</th>
                            <th>Program</th>
                            <th>Scope</th>
                            <th>Certificate</th>
                            <th>Template</th>
                            <th>Issued On</th>
                            <th>Expires On</th>
                            <th>Status</th>
                            <th class="text-end">Document</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificates as $certificate)
                            <tr>
                                <td>{{ $certificate->client?->Name ?? '-' }}</td>
                                <td>{{ $certificate->ClientID }}</td>
                                <td>{{ $certificate->program?->Title ?? $certificate->session?->program?->Title ?? '-' }}</td>
                                <td>{{ $certificate->CertificateScope === 'Program' ? 'Program' : 'Session' }}</td>
                                <td>{{ $certificate->CertificationName }}</td>
                                <td>{{ $certificate->template?->Name ?? '-' }}</td>
                                <td>{{ $certificate->IssuedOn?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $certificate->ExpiresOn?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $certificate->ComputedStatus ?? $certificate->Status }}</td>
                                <td class="text-end">
                                    @if($certificate->document)
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('file.preview', ['document' => $certificate->document->DocumentId]) }}" target="_blank">View / Print</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted">No certificates found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $certificates->links() }}
    </div>
</div>
@endsection
