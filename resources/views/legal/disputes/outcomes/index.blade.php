@extends('layouts.app')
@section('title', 'Case Outcome – ' . $case->CaseTitle)

@section('content')
    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between mb-3">
            <h4>📜 Case Outcomes for: {{ $case->CaseTitle }}</h4>
            <a href="{{ route('legal.disputes.outcomes.create', $case->Id) }}" class="btn btn-primary">➕ Add Outcome</a>
        </div>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Outcome</th>
                <th>Judgment Date</th>
                <th>Judge</th>
                <th>Court Decision</th>
                <th>Penalty</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($outcomes as $outcome)
                <tr>
                    <td>{{ $outcome->Outcome }}</td>
                    <td>{{ \Carbon\Carbon::parse($outcome->JudgmentDate)->format('d M Y') }}</td>
                    <td>{{ $outcome->JudgeName }}</td>
                    <td>{{ Str::limit($outcome->CourtDecision, 40) }}</td>
                    <td>{{ number_format($outcome->PenaltyAmount, 2) }}</td>
                    <td>
                        <a href="{{ route('legal.disputes.outcomes.show', [$case->ID, $outcome->ID]) }}"
                           class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('legal.disputes.outcomes.edit', [$case->ID, $outcome->ID]) }}"
                           class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No outcome recorded yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
