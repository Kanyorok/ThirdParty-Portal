<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">IP Type</label>
        <input type="text" name="IPType" class="form-control" required value="{{ old('IPType', $record->IPType ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Title</label>
        <input type="text" name="Title" class="form-control" required value="{{ old('Title', $record->Title ?? '') }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Owner</label>
        <input type="text" name="Owner" class="form-control" value="{{ old('Owner', $record->Owner ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Registration Number</label>
        <input type="text" name="RegistrationNumber" class="form-control" value="{{ old('RegistrationNumber', $record->RegistrationNumber ?? '') }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Registration Date</label>
        <input type="date" name="RegistrationDate" class="form-control" value="{{ old('RegistrationDate', $record->RegistrationDate ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Expiry Date</label>
        <input type="date" name="ExpiryDate" class="form-control" value="{{ old('ExpiryDate', $record->ExpiryDate ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <input type="text" name="Status" class="form-control" required value="{{ old('Status', $record->Status ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label">Remarks</label>
    <textarea name="Remarks" class="form-control" rows="3">{{ old('Remarks', $record->Remarks ?? '') }}</textarea>
</div>
