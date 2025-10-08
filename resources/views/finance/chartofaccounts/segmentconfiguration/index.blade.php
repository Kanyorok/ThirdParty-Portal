@extends('layouts.app')
@section('title', 'COA Segments')

@section('content')
    {{-- Error Message --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm rounded-3 mt-3">
            <h6 class="fw-bold mb-2"><i class="fas fa-exclamation-circle me-1"></i> Please fix the following issues:</h6>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container mt-4 mb-5">

        {{-- Top Action Buttons --}}
        <div class="d-flex flex-wrap gap-2 justify-content-start mb-3">
            <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2 shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#segmentOrderModal">
                <i class="fas fa-sort"></i> Display Order
            </button>
            <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-2 shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#glDigitsModal">
                <i class="fas fa-calculator"></i> GL Digits
            </button>
        </div>

        {{-- Segment Values --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom fw-semibold fs-5 d-flex align-items-center">
                <i class="fas fa-layer-group text-primary me-2"></i> Segment Values Viewer
            </div>

            <div class="card-body">
                {{-- Sub-actions --}}
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-success d-flex align-items-center gap-2"
                            data-bs-toggle="modal" data-bs-target="#glTypeModal">
                        <i class="fas fa-plus-circle"></i> GL Type
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary d-flex align-items-center gap-2"
                            data-bs-toggle="modal" data-bs-target="#glAccountTypeModal">
                        <i class="fas fa-layer-group"></i> GL Account Type
                    </button>
                    <button type="button" class="btn btn-sm btn-info d-flex align-items-center gap-2"
                            data-bs-toggle="modal" data-bs-target="#glSubTypeModal">
                        <i class="fas fa-sitemap"></i> GL Sub-Type
                    </button>
                </div>

                {{-- Accordion Viewer --}}
                <div class="accordion" id="staticAccountTree">
                    @foreach($data as $glTypeDesc => $glType)
                        @php
                            $bgColor = match($glTypeDesc) {
                                'Assets' => 'bg-primary',
                                'Liabilities' => 'bg-warning',
                                'Income' => 'bg-success',
                                'Expenses' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                        @endphp

                        <div class="accordion-item border-0 shadow-sm mb-2 rounded-3 overflow-hidden">
                            <h2 class="accordion-header" id="heading{{ Str::slug($glTypeDesc) }}">
                                <button class="accordion-button collapsed fw-semibold" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{ Str::slug($glTypeDesc) }}"
                                        aria-expanded="false"
                                        aria-controls="collapse{{ Str::slug($glTypeDesc) }}">
                                    {{ $glTypeDesc }}
                                    <span class="badge {{ $bgColor }} ms-2">
                                        {{ $glType['SegmentValue'] }}
                                    </span>
                                </button>
                            </h2>

                            <div id="collapse{{ Str::slug($glTypeDesc) }}"
                                 class="accordion-collapse collapse"
                                 data-bs-parent="#staticAccountTree">
                                <div class="accordion-body bg-light-subtle ps-4">

                                    <div class="accordion" id="{{ Str::slug($glTypeDesc) }}SubAccordion">
                                        @foreach($glType['Children'] as $groupDesc => $group)
                                            <div class="accordion-item border-0 mb-2 shadow-sm rounded-3">
                                                <h2 class="accordion-header" id="heading{{ Str::slug($groupDesc) }}">
                                                    <button class="accordion-button collapsed py-2 small"
                                                            type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapse{{ Str::slug($groupDesc) }}"
                                                            aria-expanded="false"
                                                            aria-controls="collapse{{ Str::slug($groupDesc) }}">
                                                        {{ $groupDesc }}
                                                        <span class="badge bg-secondary ms-2">
                                                            {{ $group['SegmentValue'] ?? '' }}
                                                        </span>
                                                    </button>
                                                </h2>

                                                <div id="collapse{{ Str::slug($groupDesc) }}"
                                                     class="accordion-collapse collapse"
                                                     data-bs-parent="#{{ Str::slug($glTypeDesc) }}SubAccordion">
                                                    <div class="accordion-body ps-4">
                                                        <ul class="list-group list-group-flush">
                                                            @foreach($group['Children'] as $child)
                                                                <li class="list-group-item border-0 ps-0">
                                                                    <strong>{{ $child['SegmentValue'] ?? '' }}</strong>
                                                                    <span class="text-muted">— {{ $child['Description'] }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div> {{-- End Accordion --}}
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('finance.chartofaccounts.segmentconfiguration.modals.glDigits')
    @include('finance.chartofaccounts.segmentconfiguration.modals.segmentOrder')
    @include('finance.chartofaccounts.segmentconfiguration.modals.glType')
    @include('finance.chartofaccounts.segmentconfiguration.modals.glAccountType')
    @include('finance.chartofaccounts.segmentconfiguration.modals.glSubType')
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/Sortable/Sortable.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const segmentList = document.getElementById('segmentList');
            if (segmentList) {
                new Sortable(segmentList, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'bg-warning-subtle',
                });
            }
        });

        function submitSegmentOrder(button) {
            const form = button.form;
            const order = Array.from(document.querySelectorAll('#segmentList li'))
                .map(item => item.dataset.segment);

            document.getElementById('segmentOrderInput').value = JSON.stringify(order);

            if (form.checkValidity()) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                form.submit();
            }
        }
    </script>

    {{-- Dependent dropdowns --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accountType1 = document.getElementById('accountType1');
            const typeGroup1 = document.getElementById('typeGroup1');
            const subType1 = document.getElementById('subType1');

            typeGroup1.innerHTML = '<option disabled selected>-- GL Account Type --</option>';
            subType1.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';

            accountType1.addEventListener('change', function () {
                const typeID = this.value;
                typeGroup1.innerHTML = '<option disabled selected>Loading...</option>';
                subType1.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';

                fetch(`/finance/get-type-groups?GLAccountTypeID=${typeID}`)
                    .then(res => res.json())
                    .then(data => {
                        typeGroup1.innerHTML = '<option disabled selected>-- GL Account Type --</option>';
                        data.forEach(item => {
                            typeGroup1.innerHTML += `<option value="${item.Id}">${item.Description} (${item.SegmentValue ?? 'Not Set'})</option>`;
                        });
                    })
                    .catch(() => typeGroup1.innerHTML = '<option disabled selected>-- Error Loading --</option>');
            });

            typeGroup1.addEventListener('change', function () {
                const groupID = this.value;
                subType1.innerHTML = '<option disabled selected>Loading...</option>';

                fetch(`/finance/get-sub-account-types?GLTypeGroupID=${groupID}`)
                    .then(res => res.json())
                    .then(data => {
                        subType1.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';
                        data.forEach(item => {
                            subType1.innerHTML += `<option value="${item.Id}">${item.Description} (${item.SegmentValue ?? 'Not Set'})</option>`;
                        });
                    })
                    .catch(() => subType1.innerHTML = '<option disabled selected>-- Error Loading --</option>');
            });
        });
    </script>

    {{-- Another dropdown script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accountType = document.getElementById('accountType');
            const typeGroup = document.getElementById('typeGroup');

            typeGroup.innerHTML = '<option disabled selected>-- GL Account Type --</option>';

            accountType.addEventListener('change', function () {
                const typeID = this.value;
                typeGroup.innerHTML = '<option disabled selected>Loading...</option>';

                fetch(`/finance/get-type-groups?GLAccountTypeID=${typeID}`)
                    .then(res => res.json())
                    .then(data => {
                        typeGroup.innerHTML = '<option disabled selected>-- GL Account Type --</option>';
                        data.forEach(item => {
                            typeGroup.innerHTML += `<option value="${item.Id}">${item.Description} (${item.SegmentValue ?? 'Not Set'})</option>`;
                        });
                    })
                    .catch(() => typeGroup.innerHTML = '<option disabled selected>-- Error Loading --</option>');
            });
        });
    </script>
@endsection
