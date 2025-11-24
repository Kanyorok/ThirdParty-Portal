@extends('layouts.app')
@section('title', 'Assigned Staff')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between mb-3">
            <h4>👤 Assigned Staff</h4>
            <a href="{{ route('legal.obligations.assignments.create', $obligation->ID) }}" class="btn btn-primary">➕
                Assign Staff</a>
        </div>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Assignee Name</th>
                <th>Remarks</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($assignments as $a)
                <tr>
                    <td>{{ $a->AssigneeName }}</td>
                    <td>{{ $a->Remarks }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No staff assigned.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
