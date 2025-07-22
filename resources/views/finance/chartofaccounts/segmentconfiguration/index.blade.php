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
                                @foreach($accountTypes as $glType)
                                    @php
                                        //Get the BG Color code for GLTYPE
                                        $bgColor='';
                                        if($glType->Value=='A'){
                                            $bgColor='bg-primary';
                                        }elseif($glType->Value=='L'){
                                            $bgColor='bg-warning';
                                        }elseif($glType->Value=='I'){
                                            $bgColor='bg-success';
                                        }elseif($glType->Value=='E'){
                                            $bgColor='bg-danger';
                                        }
                                    @endphp
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingAssets">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseAssets" aria-expanded="false"
                                                    aria-controls="collapseAssets">
                                                <span>{{$glType->Description}} <span class="badge {{$bgColor}} ms-2">{{$glType->DisplayOrder}}</span></span>
                                            </button>
                                        </h2>
                                        <div id="collapseAssets" class="accordion-collapse collapse"
                                             aria-labelledby="headingAssets" data-bs-parent="#staticAccountTree">
                                            <div class="accordion-body ps-4">

                                                {{-- GL Account Type --}}
                                                <div class="accordion mb-3" id="assetsSubAccordion">
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="headingFixedAssets">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseFixedAssets" aria-expanded="false"
                                                                    aria-controls="collapseFixedAssets">
                                                                <span>Fixed Assets <span class="badge bg-secondary ms-2">1100</span></span>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseFixedAssets" class="accordion-collapse collapse"
                                                             aria-labelledby="headingFixedAssets" data-bs-parent="#assetsSubAccordion">
                                                            <div class="accordion-body ps-4">
                                                                <ul class="list-group list-group-flush">
                                                                    <li class="list-group-item"><strong>1110</strong> – Equipment</li>
                                                                    <li class="list-group-item"><strong>1120</strong> – Furniture</li>
                                                                    <li class="list-group-item"><strong>1130</strong> – Buildings</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Other Asset Category --}}
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="headingCashAssets">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                    data-bs-target="#collapseCashAssets" aria-expanded="false"
                                                                    aria-controls="collapseCashAssets">
                                                                <span>Current Assets <span class="badge bg-secondary ms-2">1200</span></span>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseCashAssets" class="accordion-collapse collapse"
                                                             aria-labelledby="headingCashAssets" data-bs-parent="#assetsSubAccordion">
                                                            <div class="accordion-body ps-4">
                                                                <ul class="list-group list-group-flush">
                                                                    <li class="list-group-item"><strong>1210</strong> – Cash at Bank</li>
                                                                    <li class="list-group-item"><strong>1220</strong> – Accounts Receivable</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                @endforeach


                                {{-- Assets --}}
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingAssets">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseAssets" aria-expanded="true"
                                                aria-controls="collapseAssets">
                                            <span>Assets <span class="badge bg-primary ms-2">1000</span></span>
                                        </button>
                                    </h2>
                                    <div id="collapseAssets" class="accordion-collapse collapse show"
                                         aria-labelledby="headingAssets" data-bs-parent="#staticAccountTree">
                                        <div class="accordion-body ps-4">

                                            {{-- Fixed Assets --}}
                                            <div class="accordion mb-3" id="assetsSubAccordion">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="headingFixedAssets">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#collapseFixedAssets" aria-expanded="false"
                                                                aria-controls="collapseFixedAssets">
                                                            <span>Fixed Assets <span class="badge bg-secondary ms-2">1100</span></span>
                                                        </button>
                                                    </h2>
                                                    <div id="collapseFixedAssets" class="accordion-collapse collapse"
                                                         aria-labelledby="headingFixedAssets" data-bs-parent="#assetsSubAccordion">
                                                        <div class="accordion-body ps-4">
                                                            <ul class="list-group list-group-flush">
                                                                <li class="list-group-item"><strong>1110</strong> – Equipment</li>
                                                                <li class="list-group-item"><strong>1120</strong> – Furniture</li>
                                                                <li class="list-group-item"><strong>1130</strong> – Buildings</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Other Asset Category --}}
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="headingCashAssets">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#collapseCashAssets" aria-expanded="false"
                                                                aria-controls="collapseCashAssets">
                                                            <span>Current Assets <span class="badge bg-secondary ms-2">1200</span></span>
                                                        </button>
                                                    </h2>
                                                    <div id="collapseCashAssets" class="accordion-collapse collapse"
                                                         aria-labelledby="headingCashAssets" data-bs-parent="#assetsSubAccordion">
                                                        <div class="accordion-body ps-4">
                                                            <ul class="list-group list-group-flush">
                                                                <li class="list-group-item"><strong>1210</strong> – Cash at Bank</li>
                                                                <li class="list-group-item"><strong>1220</strong> – Accounts Receivable</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                {{-- Liabilities --}}
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingLiabilities">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseLiabilities" aria-expanded="false"
                                                aria-controls="collapseLiabilities">
                                            <span>Liabilities <span class="badge bg-warning ms-2">2000</span></span>
                                        </button>
                                    </h2>
                                    <div id="collapseLiabilities" class="accordion-collapse collapse"
                                         aria-labelledby="headingLiabilities" data-bs-parent="#staticAccountTree">
                                        <div class="accordion-body ps-4">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><strong>2100</strong> – Accounts Payable</li>
                                                <li class="list-group-item"><strong>2200</strong> – Accrued Expenses</li>
                                                <li class="list-group-item"><strong>2300</strong> – Short-Term Loans</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Income --}}
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingIncome">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseIncome" aria-expanded="false"
                                                aria-controls="collapseIncome">
                                            <span>Income <span class="badge bg-success ms-2">3000</span></span>
                                        </button>
                                    </h2>
                                    <div id="collapseIncome" class="accordion-collapse collapse"
                                         aria-labelledby="headingIncome" data-bs-parent="#staticAccountTree">
                                        <div class="accordion-body ps-4">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><strong>3100</strong> – Product Sales</li>
                                                <li class="list-group-item"><strong>3200</strong> – Service Revenue</li>
                                                <li class="list-group-item"><strong>3300</strong> – Interest Income</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Expenses --}}
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingExpenses">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseExpenses" aria-expanded="false"
                                                aria-controls="collapseExpenses">
                                            <span>Expenses <span class="badge bg-danger ms-2">4000</span></span>
                                        </button>
                                    </h2>
                                    <div id="collapseExpenses" class="accordion-collapse collapse"
                                         aria-labelledby="headingExpenses" data-bs-parent="#staticAccountTree">
                                        <div class="accordion-body ps-4">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><strong>4100</strong> – Salaries & Wages</li>
                                                <li class="list-group-item"><strong>4200</strong> – Rent Expense</li>
                                                <li class="list-group-item"><strong>4300</strong> – Utilities</li>
                                                <li class="list-group-item"><strong>4400</strong> – Office Supplies</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

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
                            typeGroup1.innerHTML += `<option value="${item.Id}">${item.Description}</option>`;
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
                            subType1.innerHTML += `<option value="${item.Id}">${item.Description}</option>`;
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
        });
    </script>

@endsection
