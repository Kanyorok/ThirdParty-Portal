@php $case = $case ?? null; @endphp

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Case Title</label>
        <input type="text" name="CaseTitle" value="{{ old('CaseTitle', $case->CaseTitle ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Case Number</label>
        <input type="text" name="CaseNumber" value="{{ old('CaseNumber', $case->CaseNumber ?? '') }}" class="form-control">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Court Name</label>
        <input type="text" name="CourtName" value="{{ old('CourtName', $case->CourtName ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Filing Date</label>
        <input type="date" name="FilingDate" value="{{ old('FilingDate', $case->FilingDate ?? '') }}" class="form-control">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Opposing Party</label>
        <input type="text" name="OpposingParty" value="{{ old('OpposingParty', $case->OpposingParty ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Case Type</label>
        <input type="text" name="CaseType" value="{{ old('CaseType', $case->CaseType ?? '') }}" class="form-control">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <input type="text" name="Status" value="{{ old('Status', $case->Status ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">DMS Document ID</label>
        <input type="text" name="DMSDocID" value="{{ old('DMSDocID', $case->DMSDocID ?? '') }}" class="form-control">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Summary</label>
    <textarea name="Summary" class="form-control" rows="3">{{ old('Summary', $case->Summary ?? '') }}</textarea>
</div>
