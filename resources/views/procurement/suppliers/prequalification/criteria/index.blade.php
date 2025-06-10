@extends('layouts.app')
@section('title', 'Setup Evaluation Structure')
@section('content')

<div class="card">
  <div class="card-header bg-primary text-white">Evaluation Sections & Criteria Setup</div>
  <div class="card-body">

    <form method="post" action="#">
      <div class="mb-3">
        <label for="round_id" class="form-label">Prequalification Round</label>
        <select id="round_id" name="round_id" class="form-select">
          <option selected disabled>Select Round</option>
          <option value="1">PRQ2025-001 - Stationery</option>
          <option value="2">PRQ2025-002 - IT Equipment</option>
        </select>
      </div>

      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>Select</th>
            <th>Evaluation Section</th>
            <th>Criteria Setup</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox" name="sections[]" value="1" class="form-check-input"></td>
            <td>Technical Evaluation</td>
            <td><a href="{{ route('preqcriteria.create', ['section_id' => 1]) }}" class="btn btn-sm btn-outline-primary">⚙️ Setup Criteria</a></td>
          </tr>
          <tr>
            <td><input type="checkbox" name="sections[]" value="2" class="form-check-input"></td>
            <td>Financial Evaluation</td>
            <td><a href="{{ route('preqcriteria.create', ['section_id' => 2]) }}" class="btn btn-sm btn-outline-primary">⚙️ Setup Criteria</a></td>
          </tr>
          <tr>
            <td><input type="checkbox" name="sections[]" value="3" class="form-check-input"></td>
            <td>Legal & Compliance</td>
            <td><a href="{{ route('preqcriteria.create', ['section_id' => 3]) }}" class="btn btn-sm btn-outline-primary">⚙️ Setup Criteria</a></td>
          </tr>
        </tbody>
      </table>

      <button type="submit" class="btn btn-success">Save Sections</button>
    </form>

  </div>
</div>

@endsection
