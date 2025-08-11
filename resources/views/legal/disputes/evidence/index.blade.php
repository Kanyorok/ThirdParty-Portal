@extends('layouts.app')
@section('title', 'Evidence for ' . $case->CaseTitle)

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>📂 Evidence for Case: {{ $case->CaseTitle }}</h4>
        <a href="{{ route('legal.cases.evidence.create', $case->ID) }}" class="btn btn-primary">➕ Link New Evidence</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Evidence Title</th>
                <th>Description</th>
                <th>DMS Doc ID</th>
                <th>External Link</th>
                <th>Uploaded On</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($evidence as $item)
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
                </tr>
            @empty
                <tr><td colspan="5">No evidence linked to this case yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
