@extends('layouts.app')
@section('title','Training Details')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4>🎓 Training: {{ $training->Topic }}</h4>
    <p><strong>Type:</strong> {{ $training->TrainingTypeID }}</p>
    <p><strong>Date:</strong> {{ $training->SessionDate }}</p>
    <p><strong>Facilitator:</strong> {{ $training->Facilitator }}</p>
    <p><strong>Duration:</strong> {{ $training->Duration }}</p>
    @if($training->MaterialsFilePath)
        <p><strong>Materials:</strong> <a href="{{ Storage::url($training->MaterialsFilePath) }}" target="_blank">{{ $training->MaterialsFileName }}</a></p>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <hr>
    <h5>👥 Participants</h5>
    <form method="POST" action="{{ route('legal.compliance.trainings.addParticipant',$training->Id) }}" class="mb-3">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <select name="UserID" class="form-select" required>
                    <option value="">-- Select User --</option>
                    @foreach($users as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-sm btn-primary">➕ Add Participant</button>
            </div>
        </div>
    </form>
    <table class="table table-sm">
        <thead><tr><th>Staff</th><th>Registered On</th><th>Attended</th><th>Action</th></tr></thead>
        <tbody>
            @foreach($training->participants as $p)
            <tr>
                <td>{{ $users[$p->UserID] ?? 'Unknown' }}</td>
                <td>{{ $p->RegisteredOn }}</td>
                <td>{{ $p->Attended ? '✅ Yes':'❌ No' }}</td>
                <td>
                    @if($p->Attended)
                        <form method="POST" action="{{ route('legal.compliance.trainings.quickIssueCert',[$training->Id,$p->Id]) }}" style="display:inline;">
                            @csrf
                            <button class="btn btn-sm btn-success">🎓 Quick Issue</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('legal.compliance.trainings.markAttendance',[$training->Id,$p->Id]) }}" style="display:inline;">
                            @csrf
                            <button class="btn btn-sm btn-info">Mark Attended</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center mb-3">
            <h4 class="text-info mb-0"></h4>
            <button type="submit" class="btn btn-primary mt-2">🎓 Issue Certificates to Selected</button>
        </div>	
    </table>

    <hr>

<h5>📜 Certifications Linked to This Training</h5>
<table class="table table-sm">
    <thead>
        <tr>
            <th>Staff</th>
            <th>Certification</th>
            <th>Issue Date</th>
            <th>Expiry Date</th>
            <th>Download</th>
        </tr>
    </thead>
    <tbody>
        @foreach($training->certifications as $c)
        <tr>
            <td>{{ $users[$c->UserID] ?? 'Unknown' }}</td>
            <td>{{ $c->CertificationName }}</td>
            <td>{{ $c->IssueDate }}</td>
            <td>{{ $c->ExpiryDate }}</td>
            <td>
                @php
                    $doc = [];
                    // DB::table('t_Documents')
                        // ->where('DocumentableType', \App\Models\Legal\ComplianceCertification::class)
                        // ->where('DocumentableId', $c->Id)
                        // ->first();
                @endphp
                @if($doc)
                    <a href="{{ Storage::url($doc->Path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        ⬇️ Download
                    </a>
                @else
                    <span class="text-muted">No file</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>


<script>
    document.getElementById('selectAll').addEventListener('change', function(e){
        document.querySelectorAll('input[name="participants[]"]').forEach(cb => cb.checked = e.target.checked);
    });
</script>
@endsection
