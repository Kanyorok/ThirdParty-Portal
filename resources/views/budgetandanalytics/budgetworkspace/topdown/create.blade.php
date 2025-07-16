@extends('layouts.app')
@section('title', 'General Ledger Entries')
@section('content')
    <div class="container mt-4">
        <!-- Alert Popup -->
        <div id="edit-alert" class="alert alert-info alert-dismissible fade show" role="alert"
             style="display: none; position: fixed; top: 20px; right: 20px; z-index: 1050;">
            Editing in progress...
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Budget Summary -->
        <div class="row mb-3 g-2">
            @php
                $budget = $budgets->where('Id', $budgetId)->first();
                $branch = $branches->where('Id', $branchId)->first();
            @endphp
            <div class="col-md-6">
                <label class="form-label fw-medium">Budget</label>
                <div class="border rounded p-2 bg-light">
                    <span class="fw-bold">{{ $budget ? $budget->Name : 'N/A' }}</span>
                    <span>({{ $budget ? $budget->From . ' to ' . $budget->To : 'N/A' }})</span>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Branch</label>
                <div class="border rounded p-2 bg-light">
                    <span class="fw-bold">{{ $branch ? $branch->Name : 'N/A' }}</span>
                </div>
            </div>
            {{-- <div class="col-md-3">
                <label class="form-label fw-medium">Frequency</label>
                <div class="border rounded p-2 bg-light">
                    <span class="fw-bold">Monthly</span>
                </div>
            </div> --}}
        </div>

        <form method="POST" action="{{ route('topdownallocation.store') }}">
            @csrf
            @method('POST')
            <input type="hidden" name="branchId" value="{{ $branchId }}">
            <input type="hidden" name="budgetId" value="{{ $budgetId }}">
            <input type="hidden" name="format" value="m">

            <!-- MONTHLY FORMAT -->
            <div id="monthly_form" class="table-responsive mb-3">
                <div style="overflow-x: auto; overflow-y: auto; max-height: 600px; position: relative;">
                    <table class="table table-bordered table-striped table-sm" id="budget-table"
                           style="min-width: 1600px;">
                        <thead class="table-light text-center">
                        <tr style="position: sticky; top: 0; background: #f8f9fa; z-index: 10; box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">
                            <th style="position: sticky; left: 0; background: #f8f9fa; z-index: 11; min-width: 100px; max-width: 100px;">
                                Account ID
                            </th>
                            <th style="position: sticky; left: 100px; background: #f8f9fa; z-index: 11; min-width: 200px; max-width: 200px; text-wrap: wrap;">
                                Budget Line
                            </th>
                            @for ($m = 1; $m <= 12; $m++)
                                <th style="min-width: 120px;">Month {{ $m }}</th>
                            @endfor
                            <th style="min-width: 120px;">Budget 2025</th>
                            <th style="min-width: 120px;">Actuals Dec 2024</th>
                            <th style="min-width: 110px;">% Change</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            // Group accounts by GLAccountTypeID
                            $groupedAccounts = $glsMaster->groupBy('GLAccountTypeID');
                            $accountTypes = ['A' => 'Assets', 'L' => 'Liabilities', 'E' => 'Expenses', 'I' => 'Income'];
                        @endphp
                        @foreach ($accountTypes as $typeId => $typeName)
                            @if (isset($groupedAccounts[$typeId]))
                                <tr class="table-secondary">
                                    <td colspan="17" class="fw-bold">{{ $typeName }}</td>
                                </tr>
                                @foreach ($groupedAccounts[$typeId] as $item)
                                    <tr data-account-id="{{ $item->AccountID }}"
                                        data-account-type="{{ $item->GLAccountTypeID ?? 'NA' }}">
                                        <td style="position: sticky; left: 0; background: #fff; z-index: 9; min-width: 100px; max-width: 100px;">
                                            {{ $item->AccountID }}
                                        </td>
                                        <td style="position: sticky; left: 100px; background: #fff; z-index: 9; min-width: 200px; max-width: 200px; text-wrap: wrap; overflow: hidden; text-overflow: ellipsis;"
                                            title="{{ $item->Description }}">
                                            {{ $item->Description }} <b>({{ $item->GLAccountTypeID ?? 'NA' }})</b>
                                        </td>
                                        <input type="hidden" name="gl_data[{{ $item->AccountID }}][Description]"
                                               value="{{ $item->Description }}">
                                        <input type="hidden" name="gl_data[{{ $item->AccountID }}][AttachID]"
                                               value="{{ $item->Id }}">
                                        <input type="hidden" name="gl_data[{{ $item->AccountID }}][GLAccountTypeID]"
                                               value="{{ $item->GLAccountTypeID ?? 'NA' }}">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <td>
                                                <input type="text"
                                                       name="monthly_allocations[{{ $item->AccountID }}][{{ $m }}]"
                                                       class="form-control form-control-sm text-end monthly-input"
                                                       placeholder="0.00"
                                                       data-val="0"
                                                       value=""
                                                       inputmode="numeric"
                                                       style="width: 120px; padding: 2px 5px;"/>
                                            </td>
                                        @endfor
                                        <td>
                                            <input class="form-control form-control-sm total-input text-end"
                                                   name="budget_2025[{{ $item->AccountID }}]"
                                                   style="width: 120px; padding: 2px 5px;"
                                                   value="0"
                                                   readonly/>
                                        </td>
                                        <td>
                                            <input class="form-control form-control-sm text-end"
                                                   style="width: 120px; padding: 2px 5px;"
                                                   value="{{ $item->ActualsDec2024 ?? 4000000 }}"
                                                   readonly/>
                                        </td>
                                        <td>
                                            <input class="form-control form-control-sm text-end percent-change"
                                                   style="width: 110px; padding: 2px 5px;"
                                                   value="{{ $item->PercentChange ?? '108.75%' }}"
                                                   readonly/>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Totals and Metrics Row -->
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-3">
                    <button type="submit" class="btn btn-success btn-sm" id="submit-btn"
                            onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
                        <i class="fas fa-save me-1"></i> Save Budget
                    </button>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <div class="border rounded p-2 bg-light">
                            <span class="fw-bold text-primary">A:</span> <span id="category-a" class="fw-bold">0</span>
                        </div>
                        <div class="border rounded p-2 bg-light">
                            <span class="fw-bold text-primary">L:</span> <span id="category-l" class="fw-bold">0</span>
                        </div>
                        <div class="border rounded p-2 bg-light">
                            <span class="fw-bold text-primary">E:</span> <span id="category-e" class="fw-bold">0</span>
                        </div>
                        <div class="border rounded p-2 bg-light">
                            <span class="fw-bold text-primary">I:</span> <span id="category-i" class="fw-bold">0</span>
                        </div>
                        <div class="border rounded p-2 bg-success text-white">
                            <span class="fw-bold">Net Position:</span> <span id="net-position" class="fw-bold">0</span>
                        </div>
                        <div class="border rounded p-2 bg-info text-white">
                            <span class="fw-bold">Variance:</span> <span id="variance" class="fw-bold">0</span>
                        </div>
                        <div class="border rounded p-2 bg-warning">
                            <span class="fw-bold">% Variance:</span> <span id="percent-variance"
                                                                           class="fw-bold">0.00%</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Context Menu -->
    <div id="context-menu" class="dropdown-menu" style="position: absolute; display: none; z-index: 1000;">
        <a class="dropdown-item" href="#" id="clear-row">Clear Row</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const table = document.getElementById('budget-table');
            const submitBtn = document.getElementById('submit-btn');
            const editAlert = document.getElementById('edit-alert');
            let hasEdited = false;

            // Number formatting
            const formatNumber = (num) => {
                return num.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
            };

            // Parse formatted number
            const parseNumber = (str) => {
                return parseFloat(str.replace(/,/g, '')) || 0;
            };

            // Calculate row total
            function recalcRow(row) {
                let sum = 0;
                row.querySelectorAll('.monthly-input').forEach(el => {
                    const v = parseNumber(el.dataset.val);
                    sum += v;
                });
                row.querySelector('.total-input').value = formatNumber(sum);
            }

            // Calculate category totals, net position, variance, and percentage variance
            function recalcAll() {
                const categoryTotals = {'A': 0, 'L': 0, 'E': 0, 'I': 0};
                table.querySelectorAll('tbody tr[data-account-type]').forEach(row => {
                    recalcRow(row);
                    const total = parseNumber(row.querySelector('.total-input').value);
                    const accountType = row.dataset.accountType;
                    if (categoryTotals[accountType] !== undefined) {
                        categoryTotals[accountType] += total;
                    }
                });

                // Update category totals
                document.getElementById('category-a').textContent = formatNumber(categoryTotals['A']);
                document.getElementById('category-l').textContent = formatNumber(categoryTotals['L']);
                document.getElementById('category-e').textContent = formatNumber(categoryTotals['E']);
                document.getElementById('category-i').textContent = formatNumber(categoryTotals['I']);

                // Calculate Net Position (A + E - L - I)
                const netPosition = categoryTotals['A'] + categoryTotals['E'] - categoryTotals['L'] - categoryTotals['I'];
                document.getElementById('net-position').textContent = formatNumber(netPosition);

                // Calculate Variance and Percentage Variance
                const actuals = 4000000; // Hardcoded from Actuals Dec 2024
                const variance = actuals - netPosition; // Actual - Budget Total (Net Position)
                const percentVariance = actuals !== 0 ? ((variance / actuals) * 100).toFixed(2) : 0;
                document.getElementById('variance').textContent = formatNumber(variance);
                document.getElementById('percent-variance').textContent = `${percentVariance}%`;
            }

            // Input handling
            table.querySelectorAll('.monthly-input').forEach(el => {
                el.addEventListener('input', () => {
                    if (!hasEdited) {
                        editAlert.style.display = 'block';
                        setTimeout(() => {
                            editAlert.classList.remove('show');
                            setTimeout(() => {
                                editAlert.style.display = 'none';
                            }, 150);
                        }, 8000);
                        hasEdited = true;
                    }
                    let cleanValue = el.value.replace(/[^0-9]/g, '');
                    if (cleanValue) {
                        const numValue = parseFloat(cleanValue);
                        if (numValue > 234432554) {
                            cleanValue = '234432554';
                        }
                        el.dataset.val = cleanValue;
                        el.value = formatNumber(parseFloat(cleanValue));
                    } else {
                        el.dataset.val = '0';
                        el.value = '';
                    }
                    recalcAll();
                });
                el.addEventListener('focus', () => {
                    el.classList.add('active-cell');
                    el.value = el.dataset.val !== '0' ? el.dataset.val : '';
                });
                el.addEventListener('blur', () => {
                    el.classList.remove('active-cell');
                    const rawValue = el.dataset.val || '0';
                    el.value = rawValue !== '0' ? formatNumber(parseFloat(rawValue)) : '';
                });
                el.addEventListener('keydown', (e) => {
                    if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                        e.preventDefault();
                        const inputs = Array.from(table.querySelectorAll('.monthly-input'));
                        const currentIndex = inputs.indexOf(el);
                        let nextIndex;
                        if (e.key === 'ArrowRight') nextIndex = currentIndex + 1;
                        if (e.key === 'ArrowLeft') nextIndex = currentIndex - 1;
                        if (e.key === 'ArrowDown') nextIndex = currentIndex + 12;
                        if (e.key === 'ArrowUp') nextIndex = currentIndex - 12;
                        if (nextIndex >= 0 && nextIndex < inputs.length) {
                            inputs[nextIndex].focus();
                        }
                    }
                });
            });

            // Context menu
            const contextMenu = document.getElementById('context-menu');
            const clearRowItem = document.getElementById('clear-row');
            table.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                const row = e.target.closest('tr');
                if (!row) return;
                contextMenu.style.display = 'block';
                contextMenu.style.left = `${e.pageX}px`;
                contextMenu.style.top = `${e.pageY}px`;
                clearRowItem.onclick = () => {
                    row.querySelectorAll('.monthly-input').forEach(input => {
                        input.dataset.val = '0';
                        input.value = '';
                    });
                    recalcAll();
                    contextMenu.style.display = 'none';
                };
            });
            document.addEventListener('click', () => {
                contextMenu.style.display = 'none';
            });

            // Form submission
            submitBtn.addEventListener('click', (e) => {
                if (submitBtn.form.checkValidity()) {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'Saving...';
                    submitBtn.form.submit();
                }
            });

            // Highlight significant % changes
            table.querySelectorAll('.percent-change').forEach(input => {
                const value = parseFloat(input.value.replace('%', '')) || 0;
                if (value > 100) {
                    input.style.backgroundColor = '#fff3cd';
                }
            });

            // Initial calculations
            recalcAll();
        });
    </script>

    <style>
        #monthly_form table {
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        #monthly_form th, #monthly_form td {
            border: 1px solid #dee2e6;
            padding: 4px 6px;
            vertical-align: middle;
        }

        #monthly_form th {
            font-weight: 600;
        }

        #monthly_form tr:hover:not(.table-secondary) {
            background-color: #f1f3f5;
        }

        #monthly_form .form-control {
            box-sizing: border-box;
            height: 28px;
            font-size: 0.85rem;
        }

        #monthly_form .table-responsive {
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .active-cell {
            border: 2px solid #007bff !important;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        #edit-alert {
            max-width: 300px;
        }

        .border {
            border-color: #dee2e6 !important;
        }

        .bg-light, .bg-success, .bg-info, .bg-warning {
            padding: 0.5rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.9rem;
        }
    </style>


@endsection
