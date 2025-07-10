@extends('layouts.app')
@section('title', 'Prequalification Periods')
@section('content')

    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>📋 Prequalification Periods</span>
            <a href="{{ route('preqrounds.create') }}" class="btn btn-sm btn-light">➕ New Period</a>
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Round Name</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Description/Note</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($periods as $index => $period)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $period->Title }}</td>
                        <td>{{ \Carbon\Carbon::parse($period->StartDate)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($period->EndDate)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $period->Status->badgeColor() }}">
                                {{ $period->Status->label() }}
                            </span>
                        </td>
                        <td>{{ $period->Description}}</td>
                        <td>
                            <a href="{{ route('preqrounds.edit', ['Id' => $period->Id]) }}"
                               class="btn btn-sm btn-primary">Edit</a>

                            <a href="{{ route('preqrounds.show', $period->Id) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No prequalification periods found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
