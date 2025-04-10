<div>
    <form action="{{ route('meeting-room.store') }}" method="post" id="createMeetingRoomForm"> @csrf
        <div class="mb-3">
            <label class="form-label" for="RooMName">Name <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control" id="RooMName" name="RooMName" required
                   placeholder="Name">
            <p id="RooMName_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="RooMCapacity">Capacity <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="RooMCapacity" name="RooMCapacity"
                   placeholder="Capacity" value="2">
            <p id="RooMCapacity_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="branches" class="form-label">Branches</label>
            <select class="form-control" name="RooMBranch" id="RooMBranch">
                <option selected value="">None</option>
                @foreach($branches as $branch)
                    <option
                        value="{{ $branch->OurBranchID }}">{{ \Illuminate\Support\Str::title($branch->BranchName) }}</option>
                @endforeach
            </select>
            <p id="RooMBranch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="RooMNotes">Notes </label>
            <textarea name="RooMNotes" id="RooMNotes" rows="3" class="form-control"></textarea>
            <p id="RooMNotes_end_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    onclick="window.bsOffcanvas.hide();">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="createMeetingRoomBtn" type="submit"><i
                    class="fas fa-save"></i> add Board
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('form#createMeetingRoomForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#createMeetingRoomBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchMeetingRoomsTable === "function") {
                    fetchMeetingRoomsTable();
                }
            }
        });
    });

</script>
