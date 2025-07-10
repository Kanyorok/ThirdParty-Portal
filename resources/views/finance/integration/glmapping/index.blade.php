@extends('layouts.app')
@section('title', 'GL Posting Map')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📄 GL Posting Map</h4>

        <a href="{{ route('glpostingmap.create') }}" class="btn btn-primary mb-3">➕ Add New Mapping</a>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Module</th>
                <th>Source Document</th>
                <th>Transaction Type</th>
                <th>Debit GL</th>
                <th>Credit GL</th>
                <th>Narration</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($mappings as $map)
                <tr>
                    <td>{{ $map->Module }}</td>
                    <td>{{ $map->SourceDocType }}</td>
                    <td>{{ $map->TransactionCode }} — {{ $map->TransactionDescription }}</td>
                    <td>{{ $map->DebitGL }}</td>
                    <td>{{ $map->CreditGL }}</td>
                    <td>{{ $map->PostingNarration }}</td>
                    <td>
                        <a href="{{ route('glpostingmap.edit', $map->Id) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No mappings found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
