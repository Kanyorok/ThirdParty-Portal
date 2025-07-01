@extends('layouts.app')
@section('title', 'General Ledger Entries')
@section('content')
<div class="container mt-3">
    <div id="edit-alert" class="alert alert-info alert-dismissible fade show" role="alert" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 1050;">
        Editing in progress...
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <button type="button" class="btn btn-primary btn-sm" id="toggle-edit">
            <i class="fas fa-edit me-1"></i> Enable Edit
        </button>
        <div class="d-flex align-items-center">
            <span class="text-muted fs-6 me-2" id="save-status">Saved</span>
            <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="undo-btn" disabled>
                <i class="fas fa-undo me-1"></i> Undo
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="redo-btn" disabled>
                <i class="fas fa-redo me-1"></i> Redo
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('topdownallocation.update',$budgetId) }}" id="budget-form">
        @csrf
        @method('PATCH')
        <input type="hidden" name="branchId" value="{{ $branchId }}">
        <input type="hidden" name="budgetId" value="{{ $budgetId }}">
        <input type="hidden" name="BudgetID" value="{{ $budgetId }}">
        <input type="hidden" name="BranchID" value="{{ $branchId }}">
        <div class="row mb-3 g-2">
            <div class="col-md-4">
                <label class="form-label fw-medium">Select Budget</label>
                <select class="form-select form-select-sm" name="BudgetID" required disabled>
                    <option selected disabled>-- Select Budget --</option>
                    @foreach ($budgets as $item)
                        <option value="{{ $item->Id }}" {{ $budgetId == $item->Id ? 'selected' : '' }}>{{ $item->Name }} - {{ \Carbon\Carbon::parse($item->From)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($item->To)->format('Y-m-d') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Select Branch</label>
                <select class="form-select form-select-sm" name="BranchID" required disabled>
                    <option selected disabled>-- Select Branch --</option>
                    @foreach ($branches as $item)
                        <option value="{{ $item->Id }}" {{ $branchId == $item->Id ? 'selected' : '' }}>{{ $item->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Select Format</label>
                <select class="form-select form-select-sm" id="format" name="format" disabled>
                    <option value="m" selected>Monthly</option>
                </select>
            </div>
        </div>

        <div id="monthly_form" class="table-responsive mb-3">
            <div style="overflow-x: auto; overflow-y: auto; max-height: 500px; position: relative;">
                <table class="table table-bordered table-striped table-sm" id="budget-table">
                    <thead class="table-light text-center">
                        <tr style="position: sticky; top: 0; background: #f8f9fa; z-index: 10; box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">
                            <th style="position: sticky; left: 0; background: #f8f9fa; z-index: 11; min-width: 100px; max-width: 100px;">Account ID</th>
                            <th style="position: sticky; left: 100px; background: #f8f9fa; z-index: 11; min-width: 200px; max-width: 200px; text-wrap: wrap;">Budget Line</th>
                            @for ($m = 1; $m <= 12; $m++)
                                <th style="min-width: 100px;">Month {{ $m }}</th>
                            @endfor
                            <th style="min-width: 120px;">Budget 2025</th>
                            <th style="min-width: 120px;">Actuals Dec 2024</th>
                            <th style="min-width: 100px;">% Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedAccounts = $glsMaster->groupBy('GLAccountTypeID');
                            $accountTypes = ['A' => 'Assets', 'L' => 'Liabilities', 'E' => 'Expenses', 'I' => 'Income'];
                        @endphp
                        @foreach ($accountTypes as $typeId => $typeName)
                            @if (isset($groupedAccounts[$typeId]))
                                <tr class="table-secondary">
                                    <td colspan="17" class="fw-bold">{{ $typeName }}</td>
                                </tr>
                                @foreach ($groupedAccounts[$typeId] as $item)
                                    <tr data-account-id="{{ $item->AccountID }}" data-account-type="{{ $item->GLAccountTypeID }}">
                                        <td style="position: sticky; left: 0; background: #fff; z-index: 9; min-width: 100px; max-width: 100px;">
                                            {{ $item->AccountID }}
                                        </td>
                                        <td style="position: sticky; left: 100px; background: #fff; min-width: 200px; max-width: 200px; text-wrap: wrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->Description }}">
                                            {{ $item->Description }} <b>({{ $item->GLAccountTypeID }})</b>
                                        </td>
                                        @for ($m = 1; $m <= 12; $m++)
                                            @php
                                                $monthKey = 'Month' . $m;
                                                $value = $item->$monthKey ?? 0;
                                            @endphp
                                            <td>
                                                <input type="text"
                                                       name="monthly_allocations[{{ $item->AccountID }}][{{ $m }}]"
                                                       class="form-control form-control-sm text-end monthly-input"
                                                       placeholder="0.00"
                                                       data-val="{{ $value }}"
                                                       value="{{ $value > 0 ? number_format($value, 0) : '' }}"
                                                       inputmode="numeric"
                                                       disabled
                                                       style="width: 100px; padding: 2px 5px;" />
                                            </td>
                                        @endfor
                                        <td>
                                            <input class="form-control form-control-sm total-input text-end"
                                                   name="budget_2025[{{ $item->AccountID }}]"
                                                   style="width: 120px; padding: 2px 5px;"
                                                   value="0"
                                                   readonly />
                                        </td>
                                        <td>
                                            <input class="form-control form-control-sm text-end"
                                                   style="width: 120px; padding: 2px 5px;"
                                                   value="{{ $item->ActualsDec2024 ?? 4000000 }}"
                                                   readonly />
                                        </td>
                                        <td>
                                            <input class="form-control form-control-sm text-end percent-change"
                                                   style="width: 100px; padding: 2px 5px;"
                                                   value="{{ $item->PercentChange ?? '0.00%' }}"
                                                   readonly />
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-3">
                <button type="submit" class="btn btn-success btn-sm" id="submit-btn" style="display: none;">
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
                        <span class="fw-bold">% Variance:</span> <span id="percent-variance" class="fw-bold">0.00%</span>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div id="context-menu" class="dropdown-menu" style="position: absolute; display: none; z-index: 1000;">
        <a class="dropdown-item" href="#" id="clear-row">Clear Row</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('budget-table');
    const toggle = document.getElementById('toggle-edit');
    const undo = document.getElementById('undo-btn');
    const redo = document.getElementById('redo-btn');
    const saveStatus = document.getElementById('save-status');
    const submitBtn = document.getElementById('submit-btn');
    const editAlert = document.getElementById('edit-alert');
    let edit = false;
    let history = [];
    let redoStack = [];
    let hasEdited = false;

    const formatNumber = (num) => {
        return num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    };

    const parseNumber = (str) => {
        return parseFloat(str.replace(/,/g, '')) || 0;
    };

    function capture() {
        const snapshot = Array.from(table.querySelectorAll('.monthly-input'))
            .map(el => el.dataset.val);
        history.push(snapshot);
        if (history.length > 50) history.shift();
        redoStack = [];
        undo.disabled = false;
        redo.disabled = true;
    }

    function restore(snapshot) {
        const inputs = table.querySelectorAll('.monthly-input');
        inputs.forEach((el, i) => {
            el.dataset.val = snapshot[i] || '0';
            el.value = snapshot[i] && snapshot[i] !== '0' ? formatNumber(parseFloat(snapshot[i])) : '';
        });
        recalcAll();
    }

    function recalcRow(row) {
        let sum = 0;
        row.querySelectorAll('.monthly-input').forEach(el => {
            const v = parseNumber(el.dataset.val);
            sum += v;
        });
        row.querySelector('.total-input').value = formatNumber(sum);
    }

    function recalcAll() {
        const categoryTotals = { 'A': 0, 'L': 0, 'E': 0, 'I': 0 };
        table.querySelectorAll('tbody tr[data-account-type]').forEach(row => {
            recalcRow(row);
            const total = parseNumber(row.querySelector('.total-input').value);
            const accountType = row.dataset.accountType;
            categoryTotals[accountType] += total;
        });

        document.getElementById('category-a').textContent = formatNumber(categoryTotals['A']);
        document.getElementById('category-l').textContent = formatNumber(categoryTotals['L']);
        document.getElementById('category-e').textContent = formatNumber(categoryTotals['E']);
        document.getElementById('category-i').textContent = formatNumber(categoryTotals['I']);

        const netPosition = categoryTotals['A'] + categoryTotals['E'] - categoryTotals['L'] - categoryTotals['I'];
        document.getElementById('net-position').textContent = formatNumber(netPosition);

        const actuals = 4000000;
        const variance = actuals - netPosition;
        const percentVariance = actuals !== 0 ? ((variance / actuals) * 100).toFixed(2) : 0;
        document.getElementById('variance').textContent = formatNumber(variance);
        document.getElementById('percent-variance').textContent = `${percentVariance}%`;
    }

    toggle.onclick = () => {
        edit = !edit;
        toggle.innerHTML = edit 
            ? '<i class="fas fa-lock me-1"></i> Editing in Progress' 
            : '<i class="fas fa-edit me-1"></i> Enable Edit';
        toggle.classList.toggle('btn-primary', !edit);
        toggle.classList.toggle('btn-warning', edit);
        table.querySelectorAll('.monthly-input').forEach(el => el.disabled = !edit);
        submitBtn.style.display = edit ? 'block' : 'none';
        if (edit && history.length === 0) capture();
    };

    table.querySelectorAll('.monthly-input').forEach(el => {
        el.addEventListener('input', () => {
            if (!edit) return;
            if (!hasEdited) {
                editAlert.style.display = 'block';
                setTimeout(() => {
                    editAlert.classList.remove('show');
                    setTimeout(() => { editAlert.style.display = 'none'; }, 150);
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
            capture();
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
            if (edit && ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
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

    const contextMenu = document.getElementById('context-menu');
    const clearRowItem = document.getElementById('clear-row');
    table.addEventListener('contextmenu', (e) => {
        if (!edit) return;
        e.preventDefault();
        const row = e.target.closest('tr');
        if (!row) return;
        contextMenu.style.display = 'block';
        contextMenu.style.left = `${e.pageX}px`;
        contextMenu.style.top = `${e.pageY}px`;
        clearRowItem.onclick = () => {
            capture();
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

    submitBtn.addEventListener('click', (e) => {
        e.preventDefault();
        saveStatus.textContent = 'Saving...';
        saveStatus.classList.add('saving');
        document.getElementById('budget-form').submit();
    });

    table.querySelectorAll('.percent-change').forEach(input => {
        const value = parseFloat(input.value.replace('%', '')) || 0;
        if (value > 100) {
            input.style.backgroundColor = '#fff3cd';
        }
    });

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
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.active-cell {
    border: 2px solid #007bff !important;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
}
#save-status.saving {
    color: #007bff;
}
#save-status.saved {
    color: #28a745;
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

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection