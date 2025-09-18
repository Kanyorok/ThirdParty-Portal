@extends('layouts.app')
@section('title', 'Edit Recurring Journal')

@section('content')
    <div class="container mt-0">
        <div class="card shadow rounded-4">
            <div class="card-header bg-light py-1 px-3">
                <h6 class="mb-0 text-muted"><i class="fas fa-sync-alt text-info"></i> Edit {{ $journalEntry->RefNo }}</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('recurrentjournal.update', $journalEntry->Id) }}" id="journalForm">
                    @csrf
                    @method('PUT')

                    {{-- Schedule Header --}}
                    @php $rec = $journalEntry->recurringJournals->first(); @endphp
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="StartDate" class="form-control" value="{{ optional($rec)->StartDate ? \Carbon\Carbon::parse(optional($rec)->StartDate)->format('Y-m-d') : date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cutoff Date</label>
                            <input type="date" name="CuttOffDate" class="form-control" value="{{ optional($rec)->CuttOffDate ? \Carbon\Carbon::parse(optional($rec)->CuttOffDate)->format('Y-m-d') : date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Frequency</label>
                            <select name="Frequency" class="form-select" required>
                                @foreach($paymentFrequency as $pf)
                                    <option value="{{ $pf->Value }}" {{ (optional($rec)->Frequency ?? '') === $pf->Value ? 'selected' : '' }}>{{ $pf->Description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference Name</label>
                            <input type="text" name="ReferenceName" class="form-control" value="{{ optional($rec)->ReferenceName }}" required>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label">Description</label>
                            <input type="text" name="Description" class="form-control" value="{{ $journalEntry->Description }}">
                        </div>
                    </div>

                    {{-- Lines --}}
                    <h5 class="border-bottom pb-2 mb-3 text-primary">🧾 Journal Lines</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle table-sm">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>GL Account</th>
                                <th>Branch</th>
                                <th>Department</th>
                                <th>DR / CR</th>
                                <th style="width: 180px">Amount</th>
                                <th>Narration</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody id="journalBody">
                            @foreach($journalEntry->journalLines as $i => $line)
                                <tr>
                                    <td class="line-number">{{ $i + 1 }}</td>
                                    <input type="hidden" name="LineId[]" value="{{ $line->Id }}">
                                    <td>
                                        <select name="GLAccount[]" class="form-select form-control" required>
                                            @foreach($gls as $gl)
                                                <option value="{{ $gl->Id }}" {{ $gl->Id == $line->GLAccountID ? 'selected' : '' }}>{{ $gl->GLName }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="Branch[]" class="form-select" required>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->Id }}" {{ $branch->Id == $line->BranchID ? 'selected' : '' }}>{{ $branch->Name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="Department[]" class="form-select" required>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->Id }}" {{ $department->Id == $line->DepartmentID ? 'selected' : '' }}>{{ $department->Name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        @php $isDebit = (bool)$line->IsDebit; @endphp
                                        <select name="DRCR[]" class="form-select" required>
                                            <option value="DR" {{ $isDebit ? 'selected' : '' }}>DR</option>
                                            <option value="CR" {{ !$isDebit ? 'selected' : '' }}>CR</option>
                                        </select>
                                    </td>
                                    <td>
                                        @php $amount = $isDebit ? $line->Debit : $line->Credit; @endphp
                                        <input type="number" name="Amount[]" class="form-control" step="0.01" value="{{ number_format((float)$amount, 2, '.', '') }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="Narration[]" class="form-control" value="{{ $line->Narration }}">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mb-3">
                        <button type="button" id="addRow" class="btn btn-outline-primary btn-sm">+ Add Line</button>
                    </div>

                    <div class="alert alert-info rounded-3">
                        <strong>Total Debit:</strong> <span id="totalDr">0.00</span> &nbsp;
                        <strong>Total Credit:</strong> <span id="totalCr">0.00</span> &nbsp;
                        <span id="balanceStatus" class="badge bg-warning text-dark fw-bold px-3 py-2" style="font-size: 0.75rem;">
                            Unbalanced
                        </span>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{route('recurrentjournal.index')}}" class="btn btn-secondary">Back</a>
                        <button class="btn btn-success" id="postBtn" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const body = document.getElementById('journalBody');
        const totalDr = document.getElementById('totalDr');
        const totalCr = document.getElementById('totalCr');
        const postBtn = document.getElementById('postBtn');
        const balanceStatus = document.getElementById('balanceStatus');

        function updateLineNumbers() {
            const rows = body.querySelectorAll('tr');
            rows.forEach((row, index) => {
                const lineNumberCell = row.querySelector('.line-number');
                if (lineNumberCell) { lineNumberCell.textContent = index + 1; }
            });
        }
        function calculateTotals() {
            let debit = 0, credit = 0, valid = true;
            [...body.rows].forEach(row => {
                const gl = row.querySelector('select[name="GLAccount[]"]').value;
                const drcr = row.querySelector('select[name="DRCR[]"]').value;
                const amt = parseFloat(row.querySelector('input[name="Amount[]"]').value || 0);
                if (!gl || !drcr || amt <= 0) valid = false;
                if (drcr === 'DR') debit += amt; else if (drcr === 'CR') credit += amt;
            });
            totalDr.textContent = debit.toFixed(2);
            totalCr.textContent = credit.toFixed(2);
            if (debit === credit && debit > 0 && valid) { balanceStatus.className = 'badge bg-success'; balanceStatus.textContent = 'Balanced'; postBtn.disabled = false; }
            else { balanceStatus.className = 'badge bg-danger'; balanceStatus.textContent = 'Unbalanced / Invalid'; postBtn.disabled = true; }
        }
        document.addEventListener('input', calculateTotals);
        document.addEventListener('change', calculateTotals);
        document.getElementById('addRow').addEventListener('click', () => {
            const firstRow = body.querySelector('tr');
            const clone = firstRow.cloneNode(true);
            clone.querySelectorAll('input, select').forEach(el => { if (el.tagName === 'INPUT') el.value=''; if (el.tagName === 'SELECT') el.selectedIndex=0; });
            const hiddenId = clone.querySelector('input[name="LineId[]"]'); if (hiddenId) hiddenId.value = '';
            body.appendChild(clone); updateLineNumbers(); calculateTotals();
        });
        body.addEventListener('click', function (e) {
            if (e.target.closest('.remove-line') && body.rows.length > 1) {
                e.target.closest('tr').remove(); updateLineNumbers(); calculateTotals();
            }
        });
        calculateTotals(); updateLineNumbers();
    </script>
@endsection
