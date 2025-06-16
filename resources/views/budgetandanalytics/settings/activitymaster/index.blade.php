@extends('layouts.app')
@section('title', 'Budget Activities')

@section('content')
<div class="card p-4">
    <div class="card-header bg-dark text-white py-4 mb-0" style="font-size: 20px; font-weight: bold;">
        📊 Budget Activities
    </div>

    <div class="card-body mb-0">
         <p class="text-muted mb-2">Manage your budget activities here. You can add, edit, or delete activities that are essential for budgeting and financial planning.</p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('activitymaster.create') }}" class="btn btn-primary mb-3">➕ Add New Activity</a>

        <table class="table table-striped table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Activity Code</th>
                    <th>Activity Name</th>
                    <th>Budget Line</th>
                    <th>Description</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @if($activities->count())
                    @foreach($activities as $activity)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $activity->ActivityCode ?? '-'}}</td>
                            <td>{{ $activity->ActivityName ?? '-'}}</td>
                            <td>{{ $activity->budgetLine->LineName ?? '-' }}</td>
                            <td style="white-space: normal; break-word; max-width=300px">{{ $activity->Description ?? '-'}}</td>
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
@endsection
