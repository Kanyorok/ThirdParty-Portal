@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Edit Engaged Auditor')
@section('content')
    <div class="container">
        <h3>Edit Engaged SASRA Auditor</h3>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('engaged-auditors.update', $engagedAuditor->Id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="AuditorId" class="form-label">Auditor Firm</label>
                <select name="AuditorId" id="AuditorId" class="form-control" disabled>
                    <option value="">-- Select Auditor Firm --</option>
                    @foreach ($auditors as $auditor)
                        <option
                            value="{{ $auditor->Id }}" {{ $engagedAuditor->auditor->Id == $auditor->Id ? 'selected' : '' }}>{{ $auditor->FirmName }}</option>
                    @endforeach
                </select>
                <!-- Hidden field to send the value of the disabled field -->
                <input type="hidden" name="AuditorId" value="{{ $engagedAuditor->auditor->Id }}">
            </div>

            <div class="mb-3">
                <label for="EngagementStartDate" class="form-label">Engagement Start Date</label>
                <input type="date" name="EngagementStartDate" id="EngagementStartDate" class="form-control"
                       value="{{ Carbon::parse($engagedAuditor->EngagementStartDate)->format('Y-m-d') }}" disabled>
                <!-- Hidden field to send the value of the disabled field -->
                <input type="hidden" name="EngagementStartDate"
                       value="{{ Carbon::parse($engagedAuditor->EngagementStartDate)->format('Y-m-d') }}">
            </div>

            <div class="mb-3">
                <label for="EngagementEndDate" class="form-label">Engagement End Date</label>
                <input type="date" name="EngagementEndDate" id="EngagementEndDate" class="form-control"
                       value="{{ $engagedAuditor->EngagementEndDate ? Carbon::parse($engagedAuditor->EngagementEndDate)->format('Y-m-d') : '' }}"
                       required min="{{ date('Y-m-d') }}">
            </div>

            <div class="mb-3">
                <label for="EngagementStatus" class="form-label">Engagement Status</label>
                <select name="EngagementStatus" id="EngagementStatus" class="form-control" disabled>
                    <option value="active" {{ $engagedAuditor->EngagementStatus == 'active' ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="inactive" {{ $engagedAuditor->EngagementStatus == 'inactive' ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
                <!-- Hidden field to send the value of the disabled field -->
                <input type="hidden" name="EngagementStatus" value="{{ $engagedAuditor->EngagementStatus }}">
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
@endsection
