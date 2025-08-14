<div class="modal fade" id="createCriteriaModal" tabindex="-1" aria-labelledby="createCriteriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('criteria.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCriteriaLabel">Add Criteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Criteria Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="section_id" class="form-label">Section</label>
                        <select name="section_id" class="form-control" required>
                            @foreach(\App\Models\Procurement\Section::all() as $section)
                            <option value="{{ $section->id }}">{{ $section->SectionName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="desc" class="form-label">Description</label>
                        <textarea name="desc" class="form-control"></textarea>
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