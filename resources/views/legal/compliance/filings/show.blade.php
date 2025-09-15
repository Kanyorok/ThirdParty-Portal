@extends('layouts.app')
@section('title','Filing Details')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4>📑 Filing Details</h4>
    <p><strong>Template:</strong> {{ $filing->template->Name ?? '-' }}</p>
    <p><strong>Date:</strong> {{ $filing->SubmissionDate }}</p>
    <p><strong>Status:</strong> {{ $filing->Status }}</p>
    <p><strong>Notes:</strong> {{ $filing->Notes }}</p>

    @if($filing->FilePath)
        <p><strong>File:</strong> <a href="{{ Storage::url($filing->FilePath) }}" target="_blank">{{ $filing->FileName }}</a></p>
    @endif

    <hr>
    <h5>📂 Acknowledgments</h5>
    <form method="POST" action="{{ route('legal.compliance.filings.uploadAck',$filing->Id) }}" enctype="multipart/form-data">
        @csrf
        <div class="row mb-3">
            <div class="col-md-8"><input type="file" name="AckFile" class="form-control" required></div>
            <div class="col-md-4"><button class="btn btn-primary">⬆️ Upload Ack</button></div>
        </div>
    </form>
    <table class="table table-sm">
        <thead><tr><th>File</th><th>Uploaded On</th></tr></thead>
        <tbody>
            @foreach($filing->acknowledgments as $ack)
            <tr>
                <td><a href="{{ Storage::url($ack->FilePath) }}" target="_blank">{{ $ack->AckFileName }}</a></td>
                <td>{{ $ack->UploadedOn }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
