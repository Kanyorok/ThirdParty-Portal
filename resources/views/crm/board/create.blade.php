<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div>
    <form action="{{ route('board.store') }}" method="post" id="createBoardForm"> @csrf
        <div class="mb-3">
            <label class="form-label" for="ClientID">Client ID</label>
            <input type="text" class="form-control" id="ClientID" placeholder="ClientID" required name="ClientID">
            <span id="ClientID_error" class="invalid-feedback d-none error" role="alert"></span>
        </div>
        {{--<div class="mb-3">
            <label class="form-label" for="BoardMemberName">Full Name <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="BoardMemberName" name="BoardMemberName" required
                   placeholder="Name">
            <p id="BoardMemberName_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="BoardMemberPhone">Phone Number </label>
            <input type="text" class="form-control" id="BoardMemberPhone" name="BoardMemberPhone"
                   placeholder="Phone Number">
            <p id="BoardMemberPhone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="BoardMemberEmail">Email <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="BoardMemberEmail" name="BoardMemberEmail"
                   placeholder="Email">
            <p id="BoardMemberEmail_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>--}}
        <div class="mb-3">
            <label for="BoardCommittees" class="form-label">Committees <span class="text-danger">*</span></label>
            <select class="form-control" name="BoardCommittees[]" id="BoardCommittees" multiple required>
                @foreach($committees as $committee)
                    <option value="{{ $committee->CommitteeID }}">{{ $committee->Name }}</option>
                @endforeach
            </select>
            <p id="BoardCommittees_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="BoardMemberRole">Role </label>
            <input type="text" class="form-control" id="BoardMemberRole" name="BoardMemberRole"
                   placeholder="Role">
            <p id="BoardMemberRole_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="BoardMemberNotes">Notes </label>
            <textarea name="BoardMemberNotes" id="BoardMemberNotes" rows="3" class="form-control"></textarea>
            <p id="BoardMemberNotes_end_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    onclick="  window.bsOffcanvas.hide();">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="createBoardBtn" type="submit"><i
                    class="fas fa-save"></i> add Board
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#BoardCommittees').select2({
            dropdownParent: $("#offcanvasMain"),
        });
        $('form#createBoardForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createBoardBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchBoardMembersTable === "function") {
                    fetchBoardMembersTable();
                }
            }
        });
    });

</script>
