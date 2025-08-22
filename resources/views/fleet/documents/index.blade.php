@extends('layouts.app')
@section('title', 'Vehicle Documents')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">📄 Vehicle Document Tracker</h4>
            <a href="{{ route('fleet.documents.create') }}" class="btn btn-primary">
                + Upload New Document
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Vehicle</th>
                    <th>Document Type</th>
                    <th>Number</th>
                    <th>Issue Date</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th>File</th>
                    <th>Notes</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($documents as $doc)
                    @php
                        $isExpired = $doc->ExpiryDate && \Carbon\Carbon::parse($doc->ExpiryDate)->isPast();
                    @endphp
                    <tr class="{{ $isExpired ? 'table-danger' : '' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $doc->vehicle->RegistrationNumber ?? '-' }}</td>
                        <td>{{ $doc->DocumentType }}</td>
                        <td>{{ $doc->DocumentNumber ?? '-' }}</td>
                        <td>{{ optional($doc->IssueDate)->format('d-M-Y') }}</td>
                        <td>{{ optional($doc->ExpiryDate)->format('d-M-Y') }}</td>
                        <td>
                            @if ($isExpired)
                                <span class="badge bg-danger">Expired</span>
                            @elseif ($doc->ExpiryDate)
                                <span class="badge bg-success">Valid</span>
                            @else
                                <span class="badge bg-secondary">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if ($doc->FilePath)
                                <a href="{{ asset('storage/' . $doc->FilePath) }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary">📥 Download</a>
                            @else
                                <span class="text-muted">No file</span>
                            @endif
                        </td>
                        <td>{{ $doc->Notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No documents uploaded yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
