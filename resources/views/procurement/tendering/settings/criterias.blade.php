@extends('layouts.app')

@section('title', 'Criteria Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-primary">Criteria for Section: {{ $criterias->first()?->section->SectionName ?? 'N/A' }}</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCriteriaModal">
            <i class="bi bi-plus"></i> Add Criteria
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
            @foreach($criterias as $criteria)
            <tr>
                <td>{{ $criteria->CriteriaName }}</td>
                <td>{{ $criteria->Description ?? '-' }}</td>
                <td>
                    <a href="{{ route('criteria.edit', $criteria->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('criteria.destroy', $criteria->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@include('procurement.tendering.settings.partials._create_criteria_modal')
@endsection