@extends('layouts.app')
@section('title', 'Edit GL Account')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
@endsection
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
                            <input type="text" name="GLName" value="{{ old('GLName', $gl->GLName) }}" class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-4">
                            <label>Currency</label>
                            <select name="Currency" id="Currency" class="form-select" required>
                                @foreach($currencies as $currency)
                                    <option
                                        value="{{$currency->Id}}" {{ old('Currency', $gl->CurrencyID) == $currency->Id ? 'selected' : '' }}>{{$currency->Code}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-4">
                            <label>Is Active?</label>
                            <select name="IsActive" class="form-select" required>
                                <option value="1" {{ (string) old('IsActive', $gl->IsActive) === '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ (string) old('IsActive', $gl->IsActive) === '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>

                    @if(!empty($allowThirdPartyPosting))
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="MappedGLCode">Map GL from Nimble <span class="text-danger">*</span></label>
                                <select name="MappedGLCode" id="MappedGLCode" class="form-select" required>
                                    @if($mappedGlOption)
                                        <option value="{{ $mappedGlOption['id'] }}" selected>{{ $mappedGlOption['text'] }}</option>
                                    @endif
                                </select>
                                <small class="text-muted">Select currency first, then search by GL code or name to map the matching Nimble GL account.</small>
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>GL Type</label>
                            <select name="GLAccountTypeID" id="accountType" class="form-select" required>
                                <option disabled value="">-- Select GL Type --</option>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type->Value }}" {{ old('GLAccountTypeID', $gl->GLAccountTypeID) == $type->Value ? 'selected' : '' }}>{{ $type->Description }}</option>
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
                        <textarea name="Description" class="form-control" rows="3" maxlength="255">{{ old('Description', $gl->Description) }}</textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-centre mt-1">
                        <a href="{{ route('chartofaccounts.index') }}" class="btn btn-secondary">Back</a>
                        <button class="btn btn-success" type="submit"
                                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit();}">
                            Update GL Account
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @endsection

        @section('scripts')
            <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const accountType = document.getElementById('accountType');
                    const typeGroup = document.getElementById('typeGroup');
                    const subType = document.getElementById('subType');
                    const currencySelect = document.getElementById('Currency');
                    const mappedSelectElement = document.getElementById('MappedGLCode');

                    //Pre populate the inputs that are dynamic
                    let typeID=@json($typeID);
                    let subTypeID=@json($subTypeID);
                    let gl = @json($gl);
                    fetch(`/finance/get-type-groups?GLAccountTypeID=${typeID}`)
                        .then(res => res.json())
                        .then(data => {
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

                    if (currencySelect && mappedSelectElement) {
                        const refreshMappedState = function () {
                            const hasCurrency = !!currencySelect.value;
                            mappedSelectElement.disabled = !hasCurrency;
                            mappedSelectElement.required = hasCurrency;
                        };

                        refreshMappedState();

                        currencySelect.addEventListener('change', function () {
                            if (window.jQuery && $.fn.select2) {
                                $('#MappedGLCode').val(null).trigger('change');
                            } else {
                                mappedSelectElement.value = '';
                            }
                            refreshMappedState();
                        });
                    }
                });
            </script>
            <script>
                (function () {
                    if (!(window.jQuery && $.fn.select2)) {
                        return;
                    }

                    const mappedSelect = $('#MappedGLCode');
                    if (!mappedSelect.length) {
                        return;
                    }

                    mappedSelect.select2({
                        placeholder: 'Search by GL code or name',
                        allowClear: true,
                        width: '100%',
                        ajax: {
                            url: @json(route('chartofaccounts.mapping.search')),
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term || '',
                                    currency_id: $('#Currency').val() || ''
                                };
                            },
                            processResults: function (data) {
                                return { results: data.results || [] };
                            },
                            cache: true,
                        },
                        templateResult: function (data) {
                            if (!data.id) return data.text;
                            const code = data.code || data.id || '';
                            const name = data.name || '';
                            return $('<div><strong>' + code + '</strong> <small class="text-muted">(' + name + ')</small></div>');
                        },
                        templateSelection: function (data) {
                            if (!data.id) return data.text;
                            const code = data.code || data.id || '';
                            const name = data.name || '';
                            return code && name ? (code + ' (' + name + ')') : (data.text || code);
                        }
                    });
                })();
            </script>

@endsection
