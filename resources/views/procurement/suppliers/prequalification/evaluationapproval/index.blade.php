@extends('layouts.app')
@section('title', 'Prequalification Evaluations Pending Approval')
@section('content')

    <div class="card">
        <div class="card-header bg-primary text-white">
            🗂️ Evaluation Approvals Dashboard
        </div>
        <div class="card-body">

            <div class="mb-3">
                <label for="round_id">Select Prequalification period</label>
                <select name="round_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Select period --</option>
                    <option value="1">2025 - General Services</option>
                    <option value="2">2025 - ICT Vendors</option>
                    <!-- Populate dynamically -->
                </select>
            </div>

            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Supplier</th>
                    <th>Round</th>
                    <th>Submitted On</th>
                    <th>Total Score</th>
                    <th>Evaluator</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>ABC Engineering Ltd</td>
                    <td>2025 General Prequalification</td>
                    <td>2025-06-08</td>
                    <td>82%</td>
                    <td>John Otieno</td>
                    <td><span class="badge bg-warning text-dark">Pending Approval</span></td>
                    <td>
                        <a href="{{ route('preqevalapproval.create') }}" class="btn btn-sm btn-primary">Review</a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Greenbuild Ltd</td>
                    <td>2025 General Prequalification</td>
                    <td>2025-06-08</td>
                    <td>76%</td>
                    <td>Jane Mwikali</td>
                    <td><span class="badge bg-warning text-dark">Pending Approval</span></td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">Review</a>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Skyline Traders</td>
                    <td>2025 ICT Prequalification</td>
                    <td>2025-06-07</td>
                    <td>68%</td>
                    <td>Peter Ndegwa</td>
                    <td><span class="badge bg-warning text-dark">Pending Approval</span></td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">Review</a>
                    </td>
                </tr>
                </tbody>
            </table>

        </div>
    </div>

@endsection
