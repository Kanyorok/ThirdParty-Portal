@extends('layouts.app')
@section('title','Regulatory Filings')

@section('content')
<div class="card shadow rounded-4 p-4">
<div class="d-flex justify-content-between mb-3">
    <h4>📂 Regulatory Filings</h4>
    <div>
        <a href="{{ route('legal.compliance.filings.templates') }}" class="btn btn-sm btn-outline-secondary me-2">⚙️ Manage Templates</a>
        <a href="{{ route('legal.compliance.filings.create') }}" class="btn btn-sm btn-primary">➕ Submit Filing</a>
    </div>
</div>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <table class="table table-bordered">
        <thead><tr><th>Template</th><th>Date</th><th>Status</th><th>File</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($filings as $f)
            <tr>
                <td>{{ $f->template->Name ?? '-' }}</td>
                <td>{{ $f->SubmissionDate }}</td>
                <td>{{ $f->Status }}</td>
                <td>
                    @if($f->FilePath)
                        <a href="{{ Storage::url($f->FilePath) }}" target="_blank">{{ $f->FileName }}</a>
                    @endif
                </td>
                <td>
                    <a href="{{ route('legal.compliance.filings.show',$f->Id) }}" class="btn btn-sm btn-info">🔍 View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
