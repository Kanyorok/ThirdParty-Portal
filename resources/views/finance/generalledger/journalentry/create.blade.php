@extends('layouts.app')
@section('title', 'Journal Entry')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📝 New Journal Entry</h4>

        <form method="POST" action="#">
            @csrf
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Journal Date</label>
                    <input type="date" name="JournalDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reference Number</label>
                    <input type="text" name="ReferenceNumber" class="form-control" placeholder="e.g., JV20240601"
                           required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Transaction Type</label>
                    <select name="TransactionTypeID" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="JE">JE – Manual Journal Entry</option>
                        <option value="REVJ">REVJ – Reversing Journal Entry</option>
                        <option value="RECUR">RECUR – Recurring Journal Entry</option>
                        <option value="ADJ">ADJ – GL Adjustment</option>
                        <option value="FXGAIN">FXGAIN – Forex Gain/Loss Adjustment</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="Description" class="form-control" placeholder="e.g., Salary Payment - May">
                </div>
            </div>

            <hr>
            <h5>Journal Lines</h5>

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>GL Account</th>
                    <th>Branch</th>
                    <th>Department</th>
                    <th>DR/CR</th>
                    <th>Amount</th>
                    <th>Narration</th>
                </tr>
                </thead>
                <tbody>
                @for ($i = 0; $i < 3; $i++)
                    <tr>
                        <td>
                            <select name="GLAccount[]" class="form-select">
                                <option value="">Select</option>
                                <option value="1000">1000 - Cash & Bank</option>
                                <option value="1100">1100 - Cash - HQ</option>
                                <option value="2000">2000 - Accounts Payable</option>
                                <option value="3000">3000 - Revenue</option>
                                <option value="4000">4000 - Salary Expenses</option>
                            </select>
                        </td>
                        <td>
                            <select name="Branch[]" class="form-select">
                                <option value="001">001 - HQ</option>
                                <option value="002">002 - Nairobi</option>
                                <option value="003">003 - Mombasa</option>
                            </select>
                        </td>
                        <td>
                            <select name="Department[]" class="form-select">
                                <option value="100">100 - Finance</option>
                                <option value="200">200 - HR</option>
                                <option value="300">300 - Operations</option>
                            </select>
                        </td>
                        <td>
                            <select name="DRCR[]" class="form-select">
                                <option value="DR">DR</option>
                                <option value="CR">CR</option>
                            </select>
                        </td>
                        <td><input type="number" name="Amount[]" class="form-control" step="0.01"></td>
                        <td><input type="text" name="Narration[]" class="form-control"></td>
                    </tr>
                @endfor
                </tbody>
            </table>

            <div class="alert alert-info">
                <strong>Total Debit:</strong> 100,000.00 &nbsp;&nbsp;
                <strong>Total Credit:</strong> 100,000.00 &nbsp;&nbsp;
                <span class="badge bg-success">Balanced</span>
            </div>

            <button type="submit" class="btn btn-success">Post Journal</button>
            <a href="/finance/general-ledger" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
