@extends('layouts.app')
@section('title', 'CBS GL Mapping')
@section('content')

<div class="card p-3">
  <h5>📊 CBS GL Mapping</h5>
  <table class="table table-striped table-hover">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Budget Line</th>
        <th>CBS GL Code</th>
        <th>GL Description</th>
        <th>Primary?</th>
        <th>Notes</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Interest Income – Loans</td>
        <td>401001</td>
        <td>Loan Interest Receivable</td>
        <td><span class="badge bg-success">Yes</span></td>
        <td>Main loan product line</td>
      </tr>
      <tr>
        <td>2</td>
        <td>Interest Expense – Deposits</td>
        <td>502001</td>
        <td>Deposit Interest Payable</td>
        <td><span class="badge bg-secondary">No</span></td>
        <td>Mapped for CBS cost tracking</td>
      </tr>
    </tbody>
  </table>
</div>

@endsection