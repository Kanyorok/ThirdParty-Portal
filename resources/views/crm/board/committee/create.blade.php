<div>
    <form action="{{ route('committee.store') }}" method="post" id="createCommitteeForm"> @csrf
       <div class="mb-3">
            <label class="form-label" for="CommitteeName">Committee Name <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="CommitteeName" name="CommitteeName" required
                   placeholder="Name">
            <p id="CommitteeName_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="CommitteeNotes">Notes </label>
            <textarea name="CommitteeNotes" id="CommitteeNotes" rows="3" class="form-control"></textarea>
            <p id="CommitteeNotes_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    onclick="  window.bsOffcanvas.hide();">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="createCommitteeBtn" type="submit"><i
                    class="fas fa-save"></i> add Committee
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('form#createCommitteeForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createCommitteeBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchCommitteesTable === "function") {
                    fetchCommitteesTable();
                }
            }
        });
    });

</script>
