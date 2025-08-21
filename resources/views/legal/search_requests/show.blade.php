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
                    <h6 class="text-info mb-1">Requested By;</h6>
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
        
        <form method="POST" action="{{ route('legal.store_findings.storeApprovalStatus', $request->Id) }}">
            @csrf
            @method('PATCH')

        {{-- Optional Findings and Approval Reason --}}
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="Status" class="form-label">Status</label>
                <select class="form-select" name="Status" id="Status" onchange="toggleSearchRequests()" required>
                    <option disabled selected value="{{$request->Status}}">{{$request->Status}}</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
        </div>

        <div id="findingsSection" style="display: none;">
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="Findings" class="form-label">Findings</label>
                    <textarea name="Findings" id="Findings" rows="3" class="form-control" placeholder="Enter findings here..." required>{{ $request->Findings ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div id="approvalReasonSection" style="display: none;">
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="ApprovalReason" class="form-label">Approval Reason</label>
                    <textarea name="ApprovalReason" id="ApprovalReason" rows="3" class="form-control" placeholder="Enter approval reason here...">{{ $request->ApprovalReason ?? '' }}</textarea>
                </div>
            </div>
        </div>
        {{-- Buttons --}}
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('legal.search_requests.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-long-arrow-alt-left"></i> Back
            </a>
        <button type="submit" class="btn btn-info" onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='Submitting...'; this.form.submit();}"> Submit</button>
        </div>
        </form>
    </div>
</div>

<script>
    function toggleSearchRequests(){
        const status = document.getElementById('Status').value;

        if(status === 'Approved'){
            document.getElementById('findingsSection').style.display = 'block';
            document.getElementById('approvalReasonSection').style.display = 'none';
        } else if(status === 'Rejected') {
            document.getElementById('findingsSection').style.display = 'none';
            document.getElementById('approvalReasonSection').style.display = 'block';
        } else {
            document.getElementById('findingsSection').style.display = 'none';
            document.getElementById('approvalReasonSection').style.display = 'none';
        }
    }
</script>

@endsection
