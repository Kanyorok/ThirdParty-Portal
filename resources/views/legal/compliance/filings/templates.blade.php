@extends('layouts.app')
@section('title','Filing Templates')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <div class="d-flex justify-content-between mb-3">
            <h4>📑 Filing Templates</h4>
            <a href="{{ route('legal.compliance.filings.templates.create') }}" class="btn btn-sm btn-primary">➕ Add
                Template</a>
        </div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Regulator</th>
                <th>Format</th>
                <th>Frequency</th>
                <th>Due Day</th>
            </tr>
            </thead>
            <tbody>
            @foreach($templates as $t)
                <tr>
                    <td>{{ $t->Name }}</td>
                    <td>{{ $t->type->Name ?? '-' }}</td>
                    <td>{{ $t->regulator->Name ?? '-' }}</td>
                    <td>{{ $t->format->Name ?? '-' }}</td>
                    <td>{{ $t->Frequency ?? '-' }}</td>
                    <td>{{ $t->DueDay ?? '-' }}</td>
                    <td>
                        @if($t->PortalURL)
                            <a href="{{ $t->PortalURL }}" target="_blank" class="btn btn-sm btn-outline-primary">🔗
                                Portal</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
