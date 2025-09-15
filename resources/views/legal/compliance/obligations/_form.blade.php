<div class="row mb-3">
    <div class="col-md-6">
        <label>Obligation Title *</label>
        <input type="text" name="ObligationTitle" class="form-control" value="{{ old('ObligationTitle', $obligation->ObligationTitle ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label>Regulatory Body *</label>
        <input type="text" name="RegulatoryBody" class="form-control" value="{{ old('RegulatoryBody', $obligation->RegulatoryBody ?? '') }}" required>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label>Obligation Type *</label>
        <input type="text" name="ObligationType" class="form-control" value="{{ old('ObligationType', $obligation->ObligationType ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label>Status *</label>
        <select name="Status" class="form-select" required>
            <option value="Active" {{ old('Status', $obligation->Status ?? '') == 'Active' ? 'selected' : '' }}>Active</option>
            <option value="Closed" {{ old('Status', $obligation->Status ?? '') == 'Closed' ? 'selected' : '' }}>Closed</option>
        </select>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label>Effective Date *</label>
        <input type="date" name="EffectiveDate" class="form-control" value="{{ old('EffectiveDate', $obligation->EffectiveDate ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label>Due Date</label>
        <input type="date" name="DueDate" class="form-control" value="{{ old('DueDate', $obligation->DueDate ?? '') }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label>Is Recurring? *</label>
        <select name="IsRecurring" class="form-select" required>
            <option value="0" {{ old('IsRecurring', $obligation->IsRecurring ?? 0) == 0 ? 'selected' : '' }}>No</option>
            <option value="1" {{ old('IsRecurring', $obligation->IsRecurring ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
        </select>
    </div>
    <div class="col-md-6">
        <label>Recurrence Type</label>
        <input type="text" name="RecurrenceType" class="form-control" placeholder="Monthly, Quarterly, etc." value="{{ old('RecurrenceType', $obligation->RecurrenceType ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label>Description</label>
    <textarea name="ObligationDescription" class="form-control" rows="3">{{ old('ObligationDescription', $obligation->ObligationDescription ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label>Compliance Area</label>
    <input type="text" name="ComplianceArea" class="form-control" value="{{ old('ComplianceArea', $obligation->ComplianceArea ?? '') }}">
</div>
