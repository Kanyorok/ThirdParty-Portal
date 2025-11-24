@extends('layouts.app')
@section('title','Add Training')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">➕ Add Training Session</h4>
    <form method="POST" action="{{ route('legal.compliance.trainings.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Topic *</label>
                <input type="text" name="Topic" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Type</label>
                <select name="TrainingTypeID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($types as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Facilitator</label>
                <input type="text" name="Facilitator" class="form-control">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Session Date *</label>
                <input type="date" name="SessionDate" class="form-control" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Duration</label>
                <input type="text" name="Duration" class="form-control" placeholder="e.g. 2 hours">
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">Training Materials</label>
                <input type="file" name="Materials" class="form-control">
            </div>
        </div>
        <button class="btn btn-success">💾 Save</button>
        <a href="{{ route('legal.compliance.trainings.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
