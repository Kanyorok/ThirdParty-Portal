@extends('layouts.app')
@section('title', 'New GL Account')
@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="container mt-4">
        <div class="card p-4">
            <div class="card-header bg-dark text-white">
                Create General Ledger Account
            </div>

            <div class="card-body mb-3">
                <p class="muted">
                    Create your own General Ledger Account
                </p>
                <form method="POST" action="{{ route('chartofaccounts.store') }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>GL Account Code</label>
                            <input type="text" name="GLCode" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label>Account Name</label>
                            <input type="text" name="GLName" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Account Type</label>
                            <select name="GLAccountTypeID" id="accountType" class="form-select" required>
                                <option disabled selected>-- Select --</option>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type->Value }}">{{ $type->Description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Account Type Group</label>
                            <select name="GLTypeGroupID" id="typeGroup" class="form-select" required>
                                <option disabled selected>-- Select Account Type First --</option>
                                @foreach ($typeGroups as $item)
                                    <option value="{{$item->Id}}">{{$item->Description}}
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Sub Account Type</label>
                            <select name="GLSubAccountTypeID" id="subType" class="form-select">
                                <option disabled selected>-- Select Type Group First --</option>
                                @foreach ($subAccountTypes as $item)
                                    <option value="{{$item->Id}}">{{$item->Description}}</option>
                                @endforeach
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
                                <option value="">-- None --</option>
                                @foreach($allGLAccounts as $gl)
                                    <option value="{{ $gl->Id }}" {{ old('ParentGLID') == $gl->Id ? 'selected' : '' }}>
                                        {{ $gl->GLCode }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- <input type="hidden" name="TestField" value="I am working"> --}}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Is Active?</label>
                        <select name="IsActive" class="form-select">
                            <option value="1" selected>Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between align-items-centre mb-4">
                        <a href="{{ route('chartofaccounts.index') }}" class="btn btn-secondary">Cancel</a>
                        <button class="btn btn-success" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Creating...'; this.form.submit();}">Create GL Account</button>
                    </div>
                </form>
            </div>
        </div>

    {{-- @section('scripts')
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
    </script> --}}
@endsection
