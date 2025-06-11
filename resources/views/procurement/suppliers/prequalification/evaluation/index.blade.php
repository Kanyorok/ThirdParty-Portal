@extends('layouts.app')
@section('title', 'Evaluate Supplier Application')
@section('content')

<div class="card mb-4">
  <div class="card-header bg-success text-white">
    🧮 Evaluation Sheet - ABC Engineering Ltd (Round: 2025 General Prequalification)
  </div>
  <div class="card-body">

    <h5 class="mb-3">📌 Evaluation Sections & Criteria</h5>

    <!-- Example Section 1 -->
    <div class="card mb-4 border">
      <div class="card-header bg-light">
        <strong>1. Technical Capacity</strong> (Weight: 40%)
      </div>
      <div class="card-body">
        <table class="table table-sm table-bordered">
          <thead class="table-secondary">
            <tr>
              <th>Criteria</th>
              <th>Max Score</th>
              <th>Score Awarded</th>
              <th>Comments</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Years in Similar Business</td>
              <td>10</td>
              <td><input type="number" class="form-control" max="10" min="0" value="8"></td>
              <td><input type="text" class="form-control" value="5+ years experience"></td>
            </tr>
            <tr>
              <td>Past Contracts Completed</td>
              <td>15</td>
              <td><input type="number" class="form-control" max="15" min="0" value="12"></td>
              <td><input type="text" class="form-control" value="3 similar contracts"></td>
            </tr>
            <tr>
              <td>Key Personnel Qualifications</td>
              <td>15</td>
              <td><input type="number" class="form-control" max="15" min="0" value="14"></td>
              <td><input type="text" class="form-control" value="Engineers qualified"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Example Section 2 -->
    <div class="card mb-4 border">
      <div class="card-header bg-light">
        <strong>2. Financial Stability</strong> (Weight: 30%)
      </div>
      <div class="card-body">
        <table class="table table-sm table-bordered">
          <thead class="table-secondary">
            <tr>
              <th>Criteria</th>
              <th>Max Score</th>
              <th>Score Awarded</th>
              <th>Comments</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Audited Financials</td>
              <td>15</td>
              <td><input type="number" class="form-control" max="15" min="0" value="13"></td>
              <td><input type="text" class="form-control" value="Provided 2 years' reports"></td>
            </tr>
            <tr>
              <td>Bank Statements / Liquidity</td>
              <td>15</td>
              <td><input type="number" class="form-control" max="15" min="0" value="10"></td>
              <td><input type="text" class="form-control" value="Average liquidity"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Optional: Add More Sections Dynamically -->

    <div class="mb-3">
      <label><strong>General Comments:</strong></label>
      <textarea class="form-control" rows="4">Meets most criteria; recommend approval.</textarea>
    </div>

    <div class="d-flex justify-content-between">
      <a href="{{ route('preqapplications.index') }}" class="btn btn-secondary">← Back to Application</a>
      <button class="btn btn-primary">✅ Submit Evaluation</button>
    </div>

  </div>
</div>

@endsection
