@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🛠️ Assign Roles to Committee Members</h4>
    <form>
        <!-- Tender Selection -->
        <div class="mb-3">
            <label for="tenderRef" class="form-label">Tender Reference</label>
            <select class="form-select" id="tenderRef" required>
                <option selected disabled>-- Select Tender --</option>
                <option value="TND/PROC/2025/001">TND/PROC/2025/001 - ICT Equipment</option>
                <option value="TND/PROC/2025/002">TND/PROC/2025/002 - Office Furniture</option>
            </select>
        </div>

        <!-- Committee Members and Role Assignment -->
        <div class="mb-3">
            <label class="form-label">Assign Roles</label>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Member Name</th>
                            <th>Current Role</th>
                            <th>Assign New Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example Row -->
                        <tr>
                            <td>Grace A. – Finance Officer</td>
                            <td>Member</td>
                            <td>
                                <select class="form-select">
                                    <option selected disabled>-- Select Role --</option>
                                    <option>Chairperson</option>
                                    <option>Technical Evaluator</option>
                                    <option>Financial Evaluator</option>
                                    <option>Legal Advisor</option>
                                    <option>Observer</option>
                                </select>
                            </td>
                        </tr>
                        <!-- Repeat rows for other members -->
                    </tbody>
                </table>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Role Assignments</button>
    </form>
</div>

@endsection
