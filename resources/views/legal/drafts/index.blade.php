@extends('layouts.app')
@section('title', 'Legal Drafts')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>📝 Legal Drafts</h4>
        <a href="{{ route('legal.drafts.create') }}" class="btn btn-primary">➕ New Draft</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Draft Title</th>
                <th>Document Type</th>
                <th>Status</th>
                <th>Created On</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($drafts as $draft)
            <tr>
                <td>{{ $draft->DraftTitle }}</td>
                <td>{{ $draft->DocumentType }}</td>
                <td>{{ $draft->Status }}</td>
                <td>{{ \Carbon\Carbon::parse($draft->CreatedOn)->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('legal.drafts.show', $draft->ID) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('legal.drafts.edit', $draft->ID) }}" class="btn btn-sm btn-secondary">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
