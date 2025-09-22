@extends('layouts.app')
@section('title', ' Edit Activity')

@section('content')

    <div class="card p-2">
        {{--        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center mb-3">--}}
        {{--            📝 Edit Activity--}}
        {{--        </div>--}}

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-body">
            <p class="text-muted">
                Use this form to edit the details of the activity. Ensure that all fields are filled out correctly,
                especially the budget line and activity name.
                You can also toggle the active status of the activity.
            </p>
            <form action="{{ route('activitymaster.update', $activity->Id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="BudgetLineID" class="form-label">Budget Line</label>
                    <select name="BudgetLineID" id="BudgetLineID" class="form-select" required>
                        <option value="">Select Budget Line</option>
                        @foreach($lines as $line)
                            <option
                                value="{{ $line->Id }}" {{ old('BudgetLineID', $activity->BudgetLineID) == $line->Id ? 'selected' : '' }}>
                                {{ $line->LineName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="ActivityName" class="form-label">Activity Name</label>
                    <input type="text" name="ActivityName" id="ActivityName" class="form-control"
                           value="{{ old('ActivityName', $activity->ActivityName) }}" required>
                </div>

                <div class="mb-3">
                    <label for="Description" class="form-label">Description</label>
                    <textarea name="Description" id="Description" class="form-control" rows="4"
                              required>{{ old('Description', $activity->Description) }}</textarea>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input"
                           value="0" {{ old('IsActive', $activity->IsActive) ? 'checked' : '' }}>
                    <label class="form-check-label" for="IsActive">Is Active</label>
                </div>

                <div class="justify-content-between align-items-center d-flex mb-3">
                    <a href="{{ route('activitymaster.index') }}" class="btn btn-secondary">← Back to List</a>
                    <button type="submit" class="btn btn-success"
                            onclick="$this.disabled=true; $this.innerText='Updating...'; $this.form.submit();"> 🔄 Update
                        Activity
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
