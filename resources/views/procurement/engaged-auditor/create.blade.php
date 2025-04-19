@extends('layouts.app')
@section('title', 'Engage Auditor')
@section('content')

<div class="container">
    <h3>Engage SASRA Auditor</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Please fix the following issues:<br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('engaged-auditors.store') }}" method="POST">
        @csrf

        <div class="form-group mb-3">
            <label for="SasraAuditorId">Select Auditor <span class="text-danger">*</span></label>
            <select name="SasraAuditorId" class="form-control" required>
                <option value="">-- Select Auditor --</option>
                @foreach ($auditors as $auditor)
                    <option value="{{ $auditor->Id }}">{{ $auditor->AuditorName }} ({{ $auditor->FirmName }})</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="EngagementStartDate">Engagement Start Date <span class="text-danger">*</span></label>
            <input type="date" name="EngagementStartDate" class="form-control" required min="{{ date('Y-m-d') }}">
        </div>

        <div class="form-group mb-3">
            <label for="EngagementEndDate">Engagement End Date</label>
            <input type="date" name="EngagementEndDate" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary mt-3">Engage Auditor</button>
    </form>
</div>

@endsection
