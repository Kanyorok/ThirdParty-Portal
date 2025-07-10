@extends('layouts.app')
@section('title', 'Bank Reconciliation Dashboard')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">🏦 Bank Reconciliation Dashboard</h4>

        <!-- Bank Account Selector -->
        <div class="mb-3">
            <label>Select Bank Account:</label>
            <select id="bankSelector" class="form-select w-50">
                <option value="equity" selected>001 - Equity Main Account</option>
                <option value="kcb">002 - KCB Collections</option>
            </select>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4" id="summaryCards">
            <!-- Equity Stats initially -->
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h6>Total Uploads</h6>
                        <h4 id="uploads">8</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h6>Pending Reconciliations</h6>
                        <h4 id="pending">3</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h6>Completed Reconciliations</h6>
                        <h4 id="completed">5</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h6>Last Upload Date</h6>
                        <h4 id="lastUpload">2025-06-30</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matching Summary -->
        <div class="card mb-4">
            <div class="card-header">Matching Summary</div>
            <div class="card-body">
                <ul class="list-group list-group-horizontal">
                    <li class="list-group-item flex-fill text-success" id="matched">✔ Matched: 80%</li>
                    <li class="list-group-item flex-fill text-danger" id="unmatched">❌ Unmatched: 15%</li>
                    <li class="list-group-item flex-fill text-warning" id="partial">⚠ Partial Match: 5%</li>
                </ul>
            </div>
        </div>

        <!-- Exceptions Panel -->
        <div class="card mb-4">
            <div class="card-header">Unmatched Statement Lines</div>
            <div class="card-body">
                <table class="table table-bordered table-sm" id="exceptionsTable">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Ref No.</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Injected via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-4">
            <h5>🔧 Quick Actions</h5>
            <a href="#" class="btn btn-outline-success">📤 Upload Statement</a>
            <a href="#" class="btn btn-outline-primary">🔍 Start Reconciliation</a>
            <a href="#" class="btn btn-outline-info">🧾 View Matches</a>
            <a href="#" class="btn btn-outline-secondary">🧮 Manual Match</a>
        </div>

        <!-- Reconciliation History -->
        <div class="card">
            <div class="card-header">📊 Reconciliation History</div>
            <div class="card-body">
                <table class="table table-bordered table-sm" id="historyTable">
                    <thead>
                    <tr>
                        <th>Reconcile ID</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Matched</th>
                        <th>Unmatched</th>
                        <th>Finalized By</th>
                        <th>Finalized At</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Injected via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const data = {
            equity: {
                uploads: 8,
                pending: 3,
                completed: 5,
                lastUpload: '2025-06-30',
                matched: '✔ Matched: 80%',
                unmatched: '❌ Unmatched: 15%',
                partial: '⚠ Partial Match: 5%',
                exceptions: [
                    ['2025-06-28', 'Unidentified Transfer', 'REF12345', '15,000.00', 'Unmatched'],
                    ['2025-06-27', 'Cheque Deposit', 'REF99887', '25,000.00', 'Partial Match']
                ],
                history: [
                    ['RECON001', 'Jun 2025', 'Completed', 120, 5, 'Jane', '2025-06-29'],
                    ['RECON002', 'May 2025', 'Pending', 95, 12, 'John', '--']
                ]
            },
            kcb: {
                uploads: 5,
                pending: 1,
                completed: 4,
                lastUpload: '2025-06-25',
                matched: '✔ Matched: 85%',
                unmatched: '❌ Unmatched: 10%',
                partial: '⚠ Partial Match: 5%',
                exceptions: [
                    ['2025-06-24', 'POS Settlement Delay', 'KCB1002', '10,000.00', 'Unmatched'],
                    ['2025-06-22', 'Over-the-Counter', 'KCB1105', '5,000.00', 'Partial Match']
                ],
                history: [
                    ['RECON005', 'Jun 2025', 'Completed', 110, 3, 'Mary', '2025-06-26'],
                    ['RECON004', 'May 2025', 'Completed', 102, 2, 'Ali', '2025-05-30']
                ]
            }
        };

        document.getElementById('bankSelector').addEventListener('change', function () {
            const selected = this.value;
            const d = data[selected];

            document.getElementById('uploads').innerText = d.uploads;
            document.getElementById('pending').innerText = d.pending;
            document.getElementById('completed').innerText = d.completed;
            document.getElementById('lastUpload').innerText = d.lastUpload;
            document.getElementById('matched').innerText = d.matched;
            document.getElementById('unmatched').innerText = d.unmatched;
            document.getElementById('partial').innerText = d.partial;

            // Exceptions
            const exceptionTable = document.querySelector('#exceptionsTable tbody');
            exceptionTable.innerHTML = '';
            d.exceptions.forEach(e => {
                exceptionTable.innerHTML += `<tr>
                <td>${e[0]}</td>
                <td>${e[1]}</td>
                <td>${e[2]}</td>
                <td>${e[3]}</td>
                <td class="${e[4] === 'Unmatched' ? 'text-danger' : 'text-warning'}">${e[4]}</td>
                <td><button class="btn btn-outline-primary btn-sm">🧮 Match</button></td>
            </tr>`;
            });

            // History
            const historyTable = document.querySelector('#historyTable tbody');
            historyTable.innerHTML = '';
            d.history.forEach(h => {
                historyTable.innerHTML += `<tr>
                <td>${h[0]}</td>
                <td>${h[1]}</td>
                <td><span class="badge bg-${h[2] === 'Completed' ? 'success' : 'warning'}">${h[2]}</span></td>
                <td>${h[3]}</td>
                <td>${h[4]}</td>
                <td>${h[5]}</td>
                <td>${h[6]}</td>
            </tr>`;
            });
        });
    </script>
@endsection
