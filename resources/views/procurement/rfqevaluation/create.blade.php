@extends('layouts.app')
@section('title', 'RFQ Evaluation')
@section('content')
<div class="container">
    <h2 class="mb-4">Supplier Evaluation Form</h2>

    <!-- Committee Member and RFQ -->
    <form>
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="committeeName" class="form-label">Committee Member Name</label>
          <input type="text" class="form-control" id="committeeName" placeholder="Enter name">
        </div>
        <div class="col-md-6">
          <label for="rfqNo" class="form-label">RFQ No</label>
          <select class="form-select" id="rfqNo">
            <option selected disabled>Choose RFQ No</option>
            <option value="RFQ001">RFQ001</option>
            <option value="RFQ002">RFQ002</option>
            <option value="RFQ003">RFQ003</option>
          </select>
        </div>
      </div>

      <!-- Supplier Summary Table -->
      <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped">
          <thead class="table-light">
            <tr>
              <th>Supplier Name</th>
              <th>Total Quoted (USD)</th>
              <th>Delivery Time (Days)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Supplier A</td>
              <td>$10,000</td>
              <td>5</td>
            </tr>
            <tr>
              <td>Supplier B</td>
              <td>$9,500</td>
              <td>7</td>
            </tr>
            <tr>
              <td>Supplier C</td>
              <td>$11,200</td>
              <td>4</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Evaluation Form Table -->
      <h5 class="mb-3">Evaluation Records (Per Supplier)</h5>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-secondary">
            <tr>
              <th>Evaluation Criteria</th>
              <th>Weight (%)</th>
              <th>Score (1-10)</th>
              <th>Comments</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Price Competitiveness</td>
              <td><input type="number" class="form-control" placeholder="e.g. 40"></td>
              <td><input type="number" class="form-control" placeholder="e.g. 8"></td>
              <td><input type="text" class="form-control" placeholder="Add comments..."></td>
            </tr>
            <tr>
              <td>Delivery Schedule</td>
              <td><input type="number" class="form-control"></td>
              <td><input type="number" class="form-control"></td>
              <td><input type="text" class="form-control"></td>
            </tr>
            <tr>
              <td>Quality Assurance</td>
              <td><input type="number" class="form-control"></td>
              <td><input type="number" class="form-control"></td>
              <td><input type="text" class="form-control"></td>
            </tr>
            <!-- Add more criteria rows as needed -->
          </tbody>
        </table>
      </div>

      <!-- Submit -->
      <div class="text-end mt-3">
        <button type="submit" class="btn btn-primary">Submit Evaluation</button>
      </div>
    </form>
  </div>

@endsection