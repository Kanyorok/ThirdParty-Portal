@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ $procurement_mode->Name }}</h2>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <hr>

    {{-- Timeline Form --}}
    <h4>Add Timeline</h4>
    <form action="{{ route('timelines.store') }}" method="POST">
        @csrf
        <input type="hidden" name="ProcurementModeId" value="{{ $procurement_mode->Id }}">

        <div class="form-group">
            <label for="Stage">Stage</label>
            <input type="text" class="form-control" name="Stage" required>
        </div>

        <div class="form-group">
            <label for="DurationDays">Duration (Days)</label>
            <input type="number" class="form-control" name="DurationDays" required>
        </div>

        <button type="submit" class="btn btn-primary mt-2">Add Timeline</button>
    </form>

    <hr>

    {{-- List Timelines --}}
    <h4>Timelines</h4>
    @if($procurement_mode->timelines->count())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Stage</th>
                    <th>Duration (Days)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($procurement_mode->timelines as $timeline)
                    <tr>
                        <td>{{ $timeline->Stage }}</td>
                        <td>{{ $timeline->DurationDays }}</td>
                        <td>
                            <form method="POST" action="{{ route('timelines.destroy', $timeline->Id) }}" onsubmit="return confirm('Delete this timeline?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No timelines defined for this mode yet.</p>
    @endif
</div>
@endsection
