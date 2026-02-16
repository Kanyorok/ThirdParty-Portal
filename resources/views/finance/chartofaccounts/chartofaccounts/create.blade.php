@extends('layouts.app')
@section('title', 'New GL Account')
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

    <div class="container mt-2">
        <div class="card p-2">
{{--            <div class="card-header bg-dark text-white">--}}
{{--                Create General Ledger Account--}}
{{--            </div>--}}

            <div class="card-body mb-1">
{{--                <p class="muted">--}}
{{--                    Create your own General Ledger Account--}}
{{--                </p>--}}
                <form method="POST" action="{{ route('chartofaccounts.store') }}">
                    @csrf
                    @method('POST')

                    <div class="row mb-3">
{{--                        <div class="col-md-4">--}}
{{--                            <label>GL Account Code</label>--}}
{{--                            <input type="text" name="GLCode" class="form-control" required>--}}
{{--                        </div>--}}
                        <div class="col-md-6">
                            <label>GL Name</label>
                            <input type="text" name="GLName" class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label>Currency</label>
                            <select name="Currency" id="Currency" class="form-select" required>
                                <option value="" disabled {{ old('Currency') ? '' : 'selected' }}>-- Select Currency --</option>
                                @foreach($currencies as $currency)
                                    <option value="{{$currency->Id}}" {{ old('Currency') == $currency->Id ? 'selected' : '' }}>{{$currency->Code}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if(!empty($allowThirdPartyPosting))
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="MappedGLCode">Map GL from Nimble <span class="text-danger">*</span></label>
                                <select name="MappedGLCode" id="MappedGLCode" class="form-select" required>
                                    @if(old('MappedGLCode'))
                                        <option value="{{ old('MappedGLCode') }}" selected>{{ old('MappedGLCode') }}</option>
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
                                <option disabled selected value="">-- Select GL Type --</option>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type->Value }}">{{ $type->Description }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>GL Account Type</label>
                            <select name="GLTypeGroupID" id="typeGroup" class="form-select" required>
                                <option disabled selected value="">-- GL Account Type --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>GL Sub Account Type</label>
                            <select name="GLSubAccountTypeID" id="subType" class="form-select" required>
                                <option disabled selected value="">-- GL Sub Account Type --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="Description">Description</label>
                        <textarea name="Description" class="form-control" rows="3" maxlength="255"></textarea>
                    </div>

{{--                    <div class="row mb-3">--}}
{{--                        <div class="col-md-6">--}}
{{--                            <label>Parent GL Account (Optional)</label>--}}
{{--                            <select name="ParentGLID" class="form-select">--}}
{{--                                <option value="">-- None --</option>--}}
{{--                                @foreach($allGLAccounts as $gl)--}}
{{--                                    <option value="{{ $gl->Id }}" {{ old('ParentGLID') == $gl->Id ? 'selected' : '' }}>--}}
{{--                                        {{ $gl->GLCode }}--}}
{{--                                    </option>--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                            --}}{{-- <input type="hidden" name="TestField" value="I am working"> --}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="d-flex justify-content-between align-items-centre mt-1">
                        <a href="{{ route('chartofaccounts.index') }}" class="btn btn-secondary">Back</a>
                        <button class="btn btn-success" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Creating...'; this.form.submit();}">Create GL Account</button>
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
