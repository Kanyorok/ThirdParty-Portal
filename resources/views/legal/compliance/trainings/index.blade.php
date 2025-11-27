@extends('layouts.app')
@section('title','Training Sessions')

@section('content')
<div class="card shadow rounded-4 p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>🎓 Training Sessions</h4>
        <a href="{{ route('legal.compliance.trainings.create') }}" class="btn btn-sm btn-primary">➕ Add Training</a>
    </div>
    @if(session('success')) 
        <div class="alert alert-success">{{ session('success') }}</div> 
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Topic</th>
                <th>Type</th>
                <th>Date</th>
                <th>Facilitator</th>
                <th>Duration</th>
                <th>Materials</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trainings as $t)
            <tr>
                <td>{{ $t->Topic }}</td>
                <td>{{ $t->TrainingTypeID }}</td>
                <td>{{ $t->SessionDate }}</td>
                <td>{{ $t->Facilitator }}</td>
                <td>{{ $t->Duration }}</td>
                <td>
                    @if($t->MaterialsFilePath)
                        <a href="{{ Storage::url($t->MaterialsFilePath) }}" target="_blank">{{ $t->MaterialsFileName }}</a>
                    @endif
                </td>
                <td>
                    <a href="{{ route('legal.compliance.trainings.show', $t->Id) }}" class="btn btn-sm btn-info">🔍 Show</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
