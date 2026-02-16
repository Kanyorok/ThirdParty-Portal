@extends('layouts.app')
@section('title', 'Stock Movement Dashboard')
@section('content')
<div class="container-fluid mt-4">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold">Transactions Movement</h3>
                <p class="text-muted">
                    Track quantities and values by filters & date range
                    @if(!$isHeadOffice)
                    <span class="badge bg-info ms-2">Branch: {{ $currentBranch->Name }}</span>
                    @else
                    <span class="badge bg-success ms-2">Head Office View</span>
                    @endif
                </p>
            </div>
            <div class="text-end">
                <button class="btn btn-outline-secondary" onclick="printDashboard()">
                    🖨️ Print Report
                </button>
                <button class="btn btn-outline-info ms-2" onclick="exportToExcel()">
                    📊 Export to Excel
                </button>
            </div>
        </div>
    </div>

    <form method="GET" id="filterForm" class="row g-3 mb-4 p-3 bg-light rounded-3 shadow-sm">
        @if($isHeadOffice)
        <div class="col-md-2">
            <label class="form-label small fw-bold">Branch</label>
            <select name="branch" class="form-select" id="branchSelect">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                <option value="{{ $branch->Id }}" {{ request('branch') == $branch->Id ? 'selected' : '' }}>
                    {{ $branch->Name }}
                </option>
                @endforeach
            </select>
        </div>
        @endif
        
        <div class="col-md-2">
            <label class="form-label small fw-bold">Store</label>
            <select name="store" class="form-select" id="storeSelect">
                <option value="">All Stores</option>
                @foreach($stores as $store)
                <option value="{{ $store->Id }}" {{ request('store') == $store->Id ? 'selected' : '' }}>
                    {{ $store->StoreName }}
                </option>
                @endforeach
            </select>
        </div>
        
        <div class="col-md-2">
            <label class="form-label small fw-bold">Item</label>
            <select name="item" class="form-select" id="itemSelect">
                <option value="">All Items</option>
                @foreach($items as $item)
                <option value="{{ $item->Id }}" {{ request('item') == $item->Id ? 'selected' : '' }}>
                    {{ $item->ItemName ?? $item->ItemDescription }}
                </option>
                @endforeach
            </select>
        </div>
        
        <div class="col-md-2">
            <label class="form-label small fw-bold">From Date</label>
            <input type="date" name="from_date" class="form-control" id="fromDate"
                   value="{{ request('from_date', now()->format('Y-m-01')) }}">
        </div>
        
        <div class="col-md-2">
            <label class="form-label small fw-bold">To Date</label>
            <input type="date" name="to_date" class="form-control" id="toDate"
                   value="{{ request('to_date', now()->format('Y-m-d')) }}">
        </div>
        
        <div class="col-md-2 d-flex align-items-end gap-2">
            <div class="d-grid w-100">
                <button type="submit" class="btn btn-primary">🔄 Apply Filters</button>
            </div>
            <div class="d-grid w-100">
                <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">🔄 Reset</button>
            </div>
        </div>
    </form>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-1">Total Items Tracked</h6>
                            <h3 class="card-title mb-0">{{ count($movementData) }}</h3>
                            <small class="opacity-75">Across all categories</small>
                        </div>
                        <i class="bi bi-box-seam fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-1">Stock In</h6>
                            <h3 class="card-title mb-0" id="totalIn">
                                {{ number_format($totalIn ?? 0) }}
                            </h3>
                            <small class="opacity-75">Units received</small>
                        </div>
                        <i class="bi bi-arrow-down-circle fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-1">Stock Out</h6>
                            <h3 class="card-title mb-0" id="totalOut">
                                {{ number_format($totalOut ?? 0) }}
                            </h3>
                            <small class="opacity-75">Units issued</small>
                        </div>
                        <i class="bi bi-arrow-up-circle fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-dark shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-1">Net Movement</h6>
                            <h3 class="card-title mb-0" id="netMovement">
                                {{ number_format($netMovement ?? 0) }}
                            </h3>
                            <small class="opacity-75">
                                @if(($netMovement ?? 0) > 0)
                                <span class="text-success">▲ Net gain</span>
                                @elseif(($netMovement ?? 0) < 0)
                                <span class="text-danger">▼ Net loss</span>
                                @else
                                <span class="text-muted">◼ No change</span>
                                @endif
                            </small>
                        </div>
                        <i class="bi bi-arrow-left-right fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow rounded-4 mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">📊 Daily Stock Movement Trend</h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary active" onclick="toggleChartType('line')">Line</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleChartType('bar')">Bar</button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="dailyChart"></canvas>
                    </div>
                    @if($dailyMovement->isEmpty())
                    <div class="alert alert-info mb-0 mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        No daily movement data available for the selected period.
                    </div>
                    @endif
                </div>
            </div>

            @if($isHeadOffice && $branchMovement->isNotEmpty())
            <div class="card shadow rounded-4 mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">🏢 Branch Distribution</h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary active" onclick="toggleBranchChart('pie')">Pie</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleBranchChart('doughnut')">Doughnut</button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="branchChart"></canvas>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow rounded-4 mb-4">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0">📋 Movement Summary</h6>
                </div>
                <div class="card-body p-3">
                    <div id="movementContainer">
                    </div>
                    @if(empty($movementData))
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No stock movement data found for the selected filters.
                    </div>
                    @endif
                </div>
            </div>

            <div class="card shadow rounded-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">🚀 Top Moving Items</h6>
                    <span class="badge bg-primary">Top 10</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="small">Item</th>
                                    <th class="text-end small">In</th>
                                    <th class="text-end small">Out</th>
                                    <th class="text-end small">Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topItems as $index => $item)
                                @php
                                    $net = $item['total_in'] - $item['total_out'];
                                @endphp
                                <tr>
                                    <td class="small">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                                            <span title="{{ $item['item_name'] }}">
                                                {{ Str::limit($item['item_name'], 15) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-end text-success small">{{ number_format($item['total_in']) }}</td>
                                    <td class="text-end text-danger small">{{ number_format($item['total_out']) }}</td>
                                    <td class="text-end fw-bold small {{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($net) }}
                                    </td>
                                </tr>
                                @endforeach
                                @if($topItems->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        <i class="bi bi-clipboard-x fs-4"></i>
                                        <p class="mt-2 mb-0">No movement data</p>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const movementData = @json($movementData);
    const dailyData = @json($dailyMovement->toArray());
    const branchData = @json($branchMovement->toArray());
    
    let dailyChart, branchChart;
    let currentChartType = 'line';
    let branchChartType = 'pie';
    function createMovementSection(title, opening, valueIn, valueOut, closing, isValue = false) {
        const format = isValue ? 
            num => `KES ${num.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : 
            num => num.toLocaleString();
        
        const arrow = isValue ? '💰' : '📦';
        const net = closing - opening;
        const netPercentage = opening !== 0 ? ((net / opening) * 100).toFixed(1) : 0;
        
        return `
            <div class="movement-summary">
                <div class="d-flex align-items-center mb-2">
                    <span class="me-2">${arrow}</span>
                    <span class="fw-bold small">${title}</span>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-primary bg-opacity-10 rounded">
                            <span class="small">Opening:</span>
                            <span class="fw-bold">${format(opening)}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-success bg-opacity-10 rounded">
                            <span class="small">In:</span>
                            <span class="fw-bold text-success">+${format(valueIn)}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-danger bg-opacity-10 rounded">
                            <span class="small">Out:</span>
                            <span class="fw-bold text-danger">-${format(valueOut)}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-warning bg-opacity-25 rounded">
                            <span class="small">Closing:</span>
                            <span class="fw-bold text-warning">${format(closing)}</span>
                        </div>
                    </div>
                </div>
                <div class="net-change p-2 rounded ${net >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'}">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Net Change:</span>
                        <span class="fw-bold ${net >= 0 ? 'text-success' : 'text-danger'}">
                            ${net >= 0 ? '+' : ''}${format(net)}
                            <small class="ms-1">(${netPercentage}%)</small>
                        </span>
                    </div>
                </div>
            </div>
        `;
    }

    function initializeCharts() {
        if (dailyChart) dailyChart.destroy();
        if (branchChart) branchChart.destroy();

        if (dailyData && dailyData.length > 0) {
            const dailyCtx = document.getElementById('dailyChart').getContext('2d');
            const dates = dailyData.map(d => {
                const date = new Date(d.date);
                return date.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric' 
                });
            });
            
            const inQty = dailyData.map(d => d.in_qty || 0);
            const outQty = dailyData.map(d => d.out_qty || 0);
            
            const inValue = dailyData.map(d => d.in_value || 0);
            const outValue = dailyData.map(d => d.out_value || 0);
            
            dailyChart = new Chart(dailyCtx, {
                type: currentChartType,
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Stock In (Qty)',
                            data: inQty,
                            borderColor: '#198754',
                            backgroundColor: currentChartType === 'bar' ? '#198754' : 'transparent',
                            tension: 0.3,
                            borderWidth: 2,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Stock Out (Qty)',
                            data: outQty,
                            borderColor: '#dc3545',
                            backgroundColor: currentChartType === 'bar' ? '#dc3545' : 'transparent',
                            tension: 0.3,
                            borderWidth: 2,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Quantity'
                            },
                            grid: {
                                drawBorder: false
                            },
                            beginAtZero: true
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)'
                        },
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        }

        @if($isHeadOffice && $branchMovement->isNotEmpty())
        if (branchData && branchData.length > 0) {
            const branchCtx = document.getElementById('branchChart').getContext('2d');
            const branchLabels = branchData.map(b => b.branch_name);
            const branchNetValues = branchData.map(b => Math.abs(b.net_movement || 0));
            const branchColors = [
                '#0d6efd', '#198754', '#dc3545', '#ffc107',
                '#6f42c1', '#20c997', '#fd7e14', '#e83e8c',
                '#6610f2', '#6c757d'
            ];
            
            branchChart = new Chart(branchCtx, {
                type: branchChartType,
                data: {
                    labels: branchLabels,
                    datasets: [{
                        data: branchNetValues,
                        backgroundColor: branchColors.slice(0, branchData.length),
                        borderWidth: 1,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 15,
                                boxWidth: 12,
                                font: { size: 10 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const branch = branchData[context.dataIndex];
                                    return [
                                        `${context.label}: ${context.parsed.toLocaleString()} units`,
                                        `In: ${branch.total_in.toLocaleString()}`,
                                        `Out: ${branch.total_out.toLocaleString()}`
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }
        @endif
    }

    function calculateAllItemsSummary() {
        if (!movementData || Object.keys(movementData).length === 0) {
            return {
                label: 'All Items Summary',
                quantity: [0, 0, 0, 0],
                value: [0, 0, 0, 0]
            };
        }

        let totalOpening = {{ $totalOpening ?? 0 }};
        let totalIn = {{ $totalIn ?? 0 }};
        let totalOut = {{ $totalOut ?? 0 }};
        let totalClosing = {{ $totalClosing ?? 0 }};
        let totalValueOpening = {{ $totalValueOpening ?? 0 }};
        let totalValueIn = {{ $totalValueIn ?? 0 }};
        let totalValueOut = {{ $totalValueOut ?? 0 }};
        let totalValueClosing = {{ $totalValueClosing ?? 0 }};

        return {
            label: 'All Items Summary',
            quantity: [totalOpening, totalIn, totalOut, totalClosing],
            value: [totalValueOpening, totalValueIn, totalValueOut, totalValueClosing]
        };
    }

    function updateUI() {
        const selectedItem = document.getElementById('itemSelect').value;
        const data = selectedItem && movementData[selectedItem]
            ? movementData[selectedItem]
            : calculateAllItemsSummary();

        const movementContainer = document.getElementById('movementContainer');
        if (!data || !data.label) {
            movementContainer.innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Select an item or view all items summary.
                </div>
            `;
            return;
        }

        movementContainer.innerHTML = `
            <div class="mb-3">
                <h6 class="text-primary mb-2">${data.label}</h6>
                <div class="d-flex gap-2">
                    <span class="badge bg-info">Items: ${Object.keys(movementData).length}</span>
                </div>
            </div>
            ${createMovementSection("Quantity Movement", ...data.quantity, false)}
            <hr class="my-3">
            ${createMovementSection("Value Movement", ...data.value, true)}
        `;
    }

    function toggleChartType(type) {
        currentChartType = type;
        initializeCharts();
    }

    function toggleBranchChart(type) {
        branchChartType = type;
        initializeCharts();
    }

    function printDashboard() {
        window.print();
    }

    function exportToExcel() {
        let csvContent = "data:text/csv;charset=utf-8,";
        
        csvContent += "Date,Stock In Qty,Stock Out Qty,Stock In Value,Stock Out Value\n";
        
        dailyData.forEach(row => {
            csvContent += `${row.date},${row.in_qty},${row.out_qty},${row.in_value},${row.out_value}\n`;
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `stock_movement_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function resetFilters() {
        document.getElementById('branchSelect').value = '';
        document.getElementById('storeSelect').value = '';
        document.getElementById('itemSelect').value = '';
        document.getElementById('fromDate').value = '{{ now()->format("Y-m-01") }}';
        document.getElementById('toDate').value = '{{ now()->format("Y-m-d") }}';
        document.getElementById('filterForm').submit();
    }
    @if($isHeadOffice)
    function loadStores(branchId) {
        const storeSelect = document.getElementById('storeSelect');
        storeSelect.innerHTML = '<option value="">Loading stores...</option>';
        storeSelect.disabled = true;

        fetch(`/inventory/stores-by-branch/${branchId || 0}`)
            .then(response => response.json())
            .then(stores => {
                storeSelect.innerHTML = '<option value="">All Stores</option>';
                stores.forEach(store => {
                    const option = document.createElement('option');
                    option.value = store.Id;
                    option.textContent = store.StoreName;
                    storeSelect.appendChild(option);
                });
                storeSelect.disabled = false;
            })
            .catch(() => {
                storeSelect.innerHTML = '<option value="">Error loading stores</option>';
                storeSelect.disabled = false;
            });
    }
    function loadItems(storeId) {
        const itemSelect = document.getElementById('itemSelect');
        itemSelect.innerHTML = '<option value="">Loading items...</option>';
        itemSelect.disabled = true;

        fetch(`/inventory/items-by-store/${storeId || 0}`)
            .then(response => response.json())
            .then(items => {
                itemSelect.innerHTML = '<option value="">All Items</option>';
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.Id;
                    option.textContent = item.ItemName ?? item.ItemDescription;
                    itemSelect.appendChild(option);
                });
                itemSelect.disabled = false;
            })
            .catch(() => {
                itemSelect.innerHTML = '<option value="">Error loading items</option>';
                itemSelect.disabled = false;
            });
    }
    @endif
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('itemSelect').addEventListener('change', updateUI);

        @if($isHeadOffice)
        const branchSelect = document.getElementById('branchSelect');
        const storeSelect = document.getElementById('storeSelect');
        branchSelect.addEventListener('change', function() {
            const branchId = this.value;
            loadStores(branchId);
            const itemSelect = document.getElementById('itemSelect');
            itemSelect.innerHTML = '<option value="">All Items</option>';
            itemSelect.disabled = false;
        });
        storeSelect.addEventListener('change', function() {
            const storeId = this.value;
            loadItems(storeId);
        });
        @endif
        updateUI();
        initializeCharts();
        document.querySelectorAll('.btn-group .btn').forEach(button => {
            button.addEventListener('click', function() {
                const parent = this.parentElement;
                parent.querySelectorAll('.btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });
</script>

<style>
    .card {
        border: none;
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .table td, .table th {
        padding: 0.5rem 0.75rem;
        vertical-align: middle;
    }
    
    .movement-summary .net-change {
        border-left: 4px solid;
    }
    
    .movement-summary .net-change.bg-success {
        border-left-color: #198754;
    }
    
    .movement-summary .net-change.bg-danger {
        border-left-color: #dc3545;
    }
    
    .btn-group .btn.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }
    
    @media print {
        .btn, form, .dropdown, .no-print {
            display: none !important;
        }
        
        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
            break-inside: avoid;
        }
        
        .table {
            font-size: 11px;
        }
    }
</style>
@endsection