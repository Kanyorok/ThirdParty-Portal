<div class="modal fade" id="createCriteriaModal" tabindex="-1" aria-labelledby="createCriteriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('prequalification.sections.criteria.store', ['section' => $section->Id]) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCriteriaLabel">Add Criteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="CriteriaName" class="form-label">Criteria Name</label>
                        <input type="text" name="CriteriaName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="Description" class="form-label">Description</label>
                        <textarea name="Description" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="IsActive" class="form-label">Status</label>
                        <select name="IsActive" class="form-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Criteria</button>
                </div>
            </div>
        </form>
    </div>
</div>