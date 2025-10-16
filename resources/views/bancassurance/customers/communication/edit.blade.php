@extends('layouts.app')
@section('title', 'Edit Communication Log')

@section('content')
    <div class="container mt-4">
        <form method="POST" action="{{ route('bancassurance.customers.communication.update', $log->Id) }}">
            @csrf
            @method('PUT')

            <div class="col-md-4">
                <label class="form-label">Customer</label>
                <input type="text" class="form-control" value="{{ $log->customers->thirdParty->ThirdPartyName }}"
                       disabled>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Contact Date</label>
                    <input type="datetime-local" name="ContactDate" class="form-control"
                           value="{{ \Carbon\Carbon::parse($log->ContactDate)->format('Y-m-d\TH:i') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Contact Type</label>
                    <select name="ContactType" class="form-select" required>
                        <option value="">--Select Contact Type--</option>
                        @foreach ($contacttypes as $contacttype)
                            <option value="{{ $contacttype->ID }}"
                                {{ $log->ContactType == $contacttype->ID ? 'selected' : '' }}>
                                {{ $contacttype->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Handled By</label>
                    <select name="HandledBy" class="form-select">
                        <option value="">-- Select Handled By --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->Id }}"
                                {{ $log->HandledBy == $emp->Id ? 'selected' : '' }}>
                                {{ $emp->FirstName }} {{ $emp->LastName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Summary</label>
                <input type="text" name="Summary" class="form-control"
                       value="{{ old('Summary', $log->Summary) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Detailed Notes</label>
                <textarea name="Notes" class="form-control" rows="4">{{ old('Notes', $log->Notes) }}</textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success">Update Log</button>
            </div>
        </form>
    </div>
@endsection
