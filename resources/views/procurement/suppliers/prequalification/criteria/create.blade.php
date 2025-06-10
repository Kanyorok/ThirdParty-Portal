@extends('layouts.app')
@section('title', 'Setup Evaluation Criteria')
@section('content')

    <div class="card">
        <div class="card-header bg-info text-white">
            🎯 Setup Criteria for <strong>Technical Evaluation</strong> — Round: <strong>2025 General Suppliers
                Prequalification</strong>
        </div>

        <div class="card-body">
            <form method="POST" action="#">
                <!-- Static Hidden Inputs -->
                <input type="hidden" name="round_id" value="1">
                <input type="hidden" name="section_id" value="1">

                <table class="table table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th>Select</th>
                        <th>Criterion</th>
                        <th>Description</th>
                        <th>Weight (%)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><input type="checkbox" name="criteria_ids[]" value="1" class="form-check-input" checked>
                        </td>
                        <td>Past Experience</td>
                        <td>Years of relevant experience with references.</td>
                        <td><input type="number" name="weights[1]" class="form-control" value="20"></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="criteria_ids[]" value="2" class="form-check-input"></td>
                        <td>Key Personnel</td>
                        <td>Qualified technical staff with CVs and certifications.</td>
                        <td><input type="number" name="weights[2]" class="form-control" value="25"></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="criteria_ids[]" value="3" class="form-check-input"></td>
                        <td>Equipment</td>
                        <td>Availability of required equipment and logistics.</td>
                        <td><input type="number" name="weights[3]" class="form-control" value="15"></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="criteria_ids[]" value="4" class="form-check-input"></td>
                        <td>Methodology</td>
                        <td>Approach and work plan for the assignment.</td>
                        <td><input type="number" name="weights[4]" class="form-control" value="10"></td>
                    </tr>
                    </tbody>
                </table>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('preqcriteria.index') }}" class="btn btn-secondary">← Back to Sections</a>
                    <button type="submit" class="btn btn-success">💾 Save Selected Criteria</button>
                </div>
            </form>
        </div>
    </div>

@endsection
