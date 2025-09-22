@extends('layouts.app')
@section('title', 'Journal Entry')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="container mt-0">
        <div class="card shadow rounded-4">
            <div class="card-header bg-light py-1 px-3">
                <h6 class="mb-0 text-muted"><i class="fab fa-wpforms text-info"></i></h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{route('journalentry.store')}}" id="journalForm">
                    @csrf
                    @method('POST')

                    {{-- Journal Header --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Journal Date</label>
                            <input type="date" name="JournalDate" class="form-control" value="{{ date('Y-m-d') }}"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="Description" class="form-control"
                                   placeholder="e.g., Loan Disbursement">
                        </div>
                    </div>

                    {{-- Journal Lines --}}
                    <h5 class="border-bottom pb-2 mb-3 text-primary">🧾 Journal Lines</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle table-sm">
                            <thead class="table-light">
                            <tr>
                                <th>#</th> {{-- Reintroduced line number column --}}
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
                            @for ($i = 0; $i < 2; $i++)
                                <tr>
                                    <td class="line-number">{{ $i + 1 }}</td> {{-- Line number cell --}}
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
                                        <select name="DRCR[]" class="form-select drcr-select"
                                                style="width: 1500%;max-width: max-content" required>
                                            <option value="DR">DR</option>
                                            <option value="CR">CR</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="Amount[]" class="form-control amount-input"
                                               step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="text" name="Narration[]" class="form-control"
                                               placeholder="Narration">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-line"
                                                title="Remove">
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
                        <span id="balanceStatus" class="badge bg-warning text-dark fw-bold px-3 py-2"
                              style="font-size: 0.75rem;">
                            Unbalanced
                        </span>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{route('journalentry.index')}}" class="btn btn-secondary">Back</a>
                        <button class="btn btn-success" id="postBtn" type="submit"
                                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">
                            Save Journal
                        </button>
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

        // Function to update line numbers
        function updateLineNumbers() {
            const rows = body.querySelectorAll('tr');
            rows.forEach((row, index) => {
                const lineNumberCell = row.querySelector('.line-number');
                if (lineNumberCell) {
                    lineNumberCell.textContent = index + 1;
                }
            });
        }

        // Function to calculate totals and validate
        function calculateTotals() {
            let debit = 0, credit = 0, valid = true;

            [...body.rows].forEach(row => {
                const gl = row.querySelector('select[name="GLAccount[]"]')?.value;
                const drcr = row.querySelector('select[name="DRCR[]"]')?.value;
                const amt = parseFloat(row.querySelector('input[name="Amount[]"]')?.value || 0);

                if (!gl || !drcr || amt <= 0) valid = false;

                if (drcr === 'DR') debit += amt;
                else if (drcr === 'CR') credit += amt;
            });

            totalDr.textContent = debit.toFixed(2);
            totalCr.textContent = credit.toFixed(2);

            if (debit === credit && debit > 0 && valid) {
                balanceStatus.className = 'badge bg-success';
                balanceStatus.textContent = 'Balanced';
                postBtn.disabled = false;
            } else {
                balanceStatus.className = 'badge bg-danger';
                balanceStatus.textContent = 'Unbalanced / Invalid';
                postBtn.disabled = true;
            }
        }

        // Event listeners for input and change events
        document.addEventListener('input', calculateTotals);
        document.addEventListener('change', calculateTotals);

        // Add new row
        document.getElementById('addRow').addEventListener('click', () => {
            const firstRow = body.querySelector('tr');
            const clone = firstRow.cloneNode(true);

            // Clear inputs and reset selects in the cloned row
            clone.querySelectorAll('input, select').forEach(el => {
                if (el.tagName === 'INPUT') el.value = '';
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });

            // Append the new row
            body.appendChild(clone);

            // Update line numbers and recalculate totals
            updateLineNumbers();
            calculateTotals();
        });

        // Remove row
        body.addEventListener('click', function (e) {
            if (e.target.closest('.remove-line') && body.rows.length > 1) {
                e.target.closest('tr').remove();
                updateLineNumbers();
                calculateTotals();
            }
        });

        // Initial calculations and line number setup
        calculateTotals();
        updateLineNumbers();
    </script>
@endsection
