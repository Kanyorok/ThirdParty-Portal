@extends('layouts.app')
@section('title', 'Prequalification Evaluation Overview')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
        <span>📋 Prequalification Evaluation Setup</span>
        <a href="{{ route('preqcriteria.create') }}" class="btn btn-light btn-sm fw-bold">
            ➕ Create Setup
        </a>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table id="section" class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Round Title</th>
                    <th>Evaluation Section</th>
                    <th>Section Weight %</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sections as $section)
                    <tr>
                        <td>{{ $section->round->Title ?? 'N/A' }}</td>
                        <td>{{ $section->section->SectionName ?? 'N/A' }}</td>
                        <td>{{ $section->Weight ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $section->round->Status == 'Open' ? 'success' : 'secondary' }}">
                                {{ $section->round->Status->Label() ?? $section->round->Status }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('getCriteriabySection', [
                                'round_id' => $section->RoundId,
                                'section_id' => $section->SectionId
                            ]) }}" class="btn btn-sm btn-outline-primary">
                                 View Criteria
                            </a>

                            <a href="{{ route('preqcriteria.edit', $section->RoundId) }}"
                               class="btn btn-sm btn-outline-warning">
                               Edit All
                            </a>

                            <form action="{{ route('preqcriteria.destroy', $section->Id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('This will delete all sections related to the Round Title: {{ $section->round->Title ?? 'N/A' }}.\n\nAre you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    Delete Round
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No evaluation sections found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#section').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
