@extends('layouts.app')
@section('title', 'COA Segments')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger rounded-3 shadow-sm">
            <strong>There were some errors with your submission:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container mt-3">

        <!-- Action Buttons Row -->
        <button type="button" class="btn btn-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#segmentOrderModal">
            <i class="fas fa-sort me-1"></i> Display Order
        </button>
        <button type="button" class="btn btn-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#glDigitsModal">
            <i class="fas fa-calculator me-1"></i> GL Digits
        </button>

        <!-- Segment Values Viewer -->
        <div class="card shadow-sm rounded-4 mt-2">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-layer-group me-2"></i> Segment Values
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#glTypeModal">
                        + GL Type
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#glAccountTypeModal">
                        + GL Account Type
                    </button>
                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#glSubTypeModal">
                        + GL Sub-Type
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="accordion" id="staticAccountTree">
                    {{-- Loop through Assets, Liabilities, Income, Expenses --}}
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

                        <div class="accordion-item rounded-3 overflow-hidden mb-2 shadow-sm">
                            <h2 class="accordion-header" id="heading{{Str::slug($glTypeDesc)}}">
                                <button class="accordion-button collapsed fw-semibold" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{Str::slug($glTypeDesc)}}"
                                        aria-expanded="false"
                                        aria-controls="collapse{{Str::slug($glTypeDesc)}}">
                                    {{ $glTypeDesc }}
                                    <span class="badge {{ $bgColor }} ms-2">
                                        {{ $glType['SegmentValue'] }}
                                    </span>
                                </button>
                            </h2>

                            <div id="collapse{{Str::slug($glTypeDesc)}}" class="accordion-collapse collapse"
                                 aria-labelledby="heading{{Str::slug($glTypeDesc)}}"
                                 data-bs-parent="#staticAccountTree">
                                <div class="accordion-body ps-4">
                                    <div class="accordion" id="{{Str::slug($glTypeDesc)}}SubAccordion">
                                        @foreach($glType['Children'] as $groupDesc => $group)
                                            <div class="accordion-item border-0 mb-1">
                                                <h2 class="accordion-header" id="heading{{Str::slug($groupDesc)}}">
                                                    <button class="accordion-button collapsed small" type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapse{{Str::slug($groupDesc)}}"
                                                            aria-expanded="false"
                                                            aria-controls="collapse{{Str::slug($groupDesc)}}">
                                                        {{ $groupDesc }}
                                                        <span class="badge bg-secondary ms-2">
                                                            {{ $group['SegmentValue'] ?? '' }}
                                                        </span>
                                                    </button>
                                                </h2>

                                                <div id="collapse{{Str::slug($groupDesc)}}" class="accordion-collapse collapse"
                                                     aria-labelledby="heading{{Str::slug($groupDesc)}}"
                                                     data-bs-parent="#{{Str::slug($glTypeDesc)}}SubAccordion">
                                                    <div class="accordion-body ps-4">
                                                        <ul class="list-group list-group-flush">
                                                            @foreach($group['Children'] as $child)
                                                                <li class="list-group-item small">
                                                                    <strong>{{ $child['SegmentValue'] ?? '' }}</strong>
                                                                    <span class="text-muted"> – {{ $child['Description'] }}</span>
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

    <!-- Modals -->
    @include('finance.chartofaccounts.segmentconfiguration.modals.glDigits')
    @include('finance.chartofaccounts.segmentconfiguration.modals.segmentOrder')
    @include('finance.chartofaccounts.segmentconfiguration.modals.glType')
    @include('finance.chartofaccounts.segmentconfiguration.modals.glAccountType')
    @include('finance.chartofaccounts.segmentconfiguration.modals.glSubType')

@endsection
