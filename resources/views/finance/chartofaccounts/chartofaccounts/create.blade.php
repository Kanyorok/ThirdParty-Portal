@extends('layouts.app')
@section('title', 'New GL Account')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🧾 Create New GL Account</h4>

    <form method="POST" action="{{ route('chartofaccounts.store') }}">
        @csrf

        <div class="row mb-3">
            <div class="col-md-4">
                <label>GL Account Code</label>
                <input type="text" name="GLCode" class="form-control" required>
            </div>
            <div class="col-md-8">
                <label>Account Name</label>
                <input type="text" name="AccountName" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Account Type</label>
                <select name="GLAccountTypeID" id="accountType" class="form-select" required>
                    <option disabled selected>-- Select --</option>
                    @foreach($accountTypes as $type)
                        <option value="{{ $type->Id }}">{{ $type->Description }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>Sub Type Group</label>
                <select name="GLTypeGroupID" id="typeGroup" class="form-select" required>
                    <option disabled selected>-- Select Account Type First --</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Sub Account Type</label>
                <select name="GLSubAccountTypeID" id="subType" class="form-select">
                    <option disabled selected>-- Select Type Group First --</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <input type="text" name="Description" class="form-control">
        </div>
<div class="row mb-3">
    <div class="col-md-6">
        <label>Parent GL Account (Optional)</label>
        <select name="ParentGLID" class="form-select">
            <option value="">-- No Parent (Top-level) --</option>
            @foreach($allGLAccounts as $gl)
                <option value="{{ $gl->GLCode }}">{{ $gl->GLCode }} - {{ $gl->AccountName }}</option>
            @endforeach
        </select>
    </div>
</div>
        <div class="mb-3">
            <label>Is Active?</label>
            <select name="IsActive" class="form-select">
                <option value="1" selected>Yes</option>
                <option value="0">No</option>
            </select>
        </div>

        <button class="btn btn-success">Create GL Account</button>
        <a href="{{ route('chartofaccounts.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection

@section('scripts')
<script>
    $('#accountType').on('change', function () {
        let typeId = $(this).val();
        $('#typeGroup').html('<option>Loading...</option>');
        $.get(`/api/type-groups/${typeId}`, function (data) {
            let options = '<option disabled selected>-- Select --</option>';
            data.forEach(function (item) {
                options += `<option value="${item.Id}">${item.Description}</option>`;
            });
            $('#typeGroup').html(options);
            $('#subType').html('<option disabled selected>-- Select Type Group First --</option>');
        });
    });

    $('#typeGroup').on('change', function () {
        let groupId = $(this).val();
        $('#subType').html('<option>Loading...</option>');
        $.get(`/api/sub-types/${groupId}`, function (data) {
            let options = '<option disabled selected>-- Select --</option>';
            data.forEach(function (item) {
                options += `<option value="${item.Id}">${item.Description}</option>`;
            });
            $('#subType').html(options);
        });
    });
</script>
@endsection
