<!-- Modal for GL Digits -->
<div class="modal fade" id="glDigitsModal" tabindex="-1" aria-labelledby="glDigitsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('glDigits.save') }}">
            @csrf
            @method('post')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">GL Digits</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">GL Digits</label>
                    <input type="number" min="1" name="glDigits" value="{{ $glDigits }}" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
