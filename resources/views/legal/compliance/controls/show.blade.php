@extends('layouts.app')
@section('title','Control Details')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">📄 Control Details</h4>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <p><strong>Title:</strong> {{ $control->Title }}</p>
    <p><strong>Area:</strong> {{ $control->area->Name ?? '-' }}</p>
    <p><strong>Type:</strong> {{ $control->controlType->Name ?? '-' }}</p>
    <p><strong>Owner:</strong> {{ \DB::table('t_Users')->where('Id',$control->OwnerID)->value('Name') }}</p>
    <p><strong>Description:</strong> {{ $control->Description }}</p>
    <p><strong>Status:</strong> {{ $control->IsActive ? 'Active':'Inactive' }}</p>

    <hr>
    <h5>📌 Linked Obligations</h5>
    <ul>
        @foreach($control->obligations as $obligation)
            <li>{{ $obligation->Title }}</li>
        @endforeach
    </ul>

    <hr>
    <h5>📂 Evidence</h5>
    <form method="POST" action="{{ route('legal.compliance.controls.uploadEvidence',$control->Id) }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-8 mb-3">
                <input type="file" name="evidence" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <button class="btn btn-primary">⬆️ Upload</button>
            </div>
        </div>
    </form>
    <table class="table table-sm mt-3">
        <thead><tr><th>File</th><th>Version</th><th>Uploaded On</th></tr></thead>
        <tbody>
        @foreach($control->evidence as $doc)
            <tr>
                <td><a href="{{ Storage::url($doc->FilePath) }}" target="_blank">{{ $doc->FileName }}</a></td>
                <td>v{{ $doc->Version }}</td>
                <td>{{ $doc->UploadedOn }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
