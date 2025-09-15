@extends('layouts.app')
@section('title', 'Compliance Tasks')
@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Compliance Tasks for Obligation: {{ $obligation->Title }}</h5>
            <a href="{{ route('legal.compliance.tasks.create', $obligation->Id) }}" class="btn btn-light btn-sm">+ New Task</a>
        </div>
        <div class="card-body">
            @if($tasks->isEmpty())
                <p>No tasks added for this obligation yet.</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Task</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Responsible</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                        <tr>
                            <td>{{ $task->TaskDescription }}</td>
                            <td>{{ \Carbon\Carbon::parse($task->DueDate)->format('d M Y') }}</td>
                            <td>{{ $task->Status }}</td>
                            <td>{{ $task->ResponsiblePerson }}</td>
                            <td>
                                <a href="{{ route('legal.compliance.tasks.edit', [$obligation->Id, $task->Id]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
