@extends('layouts.app')
@section('title', 'Edit GL Account')
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
            <div class="card-header bg-light text-black">
                Edit General Ledger Account ({{$gl->GLName}})
            </div>

            <div class="card-body mb-1">
                <form method="POST" action="{{ route('chartofaccounts.update', $gl->Id) }}">
                    @method('PUT') <!-- This makes it a PUT request -->
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>GL Name</label>
                            <input type="text" name="GLName" value="{{$gl->GLName}}" class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-4">
                            <label>Currency</label>
                            <select name="Currency" class="form-select" required>
                                @foreach($currencies as $currency)
                                    <option value="{{$currency->Id}}" {{$gl->CurrencyID==$currency->Id?'selected':''}}>{{$currency->Code}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-4">
                            <label>Is Active?</label>
                            <select name="IsActive" class="form-select" required>
                                <option value="1" {{$gl->IsActive==1?'selected':''}}>Yes</option>
                                <option value="0" {{$gl->IsActive==0?'selected':''}}>No</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>GL Type</label>
                            <select name="GLAccountTypeID" id="accountType" class="form-select" required>
                                <option disabled value="">-- Select GL Type --</option>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type->Value }}" {{$gl->GLAccountTypeID==$type->Value?'selected':''}}>{{ $type->Description }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>GL Account Type</label>
                            <select name="GLTypeGroupID" id="typeGroup" class="form-select" required>
                                <option disabled value="">-- GL Account Type --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>GL Sub Account Type</label>
                            <select name="GLSubAccountTypeID" id="subType" class="form-select" required>
                                <option disabled value="">-- GL Sub Account Type --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="Description">Description</label>
                        <textarea name="Description" class="form-control" rows="3" maxlength="255">{{$gl->Description}}</textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-centre mt-1">
                        <a href="{{ route('chartofaccounts.index') }}" class="btn btn-secondary">Back</a>
                        <button class="btn btn-success" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit();}">Update GL Account</button>
                    </div>
                </form>
            </div>
        </div>

        @endsection

        @section('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const accountType = document.getElementById('accountType');
                    const typeGroup = document.getElementById('typeGroup');
                    const subType = document.getElementById('subType');

                    //Pre populate the inputs that are dynamic
                    let typeID=@json($typeID);
                    let subTypeID=@json($subTypeID);
                    let gl = @json($gl);
                    fetch(`/finance/get-type-groups?GLAccountTypeID=${typeID}`)
                        .then(res => res.json())
                        .then(data => {
                            console.log('Acc',data)
                            typeGroup.innerHTML = '<option disabled selected>-- GL Account Type --</option>';
                            data.forEach(item => {
                                let selected = item.Id == gl.type_group?.Id ? 'selected' : '';
                                typeGroup.innerHTML += `<option value="${item.Id}" ${selected}>${item.Description}</option>`;
                            });
                        })
                        .catch(err => {
                            console.error('Failed to load type groups', err);
                            typeGroup.innerHTML = '<option disabled selected>-- Error Loading --</option>';
                        });

                    fetch(`/finance/get-sub-account-types?GLTypeGroupID=${subTypeID}`)
                        .then(res => res.json())
                        .then(data => {
                            console.log('Type', data);
                            subType.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';
                            data.forEach(item => {
                                let selected = item.Id == gl.sub_account?.Id ? 'selected' : '';
                                subType.innerHTML += `<option value="${item.Id}" ${selected}>${item.Description}</option>`;
                            });
                        })
                        .catch(err => {
                            console.error('Failed to load sub account types', err);
                            subType.innerHTML = '<option disabled selected>-- Error Loading --</option>';
                        });


                    // Reset child selects initially
                    typeGroup.innerHTML = '<option disabled selected>-- GL Account Type --</option>';
                    subType.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';

                    accountType.addEventListener('change', function () {
                        const typeID = this.value;

                        // Reset children
                        typeGroup.innerHTML = '<option disabled selected>Loading...</option>';
                        subType.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';

                        fetch(`/finance/get-type-groups?GLAccountTypeID=${typeID}`)
                            .then(res => res.json())
                            .then(data => {
                                typeGroup.innerHTML = '<option disabled selected>-- GL Account Type --</option>';
                                data.forEach(item => {
                                    typeGroup.innerHTML += `<option value="${item.Id}">${item.Description}</option>`;
                                });
                            })
                            .catch(err => {
                                console.error('Failed to load type groups', err);
                                typeGroup.innerHTML = '<option disabled selected>-- Error Loading --</option>';
                            });
                    });

                    typeGroup.addEventListener('change', function () {
                        const groupID = this.value;

                        // Reset subType
                        subType.innerHTML = '<option disabled selected>Loading...</option>';

                        fetch(`/finance/get-sub-account-types?GLTypeGroupID=${groupID}`)
                            .then(res => res.json())
                            .then(data => {
                                subType.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';
                                data.forEach(item => {
                                    subType.innerHTML += `<option value="${item.Id}">${item.Description}</option>`;
                                });
                            })
                            .catch(err => {
                                console.error('Failed to load sub account types', err);
                                subType.innerHTML = '<option disabled selected>-- Error Loading --</option>';
                            });
                    });
                });
            </script>

@endsection
