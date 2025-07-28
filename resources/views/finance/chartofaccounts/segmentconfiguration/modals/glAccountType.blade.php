<!-- Modal for GlType -->
<div class="modal fade" id="glAccountTypeModal" tabindex="-1" aria-labelledby="glAccountTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('glAccountTypeSegmentValue.save') }}">
            @csrf
            @method('post')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">GL Account Type Segment Values</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <div class="row mb-3">
                            <div class="col-md-12 mb-4">
                                <label>GL Type</label>
                                <select name="GLAccountTypeID" id="accountType" class="form-select" required>
                                    <option disabled selected value="">-- Select GL Type --</option>
                                    @foreach($accountTypes as $type)
                                        <option value="{{ $type->Value }}">{{ $type->Description }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label>GL Account Type</label>
                                <select name="GLTypeGroupID" id="typeGroup" class="form-select" required>
                                    <option disabled selected value="">-- GL Account Type --</option>
                                </select>
                            </div>


                            <div class="col-md-12 mb-3">
                                <label>Segment Value</label>
                                <input type="number" name="value" min="0" class="form-control" placeholder="Enter new value">
                            </div>

{{--                            <div class="col-md-4">--}}
{{--                                <label>GL Sub Account Type</label>--}}
{{--                                <select name="GLSubAccountTypeID" id="subType" class="form-select" required>--}}
{{--                                    <option disabled selected value="">-- GL Sub Account Type --</option>--}}
{{--                                </select>--}}
{{--                            </div>--}}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


