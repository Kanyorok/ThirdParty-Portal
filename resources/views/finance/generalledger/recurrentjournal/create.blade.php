@extends('layouts.app')
@section('title',' Recurring Journal')
@section('content')
    <div class="container mt-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow rounded-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-sync-alt text-info me-2"></i>
                    Recurring Journal
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{route('recurrentjournal.store')}}" id="recurringJournalForm">
                    @csrf
                    @method('POST')

                    {{-- Header --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="StartDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cut-off Date</label>
                            <input type="date" name="CuttOffDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Frequency</label>
                            <select name="Frequency" class="form-select" required>
                                <option value="">-- Select --</option>
                                @forelse($paymentFrequency as $pf)
                                    <option value="{{$pf->Value}}">{{$pf->Description}}</option>
                                @empty
                                    <option disabled selected>No records found</option>
                                @endforelse

                            </select>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label">Reference Name</label>
                            <input type="text" name="ReferenceName" class="form-control" placeholder="e.g., Monthly Rent" required>
                        </div>
                        <div class="col-md-8 mt-3">
                            <label class="form-label">Description</label>
                            <input type="text" name="Description" class="form-control" placeholder="Optional">
                        </div>
                    </div>

                    {{-- Recurring Lines --}}
                    <h5 class="border-bottom pb-2 mb-3 text-primary">📋 Recurring Journal Lines</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle table-sm">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>GL Account</th>
                                <th>Branch</th>
                                <th>Department</th>
                                <th>DR / CR</th>
                                <th>Amount</th>
                                <th>Narration</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody id="recurringBody">
                            @for ($i = 0; $i < 3; $i++)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <select name="GLAccount[]" class="form-select form-control" required>
                                            <option value="" selected disabled>Select GL</option>
                                            @foreach($gls as $gl)
                                                <option value="{{$gl->Id}}">{{$gl->GLName}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="Branch[]" class="form-select" required>
                                            @foreach($branches as $branch)
                                                <option value="{{$branch->Id}}">{{$branch->Name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="Department[]" class="form-select" required>
                                            @foreach($departments as $department)
                                                <option value="{{$department->Id}}">{{$department->Name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="DRCR[]" class="form-select drcr-select" style="width: 1500%;max-width: max-content" required>
                                            <option value="DR">DR</option>
                                            <option value="CR">CR</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="Amount[]" class="form-control amount-input" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="text" name="Narration[]" class="form-control" placeholder="Narration">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-3">
                        <button type="button" id="addRow" class="btn btn-outline-primary btn-sm">+ Add Line</button>
                    </div>

                    <div class="alert alert-info rounded-3">
                        <strong>Total Debit:</strong> <span id="totalDr">0.00</span> &nbsp;
                        <strong>Total Credit:</strong> <span id="totalCr">0.00</span> &nbsp;
                        <span id="balanceStatus" class="badge bg-warning text-dark">Unbalanced</span>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{route('recurrentjournal.index')}}" class="btn btn-secondary">Back</a>
                        <button class="btn btn-success" id="saveBtn" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">Save Recurring Journal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const recurringBody = document.getElementById('recurringBody');
        const totalDr = document.getElementById('totalDr');
        const totalCr = document.getElementById('totalCr');
        const saveBtn = document.getElementById('saveBtn');
        const balanceStatus = document.getElementById('balanceStatus');

        function calculateRecurringTotals() {
            let debit = 0, credit = 0, valid = true;

            [...recurringBody.rows].forEach(row => {
                const gl = row.querySelector('select[name="GLAccount[]"]').value;
                const drcr = row.querySelector('select[name="DRCR[]"]').value;
                const amt = parseFloat(row.querySelector('input[name="Amount[]"]').value) || 0;

                if (!gl || !drcr || amt <= 0) valid = false;

                if (drcr === 'DR') debit += amt;
                else if (drcr === 'CR') credit += amt;
            });

            totalDr.textContent = debit.toFixed(2);
            totalCr.textContent = credit.toFixed(2);

            if (debit === credit && debit > 0 && valid) {
                balanceStatus.className = 'badge bg-success';
                balanceStatus.textContent = 'Balanced';
                saveBtn.disabled = false;
            } else {
                balanceStatus.className = 'badge bg-danger';
                balanceStatus.textContent = 'Unbalanced / Invalid';
                saveBtn.disabled = true;
            }
        }

        function updateRowNumbers() {
            [...recurringBody.rows].forEach((row, index) => {
                row.cells[0].textContent = index + 1;
            });
        }

        document.addEventListener('input', calculateRecurringTotals);
        document.addEventListener('change', calculateRecurringTotals);

        document.getElementById('addRow').addEventListener('click', () => {
            const firstRow = recurringBody.querySelector('tr');
            const clone = firstRow.cloneNode(true);

            clone.querySelectorAll('input, select').forEach(el => {
                if (el.tagName === 'INPUT') el.value = '';
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });

            recurringBody.appendChild(clone);
            updateRowNumbers();
            calculateRecurringTotals();
        });

        recurringBody.addEventListener('click', function (e) {
            if (e.target.closest('.remove-line') && recurringBody.rows.length > 1) {
                e.target.closest('tr').remove();
                updateRowNumbers();
                calculateRecurringTotals();
            }
        });

        updateRowNumbers(); // Ensure numbers are correct initially
        calculateRecurringTotals(); // Initial run
    </script>

@endsection
