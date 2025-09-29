@extends('layouts.app')
@section('title', 'Statement of Financial Position')

@section('content')
    <div class="container-fluid my-3">

        {{-- Header & Toolbar --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
{{--                <h4 class="mb-1 text-primary">📊 Statement of Financial Position</h4>--}}
                @if ($isSet)
                    <div class="text-muted">
                        <span class="me-2">{{ $budgetName }}</span> · <span>{{ $period }}</span>
                    </div>
                @endif
            </div>

            <div class="d-flex gap-2">
                {{-- Optional actions; wire these routes when ready --}}
                {{-- <a href="{{ route('budgetconsolidation.export', ['format' => 'excel']) }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </a>
                <a href="{{ route('budgetconsolidation.export', ['format' => 'pdf']) }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a> --}}
                <button type="button" class="btn btn-outline-success btn-sm" id="exportExcelTop">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="exportPdfTop">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnExpandAll">
                    <i class="fas fa-plus-square me-1"></i> Expand All
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCollapseAll">
                    <i class="fas fa-minus-square me-1"></i> Collapse All
                </button>
            </div>
        </div>

        {{-- Budget Selector --}}
        <form action="{{ route('budgetconsolidation.index') }}" method="GET" class="mb-4">
            <label class="form-label fw-semibold me-2 mb-0">{{ $isSet ? 'Change Budget' : 'Select a Budget' }}</label>
            <select name="BudgetLineID" class="form-select d-inline-block w-auto" required onchange="this.form.submit()">
                <option disabled {{ !$isSet ? 'selected' : '' }}>-- Select Budget --</option>
                @foreach ($budgets as $item)
                    <option value="{{ $item->Id }}" {{ ($budgetId ?? null) == $item->Id ? 'selected' : '' }}>
                        {{ $item->Name }}
                    </option>
                @endforeach
            </select>
            @error('BudgetLineID') <small class="text-danger ms-2">{{ $message }}</small> @enderror
        </form>

        {{-- Legend --}}
        <div class="small text-muted mb-3">
            <span class="legend-dot bg-warning-subtle me-1"></span> Rate
            <span class="ms-3 legend-dot bg-success-subtle me-1"></span> Total
            <span class="ms-3 legend-dot bg-info-subtle me-1"></span> Actuals
            <span class="ms-3 legend-dot bg-danger-subtle me-1"></span> % Change
        </div>

        @php
            $months = range(1, 12);
            $monthLabels = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
            $grandTotal = 0;
            $cardIndex = 0;
        @endphp

        {{-- Categories (Assets / Liabilities / Equity / etc.) --}}
        <div class="accordion" id="consolidationAccordion">
            @foreach($data as $category => $subTypes)
                @php $categoryTotal = 0; $cardIndex++; $cardId = 'cat-'.$cardIndex; @endphp

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center bg-primary-subtle text-primary fw-semibold rounded-top py-2 px-3">
                        <div>{{ strtoupper($category) }}</div>
                        <button class="btn btn-outline-primary btn-sm" type="button"
                                data-bs-toggle="collapse" data-bs-target="#{{ $cardId }}"
                                aria-expanded="true" aria-controls="{{ $cardId }}">
                            <span class="collapse-text">Collapse</span>
                        </button>
                    </div>


                    <div id="{{ $cardId }}" class="collapse show" data-bs-parent="#consolidationAccordion">
                        <div class="card-body p-0">
                            @foreach($subTypes as $subTypeName => $entries)

                                <div class="px-3 pt-3">
                                    <span class="badge rounded-pill bg-info-subtle text-info fw-semibold px-3 py-2">
                                        {{ $subTypeName }}
                                    </span>
                                </div>


                                <div class="consol-scroll">
                                    <table class="table table-sm align-middle mb-0 consol-table"
                                           style="min-width: 1200px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 13px;">
                                        <thead class="table-light">
                                        <tr>
                                            <th class="sticky-col text-start ps-3">Budget Line</th>
                                            <th class="text-center bg-warning-subtle">Rate</th>
                                            @foreach($months as $m)
                                                <th class="text-end">{{ $monthLabels[$m] }}</th>
                                            @endforeach
                                            <th class="text-end bg-success-subtle">Total</th>
                                            <th class="text-end bg-info-subtle">Actuals</th>
                                            <th class="text-center bg-danger-subtle">% Change</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php $subTotal = 0; @endphp
                                        @foreach($entries as $entry)
                                            @php
                                                $isMonthly = ($entry['allocationType'] === 'monthly');
                                                $total = $isMonthly
                                                    ? array_sum(array_map('floatval', $entry['allocationValues']))
                                                    : floatval($entry['fullAllocation']);
                                                $subTotal += $total;
                                                $categoryTotal += $total;

                                                $prev = floatval($entry['prevYear'] ?? 0);
                                                $actual = floatval($entry['actuals'] ?? 0);
                                                $percentChange = $prev > 0 ? (($total - $prev) / $prev) * 100 : 0;
                                                $deltaClass = $percentChange > 0 ? 'delta-pos' : ($percentChange < 0 ? 'delta-neg' : 'delta-neu');
                                            @endphp
                                            <tr>
                                                <td class="sticky-col text-start ps-3">{{ $entry['budgetLineName'] }}</td>
                                                <td class="text-center bg-warning-subtle">{{ number_format($entry['rate'], 2) }}%</td>

                                                @foreach($months as $m)
                                                    <td class="text-end">
                                                        @if($isMonthly)
                                                            {{ (isset($entry['allocationValues'][$m]) && $entry['allocationValues'][$m] != '.00')
                                                                ? number_format($entry['allocationValues'][$m], 2)
                                                                : '—' }}
                                                        @else
                                                            {{ $m == 12 ? number_format($entry['fullAllocation'], 2) : '—' }}
                                                        @endif
                                                    </td>
                                                @endforeach

                                                <td class="text-end bg-success-subtle fw-semibold">{{ number_format($total, 2) }}</td>
                                                <td class="text-end bg-info-subtle">{{ number_format($entry['actual'], 2) }}</td>
                                                <td class="text-center bg-danger-subtle">
                                                    <span class="badge {{ $deltaClass }}">{{ number_format($entry['change'], 2) }}%</span>
                                                </td>
                                            </tr>
                                        @endforeach

                                        {{-- Subtotal Row --}}
                                        <tr class="table-secondary fw-semibold">
                                            <td class="sticky-col text-start ps-3">Subtotal – {{ $subTypeName }}</td>
                                            <td></td>
                                            @foreach($months as $m) <td></td> @endforeach
                                            <td class="text-end pe-2">{{ number_format($subTotal, 2) }}</td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach

                            {{-- Category Total --}}
                            <div class="d-flex justify-content-end py-3 pe-3">
                                <div class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2">
                                    Total for {{ $category }}: {{ number_format($categoryTotal, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @php $grandTotal += $categoryTotal; @endphp
            @endforeach
        </div>

        {{-- Bottom actions: Export --}}
        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-outline-success btn-sm" id="exportExcelBottom">
                <i class="fas fa-file-excel me-1"></i> Excel
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" id="exportPdfBottom">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </button>
        </div>

        {{-- Grand Total --}}
{{--        @if(!empty($data))--}}
{{--            <div class="text-end fs-5 fw-bold text-success me-1 mt-3">--}}
{{--                💰 Total Budget Cost: {{ number_format($grandTotal, 2) }}--}}
{{--            </div>--}}
{{--        @endif--}}

    </div>
@endsection

@section('styles')
    <style>
        /* Basics */
        .legend-dot{display:inline-block;width:10px;height:10px;border-radius:50%;vertical-align:middle}
        .consol-scroll{position:relative;overflow:auto}
        .consol-scroll::after{ /* soft scroll shadow hint */
            content:""; position:sticky; right:0; top:0; width:18px; height:100%;
            background: linear-gradient(to left, rgba(0,0,0,.06), transparent);
            pointer-events:none; float:right;
        }
        .consol-table th, .consol-table td{
            vertical-align: middle;
            padding: .5rem;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .consol-table th{text-align:center}
        .consol-table td.text-start, .consol-table th.text-start{text-align:left}
        .consol-table td.text-end, .consol-table th.text-end{text-align:right}

        /* Sticky header & first column */
        .consol-table thead th{
            position: sticky; top: 0; z-index: 3;
            background: var(--bs-table-bg, #fff);
            box-shadow: inset 0 -1px 0 rgba(0,0,0,.075);
        }
        .consol-table .sticky-col{
            position: sticky; left: 0; z-index: 2; background: #fff;
            box-shadow: 1px 0 0 rgba(0,0,0,.05);
        }
        .table tbody tr:hover{ background-color:#f8f9fa; transition: background-color .15s ease }

        /* Deliberate, modest color system aligned with earlier UIs */
        .bg-primary-subtle{background: rgba(13,110,253,.08) !important;}
        .text-primary{color:#0d6efd !important}

        /* Delta badges */
        .badge.delta-pos{background:#e9f7ef; color:#198754; border:1px solid #cbead6}
        .badge.delta-neg{background:#fdecea; color:#dc3545; border:1px solid #f5c2c7}
        .badge.delta-neu{background:#eff1f4; color:#6c757d; border:1px solid #dee2e6}

        /* Card rounding */
        .card{border-radius:.6rem}
        .card-header{border-top-left-radius:.6rem; border-top-right-radius:.6rem}

        /* Tight 13px table font */
        .consol-table{font-size:13px}
    </style>
@endsection

@section('scripts')
    <script>
        // Expand / Collapse all categories
        document.addEventListener('DOMContentLoaded', () => {
            const expandAll = document.getElementById('btnExpandAll');
            const collapseAll = document.getElementById('btnCollapseAll');
            const sections = document.querySelectorAll('#consolidationAccordion .collapse');

            expandAll?.addEventListener('click', () => {
                sections.forEach(s => new bootstrap.Collapse(s, { show: true }));
                document.querySelectorAll('#consolidationAccordion .card-header .collapse-text')
                    .forEach(el => el.textContent = 'Collapse');
            });
            collapseAll?.addEventListener('click', () => {
                sections.forEach(s => new bootstrap.Collapse(s, { toggle: false }).hide());
                document.querySelectorAll('#consolidationAccordion .card-header .collapse-text')
                    .forEach(el => el.textContent = 'Expand');
            });

            // Update button text per section
            document.querySelectorAll('#consolidationAccordion .card').forEach(card => {
                const btn = card.querySelector('[data-bs-toggle="collapse"]');
                const collapseEl = card.querySelector('.collapse');
                collapseEl?.addEventListener('hide.bs.collapse', () => btn.querySelector('.collapse-text').textContent = 'Expand');
                collapseEl?.addEventListener('show.bs.collapse', () => btn.querySelector('.collapse-text').textContent = 'Collapse');
            });
            // Export helpers: serialize visible table into rows for server-side generation
            function buildExportRows() {
                const rows = [];
                const monthHeaders = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                document.querySelectorAll('#consolidationAccordion .card').forEach(card => {
                    const category = card.querySelector('.card-header div')?.textContent?.trim() || '';
                    card.querySelectorAll('.consol-scroll').forEach(section => {
                        const subType = section.previousElementSibling?.querySelector('.badge')?.textContent?.trim() || '';
                        section.querySelectorAll('tbody tr').forEach(tr => {
                            const tds = tr.querySelectorAll('td');
                            if (!tds.length) return;
                            const isSubtotal = tr.classList.contains('table-secondary');
                            if (isSubtotal) return; // skip subtotal rows; can include if needed
                            const row = {
                                category: category,
                                subType: subType,
                                budgetLineName: tds[0]?.textContent?.trim() || '',
                                rate: tds[1]?.textContent?.trim() || '',
                                months: {},
                                total: tds[tds.length - 3]?.textContent?.trim() || '',
                                actuals: tds[tds.length - 2]?.textContent?.trim() || '',
                                change: tds[tds.length - 1]?.textContent?.trim() || ''
                            };
                            // months occupy from col index 2 up to length-4
                            for (let i = 0; i < 12; i++) {
                                const idx = 2 + i;
                                row.months[i+1] = tds[idx]?.textContent?.trim() || '';
                            }
                            rows.push(row);
                        });
                    });
                });
                return rows;
            }

            function postTo(url, data) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                const token = document.createElement('input');
                token.type = 'hidden'; token.name = '_token'; token.value = '{{ csrf_token() }}';
                form.appendChild(token);
                const bname = document.createElement('input');
                bname.type = 'hidden'; bname.name = 'budgetName'; bname.value = @json($budgetName ?? 'Budget');
                form.appendChild(bname);
                const payload = document.createElement('input');
                payload.type = 'hidden'; payload.name = 'rows'; payload.value = JSON.stringify(data);
                form.appendChild(payload);
                document.body.appendChild(form);
                form.submit();
            }

            function bindExport(btnId, route) {
                const el = document.getElementById(btnId);
                if (!el) return;
                el.addEventListener('click', () => {
                    const rows = buildExportRows();
                    postTo(route, rows);
                });
            }

            bindExport('exportExcelTop', '{{ route('budgetconsolidation.export.excel') }}');
            bindExport('exportPdfTop', '{{ route('budgetconsolidation.export.pdf') }}');
            bindExport('exportExcelBottom', '{{ route('budgetconsolidation.export.excel') }}');
            bindExport('exportPdfBottom', '{{ route('budgetconsolidation.export.pdf') }}');
        });
    </script>
@endsection
