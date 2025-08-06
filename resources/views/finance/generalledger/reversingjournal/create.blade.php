@extends('layouts.app')
@section('title', 'Reverse Journal Entry')

@section('content')
    <div class="container mt-5">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow border-0">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Reverse Journal Entry</h5>
            </div>

            <div class="card-body">
                <form method="POST" action="{{route('reversingjournal.store')}}">
                    @csrf
                    @method('POST')
                    <div class="row g-4 mb-3">
                        <div class="col-md-4">
                            <label for="OriginalReferenceNumber" class="form-label">Original Journal Ref #</label>
                            <select name="OriginalJournalID" id="OriginalReferenceNumber" class="form-select" required>
                                <option value="" disabled selected>-- Select Journal Ref --</option>
                                @foreach ($journalEntries as $entry)
                                    <option value="{{ $entry->Id }}">{{ $entry->RefNo }} - {{ $entry->Description ?? 'No Description' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="ReversalDate" class="form-label">Reversal Date</label>
                            <input type="date" name="ReversalDate" id="ReversalDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label for="Reason" class="form-label">Reason</label>
                            <input type="text" name="Reason" id="Reason" class="form-control" placeholder="e.g., Accrual reversal" required>
                        </div>
                    </div>

                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        This action will automatically reverse the original journal entry lines.
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{route('reversingjournal.index')}}" class="btn btn-secondary">Back</a>
                        <button class="btn btn-danger" id="saveBtn" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Reversing...'; this.form.submit();}">Reverse Journal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
