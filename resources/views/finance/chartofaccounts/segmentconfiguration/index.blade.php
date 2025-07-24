@extends('layouts.app')
@section('title', 'Segment Configuration')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some errors with your submission:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container mt-2">

        <!-- Modal Trigger Buttons -->
        <nav class="space-y-2 mb-2">
            <button type="button" class="w-full btn btn-primary flex items-center transition-transform transform hover:scale-105" data-bs-toggle="modal" data-bs-target="#segmentOrderModal">
                <i class="fas fa-sort mr-2"></i> Display Order
            </button>
            <button type="button" class="w-full btn btn-primary flex items-center transition-transform transform hover:scale-105" data-bs-toggle="modal" data-bs-target="#glDigitsModal">
                <i class="fas fa-calculator mr-2"></i> GL Digits
            </button>
        </nav>

        <!-- Segment Values -->
        <div class="card">
            <h4 class="card-header"><i class="fas fa-layer-group me-1"></i> Segment Values Viewer</h4>
            <div class="card-body">

                <div class="space-x-2 mb-2">
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#glTypeModal" title="Add GL Type">
                        GL Type
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#glAccountTypeModal" title="Add GL Account Type">
                        GL Account Type
                    </button>
                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#glSubTypeModal" title="Add GL Account Sub-Type">
                        GL Account Sub-Type
                    </button>
                </div>

                <div class="container mt-4">

                    <div class="card">
                        <div class="card-body">
                            <div class="accordion" id="staticAccountTree">

                                {{-- Assets,Liabilities, Income, Expense --}}
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

                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{Str::slug($glTypeDesc)}}">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapse{{Str::slug($glTypeDesc)}}" aria-expanded="false"
                                                    aria-controls="collapse{{Str::slug($glTypeDesc)}}">
                                                <span>{{ $glTypeDesc }} <span class="badge {{ $bgColor }} ms-2">{{ $glType['SegmentValue'] }}</span></span>
                                            </button>
                                        </h2>
                                        <div id="collapse{{Str::slug($glTypeDesc)}}" class="accordion-collapse collapse"
                                             aria-labelledby="heading{{Str::slug($glTypeDesc)}}" data-bs-parent="#staticAccountTree">
                                            <div class="accordion-body ps-4">

                                                <div class="accordion mb-3" id="{{Str::slug($glTypeDesc)}}SubAccordion">
                                                    @foreach($glType['Children'] as $groupDesc => $group)
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="heading{{Str::slug($groupDesc)}}">
                                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                        data-bs-target="#collapse{{Str::slug($groupDesc)}}" aria-expanded="false"
                                                                        aria-controls="collapse{{Str::slug($groupDesc)}}">
                                    <span>{{ $groupDesc }}
                                        <span class="badge bg-secondary ms-2">
                                            {{ $group['SegmentValue'] ?? '' }}
                                        </span>
                                    </span>
                                                                </button>
                                                            </h2>
                                                            <div id="collapse{{Str::slug($groupDesc)}}" class="accordion-collapse collapse"
                                                                 aria-labelledby="heading{{Str::slug($groupDesc)}}" data-bs-parent="#{{Str::slug($glTypeDesc)}}SubAccordion">
                                                                <div class="accordion-body ps-4">
                                                                    <ul class="list-group list-group-flush">
                                                                        @foreach($group['Children'] as $child)
                                                                            <li class="list-group-item">
                                                                                <strong>{{ $child['SegmentValue'] ?? '' }}</strong> – {{ $child['Description'] }}
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


                            </div> <!-- End Accordion -->
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal for GL Digits -->
    @include('finance.chartofaccounts.segmentconfiguration.modals.glDigits')

    <!-- Modal for Segment Order -->
    @include('finance.chartofaccounts.segmentconfiguration.modals.segmentOrder')

    <!-- Modal for Gltype -->
    @include('finance.chartofaccounts.segmentconfiguration.modals.glType')

    <!-- Modal for GLAccountType -->
    @include('finance.chartofaccounts.segmentconfiguration.modals.glAccountType')

    <!-- Modal for GLSubType -->
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

            // Extract SegmentType and Description
            const items = document.querySelectorAll('#segmentList li');
            const order = Array.from(items).map(item => item.dataset.segment); // just SegmentType

            document.getElementById('segmentOrderInput').value = JSON.stringify(order);

            // Submit safely
            if (form.checkValidity()) {
                button.disabled = true;
                button.innerText = 'Saving...';
                form.submit();
            }
        }
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accountType1 = document.getElementById('accountType1');
            const typeGroup1 = document.getElementById('typeGroup1');
            const subType1 = document.getElementById('subType1');

            // Reset child selects initially
            typeGroup1.innerHTML = '<option disabled selected>-- GL Account Type --</option>';
            subType1.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';

            accountType1.addEventListener('change', function () {
                const typeID = this.value;

                // Reset children
                typeGroup1.innerHTML = '<option disabled selected>Loading...</option>';
                subType1.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';

                fetch(`/finance/get-type-groups?GLAccountTypeID=${typeID}`)
                    .then(res => res.json())
                    .then(data => {
                        typeGroup1.innerHTML = '<option disabled selected>-- GL Account Type --</option>';
                        data.forEach(item => {
                            typeGroup1.innerHTML += `<option value="${item.Id}">${item.Description}  (${item.SegmentValue==null?'Not Set':item.SegmentValue})</option>`;
                        });
                    })
                    .catch(err => {
                        console.error('Failed to load type groups', err);
                        typeGroup1.innerHTML = '<option disabled selected>-- Error Loading --</option>';
                    });
            });

            typeGroup1.addEventListener('change', function () {
                const groupID = this.value;

                // Reset subType
                subType1.innerHTML = '<option disabled selected>Loading...</option>';

                fetch(`/finance/get-sub-account-types?GLTypeGroupID=${groupID}`)
                    .then(res => res.json())
                    .then(data => {
                        subType1.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';
                        data.forEach(item => {
                            subType1.innerHTML += `<option value="${item.Id}">${item.Description}  (${item.SegmentValue==null?'Not Set':item.SegmentValue})</option>`;
                        });
                    })
                    .catch(err => {
                        console.error('Failed to load sub account types', err);
                        subType1.innerHTML = '<option disabled selected>-- Error Loading --</option>';
                    });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accountType = document.getElementById('accountType');
            const typeGroup = document.getElementById('typeGroup');
            //const subType = document.getElementById('subType');

            // Reset child selects initially
            typeGroup.innerHTML = '<option disabled selected>-- GL Account Type --</option>';
            //subType.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';

            accountType.addEventListener('change', function () {
                const typeID = this.value;

                // Reset children
                typeGroup.innerHTML = '<option disabled selected>Loading...</option>';
                // subType.innerHTML = '<option disabled selected>-- GL Sub Account Type --</option>';

                fetch(`/finance/get-type-groups?GLAccountTypeID=${typeID}`)
                    .then(res => res.json())
                    .then(data => {
                        console.log("First:",data)
                        typeGroup.innerHTML = '<option disabled selected>-- GL Account Type --</option>';
                        data.forEach(item => {
                            typeGroup.innerHTML += `<option value="${item.Id}">${item.Description} (${item.SegmentValue==null?'Not Set':item.SegmentValue})</option>`;
                        });
                    })
                    .catch(err => {
                        console.error('Failed to load type groups', err);
                        typeGroup.innerHTML = '<option disabled selected>-- Error Loading --</option>';
                    });
            });
        });
    </script>

@endsection
