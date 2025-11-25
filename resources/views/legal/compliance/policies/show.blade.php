@extends('layouts.app')
@section('title','Policy Details')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4>📘 Policy: {{ $policy->Title }}</h4>
    <p><strong>Category:</strong> {{ $policy->CategoryID }}</p>
    <p><strong>Effective Date:</strong> {{ $policy->EffectiveDate }}</p>
    <p><strong>Version:</strong> {{ $policy->Version }}</p>
    <p><strong>Status:</strong> {{ $policy->IsActive ? 'Active':'Retired' }}</p>
    @if($policy->FilePath)
        <p><strong>Document:</strong> <a href="{{ Storage::url($policy->FilePath) }}" target="_blank">{{ $policy->FileName }}</a></p>
    @endif

    <hr>
    <h5>Staff Acknowledgments</h5>
    <form method="POST" action="{{ route('legal.compliance.policies.acknowledge',$policy->Id) }}">
        @csrf
        <button class="btn btn-primary mb-3">✅ Acknowledge</button>
    </form>

    <table class="table table-sm">
        <thead><tr><th>User</th><th>Acknowledged On</th></tr></thead>
        <tbody>
            @foreach($policy->acknowledgments as $a)
            <tr>
                <td>{{ $users[$a->UserID] ?? 'Unknown' }}</td>
                <td>{{ $a->AcknowledgedOn }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
