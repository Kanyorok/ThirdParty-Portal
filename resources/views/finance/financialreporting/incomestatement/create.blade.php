@extends('layouts.app')
@section('title', 'Income Statements')
@section('content')

<div class="container my-5">
  <div class="text-center mb-4">
    <h2>Income Statement Report</h2>
  </div>

  <!-- All Income Statements in the Income Statements Section -->
  <div class="income-statements">
    <?php foreach ($periods as $index => $period_data): ?>
      <div class="section-title"><?= $index + 1 ?>. Income Statement - Period: <?= $period_data['period'] ?></div>
      <table class="table table-bordered">
        <thead class="thead-light">
          <tr>
            <th>Category</th>
            <th>Amount (USD)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Revenue</td>
            <td>$<?= number_format($period_data['revenue'], 2) ?></td>
          </tr>
          <tr>
            <td>Cost of Goods Sold (COGS)</td>
            <td>$<?= number_format($period_data['cogs'], 2) ?></td>
          </tr>
          <tr>
            <td><strong>Gross Profit</strong></td>
            <td><strong>$<?= number_format($period_data['revenue'] - $period_data['cogs'], 2) ?></strong></td>
          </tr>
          <tr>
            <td>Operating Expenses</td>
            <td>$<?= number_format($period_data['operating_expenses'], 2) ?></td>
          </tr>
          <tr>
            <td><strong>Operating Income</strong></td>
            <td><strong>$<?= number_format($period_data['revenue'] - $period_data['cogs'] - $period_data['operating_expenses'], 2) ?></strong></td>
          </tr>
          <tr>
            <td>Other Income</td>
            <td>$<?= number_format($period_data['other_income'], 2) ?></td>
          </tr>
          <tr>
            <td><strong>Net Income</strong></td>
            <td><strong>$<?= number_format($period_data['revenue'] - $period_data['cogs'] - $period_data['operating_expenses'] + $period_data['other_income'], 2) ?></strong></td>
          </tr>
        </tbody>
      </table>
    <?php endforeach; ?>
  </div>



@endsection