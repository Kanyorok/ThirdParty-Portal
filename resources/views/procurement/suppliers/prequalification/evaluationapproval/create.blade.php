@extends('layouts.app')
@section('title', 'Prequalification Evaluation Summary')
@section('content')

<div class="card mb-4">
  <div class="card-header bg-info text-white">
    📊 Evaluation Summary - ABC Engineering Ltd (Period: 2025 General Prequalification)
  </div>
  <div class="card-body">

    <h5>🔍 Application Overview</h5>
    <table class="table table-sm table-bordered mb-4">
      <tr><th>Supplier Name</th><td>ABC Engineering Ltd</td></tr>
      <tr><th>Business Type</th><td>Company</td></tr>
      <tr><th>Submitted On</th><td>2025-06-08</td></tr>
    </table>

    <h5>🧮 Evaluation Breakdown</h5>
    <table class="table table-bordered table-sm">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Section</th>
          <th>Weight (%)</th>
          <th>Max Score</th>
          <th>Score Awarded</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Technical Capacity</td>
          <td>40%</td>
          <td>40</td>
          <td>34</td>
        </tr>
        <tr>
          <td>2</td>
          <td>Financial Stability</td>
          <td>30%</td>
          <td>30</td>
          <td>23</td>
        </tr>
        <tr>
          <td>3</td>
          <td>Legal & Compliance</td>
          <td>30%</td>
          <td>30</td>
          <td>25</td>
        </tr>
        <tr class="table-secondary fw-bold">
          <td colspan="3">TOTAL</td>
          <td>100</td>
          <td>82</td>
        </tr>
      </tbody>
    </table>

    <h5 class="mt-4">💬 Evaluator Comments</h5>
    <blockquote class="blockquote border-start border-3 ps-3">
      Supplier demonstrated strong technical and financial capacity. All required documents submitted and valid.
    </blockquote>

    <h5 class="mt-4">📌 Approval Decision</h5>
    <form>
      <div class="mb-3">
        <label for="decision">Action</label>
        <select class="form-select" id="decision">
          <option selected disabled>-- Select Decision --</option>
          <option>Approve</option>
          <option>Reject</option>
          <option>Request Rescore</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="managerComment">Manager Comments</label>
        <textarea class="form-control" id="managerComment" rows="3">Looks eligible for approval.</textarea>
      </div>

      <div class="d-flex justify-content-end">
        <a href="{{ url()->previous() }}" class="btn btn-secondary me-2">Back</a>
        <button type="submit" class="btn btn-primary">Submit Decision</button>
      </div>
    </form>

  </div>
</div>

@endsection
