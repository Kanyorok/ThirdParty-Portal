@extends('layouts.app')
@section('title', 'Execution Log Details')
@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📄 Execution Log Details</h4>
    <ul class="list-group">
        <li class="list-group-item"><strong>Signed By:</strong> {{ $log->SignedBy }}</li>
        <li class="list-group-item"><strong>Signed On:</strong> {{ $log->SignedOn }}</li>
        <li class="list-group-item"><strong>DMS Doc ID:</strong> {{ $log->LinkedDMSDocID ?? 'Not Linked' }}</li>
        <li class="list-group-item"><strong>Remarks:</strong> {{ $log->Remarks }}</li>
    </ul>
</div>
@endsection
