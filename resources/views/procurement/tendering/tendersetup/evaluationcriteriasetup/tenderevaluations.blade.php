@extends('layouts.app')
@section('title', 'Tenders Sections')
@section('content')

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>📑 Tender Section Settings</h4>
            <div class="d-flex align-items-center">
                <label for="perPage" class="me-2 mb-0">Show</label>
                <form id="perPageForm" method="GET" class="me-3">
                    <select id="perPage" name="perPage" class="form-select form-select-sm" onchange="document.getElementById('perPageForm').submit()">
                        @php $currentPer = (int) request()->query('perPage', 10); @endphp
                        @foreach([5,10,15,20,25] as $p)
                            <option value="{{ $p }}" {{ $currentPer === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addSection1Modal">
                    + Tender Criteria</a>
            </div>
        </div>

        <!-- Index Table -->
            <div class="table-responsive">
            @php
                // Build a paginator from $data (collection) so we can support per-page selection without controller changes
                $collection = collect($data ?? []);
                // Show the last record first by reversing the collection (preserve indexes)
                if ($collection->isNotEmpty()) {
                    $collection = $collection->reverse()->values();
                }
                $perPage = max(1, (int) request()->query('perPage', 10));
                $page = max(1, (int) request()->query('page', 1));
                $slice = $collection->slice(($page - 1) * $perPage, $perPage)->values();
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator($slice, $collection->count(), $perPage, $page, [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]);
            @endphp
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Ref</th>
                    <th>Sections</th>
                    <th>Criteria Items</th>
                    <th>Total Weight</th>
                    <th>Max Score</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <!-- Example Row -->
                @foreach ($paginator as $item)
                    <tr>
                        <td>{{ $paginator->firstItem() + $loop->index }}</td>
                        <td>
                            <strong>{{ $item['TenderNo'] }}</strong>
                            @if(!empty($item['Title']))
                                - {{ $item['Title'] }}
                            @endif
                        </td>
                        <td>{{$item['sectionsNumber']}}</td>
                        <td>
                            <a href="{{route('tender-criteria',$item['id'])}}"
                               class="btn btn-sm">{{$item['criteriaNumber']}} <i class="fa fa-eye"
                                                                                 style="font-size:18px;color:rgb(63, 63, 252)"></i></a>
                        </td>
                        <td>100</td>
                        <td>{{$item['criteriaNumber']*10}}</td>
                        <td>
                            <a href="{{route('tender-criteria',$item['id'])}}" class="btn btn-sm btn-outline-secondary">Criterias</a>
                            {{-- <a href="/evaluation-criteria/view/1" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="/evaluation-criteria/edit/1" class="btn btn-sm btn-outline-success">Edit</a>
                            <button class="btn btn-sm btn-outline-danger">Delete</button> --}}
                        </td>
                    </tr>

                @endforeach

                </tbody>
            </table>

            {{-- Pagination links --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $paginator->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div class="modal fade" id="addSection1Modal" tabindex="-1" aria-labelledby="addSectionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Add New Criteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{route('store-tender-sections')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Tender Title</label>
                                <select name="tender_id" class="form-control form-select" id="tender_id" required>
                                    <option selected disabled>--Select Tender title--</option>
                                    @foreach ($tenders as $item)
                                        <option value="{{ $item->Id }}">{{ $item->TenderNo }}
                                            | {{ $item->Title }}</option>
                                    @endforeach
                                </select>
                                @error('tender_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead class="table-light">
                            <tr>
                                <th>Include</th>
                                <th>Criteria</th>
                                <th>Weight (%)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($sections as $item)
                                <tr>
                                    <!-- Use the correct Section Id and key weights by SectionID so backend can map them -->
                                    <td><input type="checkbox" name="sections[]" value="{{$item->Id}}"></td>
                                    @error('sections')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <td>{{$item->SectionName}}</td>
                                    <td>
                                        <input
                                            type="number"
                                            class="form-control weight-input"
                                            name="weights[{{$item->Id}}]"
                                            value="0.00"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                        >
                                    </td>
                                    @error('weights')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Total</td>
                                <td>
                                    <strong id="totalWeight">0.00</strong>%
                                    <span id="totalBadge" class="badge bg-secondary ms-2">Needs 100%</span>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button
                            type="submit"
                            class="btn btn-success"
                            id="saveCriteriaBtn"
                        >
                            Save Criteria
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Inline JavaScript to enforce 100% weight -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('addSection1Modal');
            if (!modal) return;

            const form = modal.querySelector('form');
            const totalWeightDisplay = document.getElementById('totalWeight');
            const totalBadge = document.getElementById('totalBadge');
            const submitBtn = document.getElementById('saveCriteriaBtn');

            function updateTotal() {
                let total = 0;
                const rows = form.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    const weightInput = row.querySelector('input[type="number"]');

                    if (checkbox.checked) {
                        weightInput.disabled = false;
                        const v = parseFloat(weightInput.value);
                        if (!isNaN(v)) total += v;
                    } else {
                        // Disable and clear to avoid posting stray weights for unselected sections
                        weightInput.disabled = true;
                    }
                });

                totalWeightDisplay.textContent = total.toFixed(2);

                const ok = Math.abs(total - 100) < 0.005; // allow tiny FP tolerance
                // Badge and button state
                if (totalBadge) {
                    totalBadge.textContent = ok ? 'OK' : 'Needs 100%';
                    totalBadge.className = 'badge ms-2 ' + (ok ? 'bg-success' : (total > 100 ? 'bg-danger' : 'bg-warning'));
                }
                if (submitBtn) submitBtn.disabled = !ok;
                return total;
            }

            // Listen to checkbox and number input changes
            form.querySelectorAll('tbody tr').forEach(row => {
                const checkbox = row.querySelector('input[type="checkbox"]');
                const weightInput = row.querySelector('input[type="number"]');

                checkbox.addEventListener('change', updateTotal);
                weightInput.addEventListener('input', updateTotal);
            });

            // Validate on submit
            form.addEventListener('submit', function (e) {
                const total = updateTotal();
                if (total.toFixed(2) !== '100.00') {
                    e.preventDefault();
                    alert('Total weight must be exactly 100.00%. Current total: ' + total.toFixed(2) + '%');
                }
            });

            // Initialize on load
            updateTotal();
        });
    </script>

@endsection
