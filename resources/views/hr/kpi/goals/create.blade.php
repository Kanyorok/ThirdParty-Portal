@extends('layouts.app')

@section('title', 'New KPI Goal Set')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create KPI Goals</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.kpi.goals.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('hr.kpi.goals.store') }}">
        @csrf
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employee *</label>
                        <select name="EmployeeID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->Id }}" data-grade="{{ $emp->GradeID }}" data-role="{{ $emp->RoleID }}" @selected(old('EmployeeID')==$emp->Id)>
                                    {{ $emp->FirstName }} {{ $emp->LastName }} ({{ $emp->EmployeeNo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Period *</label>
                        <select name="PeriodID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($periods as $period)
                                <option value="{{ $period->Id }}" @selected(old('PeriodID')==$period->Id)>{{ $period->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Year *</label>
                        <select name="PeriodYear" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($yearOptions as $year)
                                <option value="{{ $year }}" @selected(old('PeriodYear')==$year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Segment *</label>
                        <select name="PeriodSegment" class="form-select" data-selected="{{ old('PeriodSegment') }}">
                            <option value="">Select Period First</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <textarea name="Notes" rows="2" class="form-control">{{ old('Notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Perspective</label>
                        <select id="pickerPerspective" class="form-select">
                            <option value="">Select Perspective</option>
                            @foreach($perspectives as $perspective)
                                <option value="{{ $perspective->Id }}">{{ $perspective->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <div class="small text-muted" id="pickerWeight">Target weight: -</div>
                        <div class="d-flex flex-wrap gap-3 mt-2" id="pickerKpis"></div>
                    </div>
                </div>
                <div class="small text-muted mt-2" id="perspectiveWeightList"></div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h5 class="mb-0">Goal Items</h5>
                <button class="btn btn-sm btn-outline-primary" type="button" id="addItem">+ Add Item</button>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 15%">Perspective</th>
                            <th style="width: 25%">KPI Item</th>
                            <th style="width: 15%">Annual Target</th>
                            <th style="width: 15%">Period Target</th>
                            <th style="width: 15%">Weight</th>
                            <th>Notes</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td>
                                <select name="Items[0][PerspectiveID]" class="form-select kpi-perspective">
                                    <option value="">Select Perspective</option>
                                    @foreach($perspectives as $perspective)
                                        <option value="{{ $perspective->Id }}">{{ $perspective->Name }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-1">
                                    <button type="button" class="btn btn-link btn-sm p-0 distribute-weights">Distribute evenly</button>
                                </div>
                            </td>
                            <td>
                                <select name="Items[0][KpiItemID]" class="form-select kpi-item" required>
                                    <option value="">Select Perspective First</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" name="Items[0][AnnualTarget]" class="form-control"></td>
                            <td><input type="number" step="0.01" name="Items[0][PeriodTarget]" class="form-control"></td>
                            <td><input type="number" step="0.01" name="Items[0][Weight]" class="form-control kpi-weight" required></td>
                            <td><input type="text" name="Items[0][Notes]" class="form-control"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
                        </tr>
                    </tbody>
                </table>
                <div class="border-top p-3">
                    <div class="small text-muted" id="weightSummary">Total KPI weight: 0 / 100</div>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end gap-2">
            <button type="submit" name="Action" value="draft" class="btn btn-outline-secondary">Save Draft</button>
            <button type="submit" name="Action" value="submit" class="btn btn-primary">Submit for Approval</button>
        </div>
    </form>
</div>

<script>
    (function () {
        const table = document.getElementById('itemsTable').querySelector('tbody');
        const addBtn = document.getElementById('addItem');
        const employeeSelect = document.querySelector('select[name="EmployeeID"]');
        const periodSelect = document.querySelector('select[name="PeriodID"]');
        const segmentSelect = document.querySelector('select[name="PeriodSegment"]');
        const pickerPerspective = document.getElementById('pickerPerspective');
        const pickerWeight = document.getElementById('pickerWeight');
        const pickerKpis = document.getElementById('pickerKpis');
        const perspectiveWeightList = document.getElementById('perspectiveWeightList');
        let index = 1;
        const kpiItems = @json($kpiItemsPayload);
        const perspectives = @json($perspectivesPayload);
        const perspectiveWeights = @json($perspectiveWeightsPayload);
        const periods = @json($periodsPayload);

        function bindRow(row) {
            row.querySelector('.remove-row').addEventListener('click', function () {
                if (table.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    updateWeightSummary();
                    renderPickerKpis();
                }
            });
            const perspectiveSelect = row.querySelector('.kpi-perspective');
            const kpiSelect = row.querySelector('.kpi-item');
            const weightInput = row.querySelector('.kpi-weight');
            const distributeBtn = row.querySelector('.distribute-weights');
            perspectiveSelect.addEventListener('change', function () {
                populateKpisForRow(row);
            });
            kpiSelect.addEventListener('change', function () {
                updateWeightSummary();
            });
            distributeBtn?.addEventListener('click', function () {
                distributeEvenlyForRow(row);
            });
        }

        function getSelectedEmployeeMeta() {
            const option = employeeSelect?.selectedOptions?.[0];
            const gradeId = option?.dataset?.grade ? parseInt(option.dataset.grade, 10) : null;
            const roleId = option?.dataset?.role ? parseInt(option.dataset.role, 10) : null;
            return { gradeId, roleId };
        }

        function getSelectedPeriodId() {
            const value = periodSelect?.value;
            return value ? parseInt(value, 10) : null;
        }

        function buildSegmentLabel(count, index) {
            if (count === 4) {
                return `Q${index}`;
            }
            if (count === 2) {
                return `H${index}`;
            }
            if (count === 12) {
                return `M${index}`;
            }
            if (count === 1) {
                return 'Full Year';
            }
            return `Segment ${index}`;
        }

        function updateSegmentOptions() {
            const selectedPeriod = getSelectedPeriodId();
            const selectedValue = segmentSelect?.dataset?.selected ? parseInt(segmentSelect.dataset.selected, 10) : null;
            segmentSelect.innerHTML = '';
            if (!selectedPeriod) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Select Period First';
                segmentSelect.appendChild(option);
                return;
            }
            const period = periods.find(p => p.Id === selectedPeriod);
            const count = period?.SegmentCount ?? 1;
            for (let i = 1; i <= count; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = buildSegmentLabel(count, i);
                if (selectedValue && i === selectedValue) {
                    option.selected = true;
                }
                segmentSelect.appendChild(option);
            }
            if (!segmentSelect.value && count >= 1) {
                segmentSelect.value = '1';
            }
        }

        function resolveAllowedPerspectives(periodId, gradeId, roleId) {
            if (!periodId) {
                return perspectives.map(p => p.Id);
            }
            const byPerspective = {};
            perspectiveWeights.forEach(row => {
                if (Number(row.PeriodID) !== periodId) {
                    return;
                }
                const pid = row.PerspectiveID;
                if (!byPerspective[pid]) {
                    byPerspective[pid] = [];
                }
                byPerspective[pid].push(row);
            });
            const allowed = [];
            Object.keys(byPerspective).forEach(key => {
                const pid = parseInt(key, 10);
                const rows = byPerspective[pid];
                let match = rows.find(r => r.GradeID == gradeId && r.RoleID == roleId);
                if (!match && gradeId !== null) {
                    match = rows.find(r => r.GradeID == gradeId && r.RoleID == null);
                }
                if (!match && roleId !== null) {
                    match = rows.find(r => r.GradeID == null && r.RoleID == roleId);
                }
                if (!match) {
                    match = rows.find(r => r.GradeID == null && r.RoleID == null);
                }
                if (match) {
                    allowed.push(pid);
                }
            });
            return allowed;
        }

        function normalizeWeight(value) {
            const weight = Number(value) || 0;
            return weight <= 1 ? weight * 100 : weight;
        }

        function resolvePerspectiveWeightMap(periodId, gradeId, roleId) {
            if (!periodId) {
                return {};
            }
            const byPerspective = {};
            perspectiveWeights.forEach(row => {
                if (Number(row.PeriodID) !== periodId) {
                    return;
                }
                const pid = row.PerspectiveID;
                if (!byPerspective[pid]) {
                    byPerspective[pid] = [];
                }
                byPerspective[pid].push(row);
            });
            const resolved = {};
            Object.keys(byPerspective).forEach(key => {
                const pid = parseInt(key, 10);
                const rows = byPerspective[pid];
                let match = rows.find(r => r.GradeID == gradeId && r.RoleID == roleId);
                if (!match && gradeId !== null) {
                    match = rows.find(r => r.GradeID == gradeId && r.RoleID == null);
                }
                if (!match && roleId !== null) {
                    match = rows.find(r => r.GradeID == null && r.RoleID == roleId);
                }
                if (!match) {
                    match = rows.find(r => r.GradeID == null && r.RoleID == null);
                }
                if (match) {
                    resolved[pid] = normalizeWeight(match.Weight);
                }
            });
            return resolved;
        }

        function buildPerspectiveNameMap() {
            const map = {};
            perspectives.forEach(p => {
                map[p.Id] = p.Name;
            });
            return map;
        }

        function updatePerspectiveWeightList() {
            if (!perspectiveWeightList) {
                return;
            }
            const { gradeId, roleId } = getSelectedEmployeeMeta();
            const periodId = getSelectedPeriodId();
            const expectedMap = resolvePerspectiveWeightMap(periodId, gradeId, roleId);
            const names = buildPerspectiveNameMap();
            const parts = Object.keys(expectedMap).map(key => {
                const pid = parseInt(key, 10);
                const name = names[pid] ?? `Perspective ${pid}`;
                return `${name}: ${expectedMap[pid].toFixed(2)}`;
            });
            perspectiveWeightList.textContent = parts.length
                ? `Perspective weights: ${parts.join(' | ')}`
                : 'Perspective weights: -';
        }

        function syncPickerOptions() {
            if (!pickerPerspective) {
                return;
            }
            const { gradeId, roleId } = getSelectedEmployeeMeta();
            const periodId = getSelectedPeriodId();
            const allowed = resolveAllowedPerspectives(periodId, gradeId, roleId);
            const current = pickerPerspective.value ? parseInt(pickerPerspective.value, 10) : null;
            pickerPerspective.querySelectorAll('option').forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }
                const pid = parseInt(option.value, 10);
                const isAllowed = allowed.includes(pid);
                option.hidden = !isAllowed;
                option.disabled = !isAllowed;
            });
            if (current && !allowed.includes(current)) {
                pickerPerspective.value = '';
            }
        }

        function findRowByPerspectiveKpi(perspectiveId, kpiId) {
            return Array.from(table.querySelectorAll('.item-row')).find(row => {
                const rowPerspective = parseInt(row.querySelector('.kpi-perspective')?.value, 10);
                const rowKpi = parseInt(row.querySelector('.kpi-item')?.value, 10);
                return rowPerspective === perspectiveId && rowKpi === kpiId;
            });
        }

        function removeRowsForPerspectiveKpi(perspectiveId, kpiId) {
            table.querySelectorAll('.item-row').forEach(row => {
                const rowPerspective = parseInt(row.querySelector('.kpi-perspective')?.value, 10);
                const rowKpi = parseInt(row.querySelector('.kpi-item')?.value, 10);
                if (rowPerspective === perspectiveId && rowKpi === kpiId) {
                    row.remove();
                }
            });
        }

        function renderPickerKpis() {
            if (!pickerKpis || !pickerPerspective) {
                return;
            }
            pickerKpis.innerHTML = '';
            const pidValue = pickerPerspective.value;
            if (!pidValue) {
                pickerWeight.textContent = 'Target weight: -';
                return;
            }
            const pid = parseInt(pidValue, 10);
            const { gradeId, roleId } = getSelectedEmployeeMeta();
            const periodId = getSelectedPeriodId();
            const expectedMap = resolvePerspectiveWeightMap(periodId, gradeId, roleId);
            const targetWeight = expectedMap[pid] ?? 0;
            pickerWeight.textContent = `Target weight: ${targetWeight.toFixed(2)}`;

            const options = kpiItems.filter(item => item.PerspectiveID == pid);
            if (!options.length) {
                pickerKpis.innerHTML = '<span class="text-muted small">No KPIs for this perspective.</span>';
                return;
            }
            options.forEach(item => {
                const wrapper = document.createElement('div');
                wrapper.className = 'form-check';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'form-check-input';
                checkbox.id = `picker-kpi-${pid}-${item.Id}`;
                checkbox.checked = Boolean(findRowByPerspectiveKpi(pid, item.Id));
                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) {
                        createRowWithPerspectiveKpi(pid, item.Id);
                    } else {
                        removeRowsForPerspectiveKpi(pid, item.Id);
                        updateWeightSummary();
                    }
                });
                const label = document.createElement('label');
                label.className = 'form-check-label';
                label.setAttribute('for', checkbox.id);
                label.textContent = item.Name;
                wrapper.appendChild(checkbox);
                wrapper.appendChild(label);
                pickerKpis.appendChild(wrapper);
            });
        }

        function addRow() {
            const row = document.createElement('tr');
            row.className = 'item-row';
            row.innerHTML = `
                <td>
                    <select name="Items[${index}][PerspectiveID]" class="form-select kpi-perspective">
                        <option value="">Select Perspective</option>
                        @foreach($perspectives as $perspective)
                            <option value="{{ $perspective->Id }}">{{ $perspective->Name }}</option>
                        @endforeach
                    </select>
                    <div class="mt-1">
                        <button type="button" class="btn btn-link btn-sm p-0 distribute-weights">Distribute evenly</button>
                    </div>
                </td>
                <td>
                    <select name="Items[${index}][KpiItemID]" class="form-select kpi-item" required>
                        <option value="">Select Perspective First</option>
                    </select>
                </td>
                <td><input type="number" step="0.01" name="Items[${index}][AnnualTarget]" class="form-control"></td>
                <td><input type="number" step="0.01" name="Items[${index}][PeriodTarget]" class="form-control"></td>
                <td><input type="number" step="0.01" name="Items[${index}][Weight]" class="form-control kpi-weight" required></td>
                <td><input type="text" name="Items[${index}][Notes]" class="form-control"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
            `;
            table.appendChild(row);
            bindRow(row);
            filterPerspectiveOptions();
            populateKpisForRow(row);
            index++;
            return row;
        }

        function createRowWithPerspectiveKpi(perspectiveId, kpiId) {
            if (findRowByPerspectiveKpi(perspectiveId, kpiId)) {
                return;
            }
            const row = addRow();
            const perspectiveSelect = row.querySelector('.kpi-perspective');
            perspectiveSelect.value = String(perspectiveId);
            populateKpisForRow(row);
            const kpiSelect = row.querySelector('.kpi-item');
            kpiSelect.value = String(kpiId);
            updateWeightSummary();
        }

        function distributeEvenlyForRow(row) {
            const perspectiveValue = row.querySelector('.kpi-perspective')?.value;
            if (!perspectiveValue) {
                return;
            }
            const pid = parseInt(perspectiveValue, 10);
            const { gradeId, roleId } = getSelectedEmployeeMeta();
            const periodId = getSelectedPeriodId();
            const expectedMap = resolvePerspectiveWeightMap(periodId, gradeId, roleId);
            if (!(pid in expectedMap)) {
                window.alert('No configured weight found for this perspective.');
                return;
            }
            const rows = Array.from(table.querySelectorAll('.item-row')).filter(r => {
                return parseInt(r.querySelector('.kpi-perspective')?.value, 10) === pid;
            });
            if (!rows.length) {
                return;
            }
            const perItem = expectedMap[pid] / rows.length;
            rows.forEach(r => {
                const input = r.querySelector('.kpi-weight');
                if (input) {
                    input.value = perItem.toFixed(2);
                }
            });
            updateWeightSummary();
        }

        function updateWeightSummary() {
            const summary = document.getElementById('weightSummary');
            if (!summary) {
                return;
            }
            const { gradeId, roleId } = getSelectedEmployeeMeta();
            const periodId = getSelectedPeriodId();
            const expectedMap = resolvePerspectiveWeightMap(periodId, gradeId, roleId);
            const names = buildPerspectiveNameMap();
            const totals = {};
            let overallTotal = 0;
            table.querySelectorAll('.item-row').forEach(row => {
                const perspectiveValue = row.querySelector('.kpi-perspective')?.value;
                const weightValue = parseFloat(row.querySelector('.kpi-weight')?.value);
                if (!perspectiveValue) {
                    return;
                }
                const pid = parseInt(perspectiveValue, 10);
                const weight = Number.isFinite(weightValue) ? weightValue : 0;
                totals[pid] = (totals[pid] || 0) + weight;
                overallTotal += weight;
            });
            const lines = [`Total KPI weight: ${overallTotal.toFixed(2)} / 100`];
            Object.keys(expectedMap).forEach(key => {
                const pid = parseInt(key, 10);
                const expected = expectedMap[pid] ?? 0;
                const actual = totals[pid] ?? 0;
                const name = names[pid] ?? `Perspective ${pid}`;
                const status = Math.abs(actual - expected) <= 0.01 ? 'OK' : 'Mismatch';
                lines.push(`${name}: ${actual.toFixed(2)} / ${expected.toFixed(2)} (${status})`);
            });
            summary.textContent = lines.join(' | ');
        }

        function filterPerspectiveOptions() {
            const { gradeId, roleId } = getSelectedEmployeeMeta();
            const periodId = getSelectedPeriodId();
            const allowed = resolveAllowedPerspectives(periodId, gradeId, roleId);
            table.querySelectorAll('.kpi-perspective').forEach(select => {
                const current = select.value ? parseInt(select.value, 10) : null;
                select.querySelectorAll('option').forEach(option => {
                    if (!option.value) {
                        option.hidden = false;
                        option.disabled = false;
                        return;
                    }
                    const pid = parseInt(option.value, 10);
                    const isAllowed = allowed.includes(pid);
                    option.hidden = !isAllowed;
                    option.disabled = !isAllowed;
                });
                if (current && !allowed.includes(current)) {
                    select.value = '';
                }
            });
            updatePerspectiveWeightList();
            syncPickerOptions();
            updateWeightSummary();
        }

        function populateKpisForRow(row) {
            const perspectiveSelect = row.querySelector('.kpi-perspective');
            const kpiSelect = row.querySelector('.kpi-item');
            const selectedPerspective = perspectiveSelect.value ? parseInt(perspectiveSelect.value, 10) : null;
            kpiSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = selectedPerspective ? 'Select KPI' : 'Select Perspective First';
            kpiSelect.appendChild(placeholder);
            if (!selectedPerspective) {
                return;
            }
            const options = kpiItems.filter(item => item.PerspectiveID == selectedPerspective);
            if (!options.length) {
                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = 'No KPIs for this perspective';
                kpiSelect.appendChild(empty);
                return;
            }
            options.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.Id;
                opt.textContent = item.Name;
                kpiSelect.appendChild(opt);
            });
            updateWeightSummary();
        }

        addBtn.addEventListener('click', function () {
            addRow();
            updateWeightSummary();
            renderPickerKpis();
        });

        table.querySelectorAll('.item-row').forEach(bindRow);
        filterPerspectiveOptions();
        table.querySelectorAll('.item-row').forEach(populateKpisForRow);
        updateSegmentOptions();
        syncPickerOptions();
        renderPickerKpis();
        updatePerspectiveWeightList();
        updateWeightSummary();
        employeeSelect?.addEventListener('change', () => {
            filterPerspectiveOptions();
            table.querySelectorAll('.item-row').forEach(populateKpisForRow);
            syncPickerOptions();
            renderPickerKpis();
            updatePerspectiveWeightList();
            updateWeightSummary();
        });
        periodSelect?.addEventListener('change', () => {
            segmentSelect.dataset.selected = '';
            updateSegmentOptions();
            filterPerspectiveOptions();
            table.querySelectorAll('.item-row').forEach(populateKpisForRow);
            syncPickerOptions();
            renderPickerKpis();
            updatePerspectiveWeightList();
            updateWeightSummary();
        });
        table.addEventListener('input', (event) => {
            if (event.target.classList.contains('kpi-weight')) {
                updateWeightSummary();
            }
        });
        table.addEventListener('change', (event) => {
            if (event.target.classList.contains('kpi-perspective') || event.target.classList.contains('kpi-item')) {
                updateWeightSummary();
                renderPickerKpis();
            }
        });
        pickerPerspective?.addEventListener('change', () => {
            renderPickerKpis();
        });
    })();
</script>
@endsection
