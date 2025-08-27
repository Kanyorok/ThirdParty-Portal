@extends('layouts.app')
@section('title', 'Search Request Details')

@section('content')
<div class="card p-1 shadow rounded-4">
    <div class="card-body">
        
        {{-- Intro --}}
        <p class="text-muted">
            Detailed information for the selected search request. This record includes request type, entity name, purpose, and current status.
        </p>

        {{-- Details --}}
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <div class="p-3 bg-light rounded-3">
                    <h6 class="text-info mb-1">Request Type:</h6>
                    <p class="mb-0 fw-semibold">{{ $request->RequestType }}</p>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="p-3 bg-light rounded-3">
                    <h6 class="text-info mb-1">Entity Name:</h6>
                    <p class="mb-0 fw-semibold">{{ $request->EntityName }}</p>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class=" p-3 bg-light rounded-3">
                    <h6 class="text-info mb-1">Requested By:</h6>
                    <p class="mb-0 fw-semibold">{{ $request->RequestedBy ?? '—' }}</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class=" p-3 bg-light rounded-3">
                    <h6 class="text-info mb-1">Request Date:</h6>
                    <p class="mb-0 fw-semibold">
                        {{ \Carbon\Carbon::parse($request->RequestDate)->format('d M Y H:i') }}
                    </p>
                </div>
            </div>

            <div class="col-md-12 mb-3">
                <div class="p-3 bg-light rounded-3">
                    <h6 class="text-info mb-1">Remarks:</h6>
                    <p class="mb-0 fw-semibold">{{ $request->Remarks ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- ✅ Display Current Status --}}
        @if($request->Status)
        <div class="mb-4">
            <div class="p-3 rounded-3 
                {{ $request->Status == 'Approved' ? 'bg-light' : 'bg-light' }}">
                
                @if($request->Status == 'Approved')
                <h6 class="mb-1">Current Status: 
                </h6>
                    <span class="badge bg-success fw-bold">{{ $request->Status }}</span>
                    <p class="mb-0"><strong>Findings:</strong> {{ $request->Findings ?? '—' }}</p>
                @elseif($request->Status == 'Rejected')
                <h6 class="text-info mb-1">Current Status: 
                </h6>
                    <span class="badge bg-danger fw-bold">{{ $request->Status }}</span>
                    <p class="mb-0"><strong>Reason for Rejection:</strong> {{ $request->ApprovalReason ?? '—' }}</p>
                @endif
            </div>
        </div>
        @endif

        {{-- ✅ Only show the form if status is still pending --}}
        @if(!in_array($request->Status, ['Approved', 'Rejected']))
        <form method="POST" action="{{ route('legal.store_findings.storeApprovalStatus', $request->Id) }}">
            @csrf
            @method('PATCH')

            {{-- Status --}}
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="Status" class="form-label">Status</label>
                    <select class="form-select" name="Status" id="Status" onchange="toggleSearchRequests()" required>
                        <option value="" disabled {{ !$request->Status ? 'selected' : '' }}>-- Select Status --</option>
                        <option value="Approved">Approve</option>
                        <option value="Rejected">Reject</option>
                    </select>
                </div>
            </div>

            {{-- Findings (Approved only) --}}
            <div id="findingsSection" style="display: none;">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="Findings" class="form-label">Findings</label>
                        <textarea name="Findings" id="Findings" rows="3" class="form-control" placeholder="Enter findings here...">{{ $request->Findings ?? '' }}</textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('legal.search_requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-long-arrow-alt-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-success"
                        onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='Submitting...'; this.form.submit();}">
                        <i class="fas fa-thumbs-up"></i> Approve
                    </button>
                </div>
            </div>

            {{-- Approval Reason (Rejected only) --}}
            <div id="approvalReasonSection" style="display: none;">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="ApprovalReason" class="form-label">Reason for Rejection</label>
                        <textarea name="ApprovalReason" id="ApprovalReason" rows="3" class="form-control" placeholder="Enter approval reason here...">{{ $request->ApprovalReason ?? '' }}</textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('legal.search_requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-long-arrow-alt-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-danger"
                        onclick="document.getElementById('Status').value='Rejected';
                                if(this.form.checkValidity()){this.disabled=true; this.innerText='Submitting...'; this.form.submit();}">
                        <i class="fas fa-thumbs-down"></i> Reject
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>
</div>

<script>
function toggleSearchRequests(){
    const status = document.getElementById('Status').value;
    const findingsSection = document.getElementById('findingsSection');
    const findingsInput = document.getElementById('Findings');
    const reasonSection = document.getElementById('approvalReasonSection');
    const reasonInput = document.getElementById('ApprovalReason');

    if(status === 'Approved'){
        findingsSection.style.display = 'block';
        findingsInput.required = true;
        reasonSection.style.display = 'none';
        reasonInput.required = false;
        reasonInput.value = "";
    } else if(status === 'Rejected') {
        reasonSection.style.display = 'block';
        reasonInput.required = true;
        findingsSection.style.display = 'none';
        findingsInput.required = false;
        findingsInput.value = "";
    } else {
        findingsSection.style.display = 'none';
        findingsInput.required = false;
        findingsInput.value = "";
        reasonSection.style.display = 'none';
        reasonInput.required = false;
        reasonInput.value = "";
    }
}
</script>
@endsection
