@extends('layouts.app')
@section('title', 'Award Tender')
@section('content')

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            🏆 Award Tender: TENDER/ICT/2025/004 – Procurement of ICT Equipment
        </div>
        <div class="card-body">
            <h5 class="mb-3">📊 Final Scoring Summary</h5>
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>Bidder</th>
                        <th>Technical Score</th>
                        <th>Financial Score</th>
                        <th>Total Score</th>
                        <th>Responsive?</th>
                        <th>Select</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>CompTech Solutions</td>
                        <td>85%</td>
                        <td>92%</td>
                        <td><strong>88.5%</strong></td>
                        <td><span class="badge bg-success">Yes</span></td>
                        <td><input type="radio" name="winner" checked></td>
                    </tr>
                    <tr>
                        <td>NetWave Ltd</td>
                        <td>70%</td>
                        <td>89%</td>
                        <td>79.5%</td>
                        <td><span class="badge bg-success">Yes</span></td>
                        <td><input type="radio" name="winner"></td>
                    </tr>
                </tbody>
            </table>

            <div class="mb-3">
                <label class="form-label">Award Justification</label>
                <textarea class="form-control" rows="3">Highest total score across both technical and financial evaluation.</textarea>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="notify" checked>
                <label class="form-check-label" for="notify">
                    Notify Unsuccessful Bidders
                </label>
            </div>

            <div class="text-end">
                <button class="btn btn-primary">✅ Confirm Tender Award</button>
            </div>
        </div>
    </div>
</div>

@endsection
