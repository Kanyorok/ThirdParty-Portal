@extends('layouts.app')
@section('title', 'Legal Case Registry')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>⚖️ Legal Case Registry</h4>
        <a href="{{ route('legal.cases.create') }}" class="btn btn-primary">➕ New Legal Case</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Case Title</th>
                <th>Court</th>
                <th>Filing Date</th>
                <th>Opposing Party</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <tr>
        <td>ABC Corp vs XYZ Ltd</td>
        <td>High Court</td>
        <td>2023-04-15</td>
        <td>XYZ Ltd</td>
        <td>Ongoing</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>John Doe vs Jane Smith</td>
        <td>Supreme Court</td>
        <td>2022-11-10</td>
        <td>Jane Smith</td>
        <td>Closed</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>County of Nairobi vs Alpha Contractors</td>
        <td>Commercial Court</td>
        <td>2024-02-28</td>
        <td>Alpha Contractors</td>
        <td>Pending</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Mary Wanjiku vs Green Estates Ltd</td>
        <td>Environment & Land Court</td>
        <td>2023-07-19</td>
        <td>Green Estates Ltd</td>
        <td>Ongoing</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Peter Kamau vs National Transport Authority</td>
        <td>Administrative Court</td>
        <td>2024-05-09</td>
        <td>National Transport Authority</td>
        <td>Appeal Filed</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>

            {{-- @forelse($cases as $case)
                <tr>
                    <td>{{ $case->CaseTitle }}</td>
                    <td>{{ $case->CourtName }}</td>
                    <td>{{ \Carbon\Carbon::parse($case->FilingDate)->format('d M Y') }}</td>
                    <td>{{ $case->OpposingParty }}</td>
                    <td>{{ $case->Status }}</td>
                    <td>
                        <a href="{{ route('legal.cases.show', $case->ID) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('legal.cases.edit', $case->ID) }}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="{{ route('legal.cases.evidence.index', $case->ID) }}" class="btn btn-sm btn-dark">📂 Evidence</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No cases found.</td></tr>
            @endforelse --}}
        </tbody>
    </table>
</div>
@endsection
