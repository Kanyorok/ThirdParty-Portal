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
                    + Sections</a>
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
                        <td>
                            @php $names = $item['sectionNames'] ?? []; @endphp
                            @if(($item['sectionsNumber'] ?? 0) > 0)
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#sectionsModal"
                                        data-tenderref="{{ $item['TenderNo'] }}"
                                        data-sections='@json($names)'>
                                    {{ $item['sectionsNumber'] }}
                                </button>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $criteriaNames = $item['criteriaNames'] ?? [];
                                $criteriaBySection = $item['criteriaBySection'] ?? [];
                            @endphp
                            @if(($item['criteriaNumber'] ?? 0) > 0)
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#criteriaModal"
                                        data-tenderref="{{ $item['TenderNo'] }}"
                                        data-criteria='@json($criteriaNames)'
                                        data-criteria-grouped='@json($criteriaBySection)'>
                                    {{ $item['criteriaNumber'] }}
                                </button>
                            @else
                                <span class="text-muted">0</span>
                            @endif
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

    <!-- Sections Modal -->
    <div class="modal fade" id="sectionsModal" tabindex="-1" aria-labelledby="sectionsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="sectionsModalLabel">Tender Sections</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="sectionsList" class="list-group list-group-flush"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Criteria Items Modal -->
    <div class="modal fade" id="criteriaModal" tabindex="-1" aria-labelledby="criteriaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="criteriaModalLabel">Criteria Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="criteriaList" class="list-group list-group-flush"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div class="modal fade" id="addSection1Modal" tabindex="-1" aria-labelledby="addSectionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Add New Sections</h5>
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
                                <th>Sections</th>
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
                            Save Sections
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Inline JavaScript to enforce 100% weight -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sections modal population
            const sectionsModal = document.getElementById('sectionsModal');
            if (sectionsModal) {
                sectionsModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const tenderRef = button?.getAttribute('data-tenderref') || '';
                    const namesJson = button?.getAttribute('data-sections') || '[]';
                    let names = [];
                    try { names = JSON.parse(namesJson); } catch (_) { names = []; }

                    const list = sectionsModal.querySelector('#sectionsList');
                    list.innerHTML = '';
                    if (Array.isArray(names) && names.length) {
                        names.forEach(n => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item';
                            li.textContent = n;
                            list.appendChild(li);
                        });
                    } else {
                        const li = document.createElement('li');
                        li.className = 'list-group-item text-muted';
                        li.textContent = 'No sections found.';
                        list.appendChild(li);
                    }

                    const title = sectionsModal.querySelector('#sectionsModalLabel');
                    if (title) title.textContent = `Tender Sections${tenderRef ? ' — ' + tenderRef : ''}`;
                });
            }

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

            // Criteria modal population (grouped by Section if provided)
            const criteriaModal = document.getElementById('criteriaModal');
            if (criteriaModal) {
                criteriaModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const tenderRef = button?.getAttribute('data-tenderref') || '';
                    const namesJson = button?.getAttribute('data-criteria') || '[]';
                    const groupedJson = button?.getAttribute('data-criteria-grouped') || '{}';
                    let names = [];
                    let grouped = {};
                    try { names = JSON.parse(namesJson); } catch (_) { names = []; }
                    try { grouped = JSON.parse(groupedJson); } catch (_) { grouped = {}; }

                    const list = criteriaModal.querySelector('#criteriaList');
                    list.innerHTML = '';
                    const groupedKeys = grouped && typeof grouped === 'object' ? Object.keys(grouped) : [];

                    if (groupedKeys.length) {
                        // Render grouped by section
                        groupedKeys.forEach(sectionName => {
                            // Section heading
                            const header = document.createElement('li');
                            header.className = 'list-group-item fw-bold bg-light';
                            header.textContent = sectionName || 'Unnamed Section';
                            list.appendChild(header);

                            const items = Array.isArray(grouped[sectionName]) ? grouped[sectionName] : [];
                            // Normalize to objects and keep only selected when a flag exists
                            const selectedItems = items
                                .map(v => typeof v === 'string' ? { name: v, selected: true } : v)
                                .filter(v => v && (v.selected === undefined ? true : !!v.selected));

                            if (selectedItems.length) {
                                selectedItems.forEach(v => {
                                    const li = document.createElement('li');
                                    li.className = 'list-group-item text-danger'; // red text for chosen items
                                    li.textContent = v.name ?? v;
                                    list.appendChild(li);
                                });
                            } else {
                                const li = document.createElement('li');
                                li.className = 'list-group-item text-muted';
                                li.textContent = 'No criteria selected.';
                                list.appendChild(li);
                            }
                        });
                    } else if (Array.isArray(names) && names.length) {
                        // Fallback: flat list
                        names.forEach(n => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item text-danger';
                            li.textContent = n;
                            list.appendChild(li);
                        });
                    } else {
                        const li = document.createElement('li');
                        li.className = 'list-group-item text-muted';
                        li.textContent = 'No criteria items found.';
                        list.appendChild(li);
                    }

                    const title = criteriaModal.querySelector('#criteriaModalLabel');
                    if (title) title.textContent = `Criteria Items${tenderRef ? ' — ' + tenderRef : ''}`;
                });
            }
        });
    </script>

@endsection
