@extends('layouts.app')
@section('title', 'Maintain Transaction Types')
@section('content')
<div class="container">
    <div class="card shadow rounded-3">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0">Create Transaction Type</h5>
      </div>
      <div class="card-body">
        <form id="transactionTypeForm">
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="trxCode" class="form-label">Transaction Code</label>
              <input type="text" class="form-control" id="trxCode" name="trxCode" required>
            </div>
            <div class="col-md-8">
              <label for="trxName" class="form-label">Transaction Name</label>
              <input type="text" class="form-control" id="trxName" name="trxName" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="module" class="form-label">Module</label>
              <select class="form-select" id="module" name="module" required>
                <option value="">Select Module</option>
                <option value="GL">General Ledger</option>
                <option value="CB">Cashbook</option>
                <option value="AR">Accounts Receivable</option>
                <option value="AP">Accounts Payable</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="category" class="form-label">Category</label>
              <select class="form-select" id="category" name="category" required>
                <option value="">Select Category</option>
                <option value="Credit">Credit</option>
                <option value="Debit">Debit</option>
                <option value="Transfer">Transfer</option>
                <option value="Adjustment">Adjustment</option>
              </select>
            </div>
          </div>

          <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" id="affectsLedger" name="affectsLedger">
            <label class="form-check-label" for="affectsLedger">Affects Ledger</label>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description (Optional)</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
          </div>

          <div class="d-flex justify-content-end">

 @endsection