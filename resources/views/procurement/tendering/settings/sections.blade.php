@extends('layouts.app')

@section('title', 'Sections Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-primary">Sections</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSectionModal">
            <i class="bi bi-plus"></i> Add Section
        </button>
    </div>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sections as $section)
            <tr>
                <td>{{ $section->SectionName }}</td>
                <td>{{ $section->Description ?? '-' }}</td>
                <td>
                    <a href="{{ route('sections.edit', $section->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('sections.destroy', $section->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                    <a href="{{ route('criteria.show', $section->id) }}" class="btn btn-info btn-sm">Criteria</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@include('procurement.tendering.settings.partials._create_section_modal')
@endsection