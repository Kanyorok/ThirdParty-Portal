@extends('layouts.app')
@section('title', 'Create Activity')

@section('content')
    <div class="card p-2">
        {{--        <div class="d-flex justify-content-between align-items-center mb-3">--}}
        {{--            <h5>📝 Create Activity</h5>--}}
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

        <form action="{{ route('activitymaster.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="BudgetLineId" class="form-label">Budget Line</label>
                <select name="BudgetLineID" id="BudgetLineID" class="form-select" required>
                    <option value="">Select Budget Line</option>
                    @foreach($lines as $line)
                        <option value="{{ $line->Id }}" {{ old('BudgetLineId') == $line->Id ? 'selected' : '' }}>
                            {{ $line->LineName }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- <div class="mb-3">
                <label for="ActivityCode" class="form-label">Activity Code</label>
                <input type="text" name="ActivityCode" id="ActivityCode" class="form-control" value="{{ old('ActivityCode') }}" required>
            </div> --}}

            <div class="mb-3">
                <label for="ActivityName" class="form-label">Activity Name</label>
                <input type="text" name="ActivityName" id="ActivityName" class="form-control"
                       value="{{ old('ActivityName') }}" required>
            </div>

            <div class="mb-3">
                <label for="Description" class="form-label">Description</label>
                <textarea name="Description" id="Description" class="form-control" rows="4"
                          required>{{ old('Description') }}</textarea>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="IsActive" id="IsActive"
                       class="form-check-input" {{ old('IsActive', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="IsActive">Is Active</label>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-success"
                        onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">💾 Save Activity
                </button>
                <button type="reset" class="btn btn-secondary">🔄 Reset Form</button>
                <a href="{{ route('activitymaster.index') }}" class="btn btn-secondary">← Back to List</a>
            </div>
        </form>
    </div>
@endsection
