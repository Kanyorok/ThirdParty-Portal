@extends('layouts.app')
@section('title','Certifications')

@section('content')
<div class="card shadow rounded-4 p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>📜 Staff Certifications</h4>
        <a href="{{ route('legal.compliance.certifications.create') }}" class="btn btn-sm btn-primary">➕ Add Certification</a>
    </div>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <table class="table table-bordered">
        <thead><tr><th>Staff</th><th>Certification</th><th>Issue Date</th><th>Expiry Date</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($certs as $c)
            <tr @if($c->ExpiryDate && \Carbon\Carbon::parse($c->ExpiryDate)->isPast()) class="table-danger" @endif>
                <td>{{ $users[$c->UserID] ?? 'Unknown' }}</td>
                <td>{{ $c->CertificationName }}</td>
                <td>{{ $c->IssueDate }}</td>
                <td>{{ $c->ExpiryDate }}</td>
                <td>{{ $c->Status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
