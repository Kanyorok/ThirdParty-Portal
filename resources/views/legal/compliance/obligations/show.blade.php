@extends('layouts.app')
@section('title', 'Obligation Details')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">📄 Obligation Details</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <p><strong>Title:</strong> {{ $obligation->Title }}</p>
    <p><strong>Description:</strong> {{ $obligation->Description }}</p>
    <p><strong>Regulator:</strong> {{ $obligation->regulator->Name ?? '' }}</p>
    <p><strong>Compliance Area:</strong> {{ $obligation->area->Name ?? '' }}</p>
    <p><strong>Effective Date:</strong> {{ $obligation->EffectiveDate }}</p>
    <p><strong>Status:</strong> {{ $obligation->IsActive ? 'Active' : 'Inactive' }}</p>

    <hr>

    <h5>📂 Documents</h5>
    <form method="POST" action="{{ route('legal.compliance.obligations.uploadDoc', $obligation->Id) }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <input type="file" name="document" class="form-control" required>
        </div>
        <button class="btn btn-primary">⬆️ Upload Document</button>
    </form>

    <table class="table table-sm mt-3">
        <thead><tr><th>File</th><th>Version</th><th>Uploaded On</th></tr></thead>
        <tbody>
            @foreach($obligation->documents as $doc)
            <tr>
                <td><a href="{{ Storage::url($doc->FilePath) }}" target="_blank">{{ $doc->FileName }}</a></td>
                <td>v{{ $doc->Version }}</td>
                <td>{{ $doc->UploadedOn }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

   <h5>📊 Impact Assessments</h5>
<form method="POST" action="{{ route('legal.compliance.obligations.addImpact', $obligation->Id) }}">
    @csrf
    <div class="row">
        <div class="col-md-9 mb-3">
            <label class="form-label">Impact Description *</label>
            <textarea name="ImpactDescription" class="form-control" required></textarea>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Department</label>
            <select name="Department" class="form-select">
                <option value="">-- Select Department --</option>
                @foreach(\DB::table('t_Departments')->orderBy('Name')->get() as $dept)
                    <option value="{{ $dept->Name }}">{{ $dept->Name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <button class="btn btn-success">💾 Add Impact</button>
</form>

@endsection
