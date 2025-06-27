@extends('layouts.app')
@section('title', 'Balance Sheet Entries')
@section('content')
<div class="container mt-3">
    <!-- Alert Popup -->
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

    <form method="POST" action="#" id="budget-form">
        @csrf
        @method('PATCH')
        <input type="hidden" name="branchId" value="{{ $branchId }}">
        <input type="hidden" name="budgetId" value="{{ $budgetId }}">
        <!-- Budget selection -->
        <div class="row mb-3 g-2">
            <div class="col-md-4">
                <label class="form-label fw-medium">Select Budget</label>
                <select class="form-select form-select-sm" name="BudgetID" required disabled>
                    <option selected disabled>-- Select Budget --</option>
                    @foreach ($budgets as $item)
                        <option value="{{ $item->Id }}" {{ $budgetId == $item->Id ? 'selected' : '' }}>{{ $item->Name }} - {{ $item->From.' '.$item->To }}</option>
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

        <!-- MONTHLY FORMAT -->
        <div id="monthly_form" class="table-responsive mb-3">
            <div style="overflow-x: auto; overflow-y: auto; max-height: 500px; position: relative;">
                <table class="table table-bordered table-striped table-sm" id="budget-table">
                    <thead class="table-light text-center">
                        <tr style="position: sticky; top: 0; background: #f8f9fa; z-index: 10; box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">
                            <th style="position: sticky; left: 0; background: #f8f9fa; z-index: 11; min-width: 100px; max-width: 100px;">Account ID</th>
                            <th style="min-width: 200px; max-width: 200px; text-wrap: wrap;">Budget Line</th>
                            @for ($m = 1; $m <= 12; $m++)
                                <th style="min-width: 100px;">Month {{ $m }}</th>
                            @endfor
                            <th style="min-width: 120px;">Budget 2025</th>
                            <th style="min-width: 120px;">Actuals Dec 2024</th>
                            <th style="min-width: 100px;">% Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($glsMaster as $item)
                            <tr data-account-id="{{ $item->AccountID }}">
                                <td style="position: sticky; left: 0; background: #fff; z-index: 9; min-width: 100px; max-width: 100px;">{{ $item->AccountID }}</td>
                                <td style="min-width: 200px; max-width: 200px; text-wrap: wrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->Description }}">{{ $item->Description }}</td>
                                @for ($m = 1; $m <= 12; $m++)
                                    @php
                                        $value = 0.00;
                                        if($isExisting) {
                                            $monthKey = 'Month' . $m;
                                            $value = $item->$monthKey ?? 0;
                                          
                                        }
                                    @endphp
                                    <td>
                                        <input type="text" 
                                               name="monthly_allocations[{{ $item->AccountID }}][{{ $m }}]"
                                               class="form-control form-control-sm text-end monthly-input"
                                               placeholder="0.00"
                                               data-val="{{ $value }}"
                                               value="{{ $value>0 ? number_format($value, 0) : '' }}"
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
                                           value="{{ $item->PercentChange ?? '108.75%' }}" 
                                           readonly />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Totals and Submit -->
            <div class="d-flex justify-content-between align-items-center mt-2">
                <button type="submit" class="btn btn-success btn-sm" id="submit-btn" style="display: none;">
                    <i class="fas fa-save me-1"></i> Save Budget
                </button>
                <div class="text-end fs-5 fw-bold text-success me-3">
                    💰 Total Budget Cost: <span id="budget-2025-total">0</span>
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
    const toggle = document.getElementById('toggle-edit');
    const undoBtn = document.getElementById('undo-btn');
    const redoBtn = document.getElementById('redo-btn');
    const saveStatus = document.getElementById('save-status');
    const totalLabel = document.getElementById('budget-2025-total');
    const submitBtn = document.getElementById('submit-btn');
    const editAlert = document.getElementById('edit-alert');
    
    let editMode = false;
    let history = [];
    let redoStack = [];
    let hasEdited = false;
    let historyIndex = -1;

    // Number formatting
    const formatNumber = (num) => {
        return num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    };

    // Parse formatted number
    const parseNumber = (str) => {
        return parseFloat(str.replace(/,/g, '')) || 0;
    };

    // Capture current state for undo/redo
    function captureState() {
        const state = {};
        document.querySelectorAll('#budget-table tbody tr').forEach(row => {
            const accountId = row.dataset.accountId;
            state[accountId] = Array.from(row.querySelectorAll('.monthly-input'))
                                     .map(input => input.dataset.val || '0');
        });
        
        // Remove any future states if we're not at the end
        if (historyIndex < history.length - 1) {
            history = history.slice(0, historyIndex + 1);
        }
        
        history.push(state);
        historyIndex = history.length - 1;
        
        // Keep history size manageable
        if (history.length > 50) {
            history.shift();
            historyIndex--;
        }
        
        redoStack = [];
        updateUndoRedoButtons();
    }

    // Restore a saved state
    function restoreState(state) {
        document.querySelectorAll('#budget-table tbody tr').forEach(row => {
            const accountId = row.dataset.accountId;
            const values = state[accountId];
            if (values) {
                row.querySelectorAll('.monthly-input').forEach((input, i) => {
                    const value = values[i] || '0';
                    input.dataset.val = value;
                    input.value = value !== '0' ? formatNumber(parseFloat(value)) : '';
                });
            }
        });
        recalcAll();
    }

    // Update undo/redo button states
    function updateUndoRedoButtons() {
        undoBtn.disabled = historyIndex <= 0;
        redoBtn.disabled = historyIndex >= history.length - 1;
    }

    // Calculate row total
    function recalcRow(row) {
        let sum = 0;
        row.querySelectorAll('.monthly-input').forEach(el => {
            const v = parseNumber(el.dataset.val);
            sum += v;
        });
        row.querySelector('.total-input').value = formatNumber(sum);
    }

    // Calculate all totals
    function recalcAll() {
        let grand = 0;
        table.querySelectorAll('tbody tr').forEach(row => {
            recalcRow(row);
            grand += parseNumber(row.querySelector('.total-input').value);
        });
        totalLabel.textContent = formatNumber(grand);
    }

    // Toggle edit mode
    toggle.onclick = () => {
        editMode = !editMode;
        toggle.innerHTML = editMode 
            ? '<i class="fas fa-lock me-1"></i> Lock Edit' 
            : '<i class="fas fa-edit me-1"></i> Enable Edit';
        toggle.classList.toggle('btn-primary', !editMode);
        toggle.classList.toggle('btn-warning', editMode);
        table.querySelectorAll('.monthly-input').forEach(el => el.disabled = !editMode);
        submitBtn.style.display = editMode ? 'block' : 'none';
        
        // Capture initial state when entering edit mode
        if (editMode && history.length === 0) {
            captureState();
        }
    };

    // Undo functionality
    undoBtn.addEventListener('click', () => {
        if (historyIndex > 0) {
            historyIndex--;
            restoreState(history[historyIndex]);
            updateUndoRedoButtons();
        }
    });

    // Redo functionality
    redoBtn.addEventListener('click', () => {
        if (historyIndex < history.length - 1) {
            historyIndex++;
            restoreState(history[historyIndex]);
            updateUndoRedoButtons();
        }
    });

    // Input handling
    table.querySelectorAll('.monthly-input').forEach(el => {
        let inputTimeout;
        
        el.addEventListener('input', () => {
            if (!editMode) return;
            
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
            
            // Debounce state capture to avoid too many history entries
            clearTimeout(inputTimeout);
            inputTimeout = setTimeout(() => {
                captureState();
            }, 500);
            
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
            if (editMode && ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
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
        if (!editMode) return;
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
            captureState();
            contextMenu.style.display = 'none';
        };
    });
    
    document.addEventListener('click', () => {
        contextMenu.style.display = 'none';
    });

    // Form submission (frontend only)
    submitBtn.addEventListener('click', (e) => {
        e.preventDefault();
        saveStatus.textContent = 'Saving...';
        saveStatus.classList.add('saving');
        setTimeout(() => {
            saveStatus.textContent = 'Saved';
            saveStatus.classList.remove('saving');
            saveStatus.classList.add('saved');
            hasEdited = false;
            // Reset history after save
            history = [];
            historyIndex = -1;
            captureState(); // Capture the saved state as new baseline
            editAlert.style.display = 'none';
        }, 500);
    });

    // Highlight significant % changes
    table.querySelectorAll('.percent-change').forEach(input => {
        const value = parseFloat(input.value.replace('%', '')) || 0;
        if (value > 100) {
            input.style.backgroundColor = '#fff3cd';
        }
    });

    // Initial calculations and state capture
    recalcAll();
    captureState();
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
#monthly_form tr:hover {
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
</style>

<!-- Include Font Awesome and Bootstrap CSS/JS for alert -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection