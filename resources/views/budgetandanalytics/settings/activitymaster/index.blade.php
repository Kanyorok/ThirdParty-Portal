@extends('layouts.app')
@section('title', 'Budget Activities')

@section('content')
<div class="card p-4">
    <div class="card-header bg-dark text-white py-4 mb-0" style="font-size: 20px; font-weight: bold;">
        📊 Budget Activities
    </div>

<div class="card-body mb-0">
    <p class="text-muted mb-2">
        Manage your budget activities here. You can add, edit, or delete activities that are essential for budgeting. Each activity is linked to a budget line.
    </p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('activitymaster.create') }}" class="btn btn-primary mb-3">➕ Add New Activity</a>

    <!-- WRAP TABLE -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th style="white-space: nowrap;">#</th>
                    <th style="white-space: nowrap;">Activity Name</th>
                    <th style="white-space: nowrap;">Budget Line</th>
                    <th style="min-width: 300px; white-space: normal;">Description</th>
                    <th style="white-space: nowrap;">Status</th>
                </tr>
            </thead>
            <tbody>
                @if($activities->count())
                    @foreach($activities as $activity)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $activity->ActivityName ?? '-' }}</td>
                            <td>{{ $activity->budgetLine->LineName ?? '-' }}</td>
                            <td style="white-space: normal; word-break: break-word;">
                                {{ $activity->Description ?? '-' }}
                            </td>
                            <td>
                                @if($activity->IsActive)
                                    <span class="text-success">✅ Active</span>
                                @else
                                    <span class="text-danger">🚫 Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center text-muted">No budget activities found.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>


</div>
@endsection
