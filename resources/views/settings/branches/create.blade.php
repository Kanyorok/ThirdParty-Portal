<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<div>
    <h2 class="text-center">{{ $branch->BranchName }}</h2>
    <ul class="list-group list-group-flush">
        <li class="list-group-item"><b>Branch ID </b><span class="float-end">{{ $branch->OurBranchID }} </span></li>
        <li class="list-group-item"><b>Address1 </b><span
                class="float-end"> {{ $branch->Address1 }} {{ $branch->Address2 }}</span></li>
        <li class="list-group-item"><b>Mobile </b><span class="float-end"> {{ $branch->Mobile }} </span></li>
        <li class="list-group-item"><b>EMail </b><span class="float-end"> {{ $branch->EMailID }} </span></li>
    </ul>

    <hr>
    <h3>Update Managers</h3>
    <form action="{{ route('branches.store') }}" method="post" id="updateManagerForm"> @csrf
        <div class="mb-3">
            <label for="BranchID" class="form-label">BranchID </label>
            <input type="text" name="BranchID" id="BranchID" class="form-control" readonly
                   value="{{ $branch->OurBranchID }}">
            <p id="BranchID_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="Manager" class="form-label">Branch Manager </label>
            <select class="form-control form-control-lg w-100 select-users" name="Manager" id="Manager">
                @if($local?->manager instanceof \App\Models\Auth\User)
                    <option selected value="{{ $local->manager->UserID }}">{{ $local->manager->Name }}
                        - {{ $local->manager->UserID }}</option>
                @endif
            </select>
            <p id="Manager_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <div class="mb-3">
            <label for="Operation" class="form-label">Operation Manager </label>
            <select class="form-control form-control-lg w-100 select-users" name="Operation" id="Operation">
                @if($local?->operation instanceof \App\Models\Auth\User)
                    <option selected value="{{ $local->operation->UserID }}">{{ $local->operation->Name }}
                        - {{ $local->operation->UserID }}</option>
                @endif
            </select>
            <p id="Operation_error" class="invalid-feedback d-none error col-12"
               role="alert"></p>
        </div>
        <hr>
        <div class="mt-4">
            <button type="button" class="btn btn-secondary float-start"
                    data-bs-dismiss="modal">
                cancel
            </button>
            <button class="btn btn-primary float-end" id="updateManagerBtn" type="submit"><i
                    class="fas fa-save"></i> update manager
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('.select-users').select2({
            placeholder: "Choose users ...", minimumInputLength: 2,
            dropdownParent: $("#offcanvasMain"),
            ajax: {
                url: '{{route('users.select2')}}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {q: $.trim(params.term)};
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {text: item.Name, id: item.UserID}
                        })
                    };
                },
                cache: true
            }
        });

        $('form#updateManagerForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#updateManagerBtn'), false, true, true)) {
                window.bsOffcanvas.hide();
                if (typeof fetchBranchesTable === "function") {
                    fetchBranchesTable();
                }
            }
        });
    });

</script>
