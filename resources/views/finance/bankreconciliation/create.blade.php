@extends('layouts.app')
@section('title', 'Bank Reconciliation')
@section('content')

<div class="row mb-3">
            <div class="col-md-6">
                <label for="bank_account" class="form-label">Bank Account</label>
                <select class="form-select" id="bank_account" name="bank_account" required>
                    <option selected disabled>Select account</option>
                    <option value="1">Equity Bank - Main Account</option>
                    <option value="2">Co-op Bank - Operations</option>
                    <option value="3">KCB - Payroll Account</option>
                </select>

            </div>
            <div class="col-md-6">
                <label for="recon_date" class="form-label">Reconciliation Date</label>
                <input type="date" class="form-control" id="recon_date" name="recon_date" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="statement_balance" class="form-label">Bank Statement Closing Balance</label>
            <input type="number" step="0.01" class="form-control" id="statement_balance" name="statement_balance" required>
        </div>

        <div class="mb-3">
            <label for="gl_balance" class="form-label">General Ledger Balance</label>
            <input type="number" step="0.01" class="form-control" id="gl_balance" name="gl_balance" required>
        </div>

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks / Notes</label>
            <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
        </div>

@endsection