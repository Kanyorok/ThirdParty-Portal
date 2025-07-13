@extends('layouts.app')
@section('title', 'Budget Activity Master')

@section('content')
    <div class="card p-4">
        <div class="card-header bg-dark text-white py-4 mb-0" style="font-size: 20px; font-weight: bold;">
            📊 Budget Activity Master
        </div>

        <div class="card-body mb-0">
            <p class="text-muted mb-2">
                Manage your budget activities here. You can add, edit, or delete activities that are essential for
                budgeting. Each activity is linked to a budget line.
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
                        <th style="white-space: nowrap;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($activities->count())
                        @foreach($activities as $index => $activity)
                            <tr>
                                <td>{{ $activities->firstItem() + $index }}</td>
                                <td>{{ $activity->ActivityName ?? '-' }}</td>
                                <td>{{ $activity->budgetLine->LineName ?? '-' }}</td>
                                <td style="white-space: normal; word-break: break-word;">
                                    {{ $activity->Description ?? '-' }}
                                </td>
                                <td>
                                    @if($activity->IsActive)
                                        <span class="text-success" value="1">✅ Active</span>
                                    @else
                                        <span class="text-danger" value="0">🚫 Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('activitymaster.edit', $activity->Id) }}"
                                           class="btn btn-sm btn-warning">✏️</a>
                                        {{-- <form action="{{ route('activitymaster.destroy', $activity->Id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this activity?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                🗑️
                                            </button>
                                        </form> --}}
                                        <button type="button"
                                                class="btn btn-sm btn-danger custom-delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="{{ $activity->ActivityName  }}" {{-- Pass item name --}}
                                                data-route="{{route('activitymaster.destroy', $activity->Id)}}"> {{--Pass delete route--}}
                                            🗑️
                                        </button>
                                    </div>
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
                {{-- ----Pagination---- --}}
                <div class="mt-3">
                    {{ $activities->links() }}
                </div>

            </div>
        </div>
    </div>
    @include('components.modals.delete-confirm')
@endsection
