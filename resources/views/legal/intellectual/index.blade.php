@extends('layouts.app')
@section('title', 'IP & Trademark Registry')

@section('content')
<div class="card shadow p-4 rounded-4">
    <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-info mb-0"><i class="fas fa-brain"></i> IP & Trademark Registry</h4>
        <a href="{{ route('legal.intellectual.create') }}" class="btn btn-info">
            <i class="fas fa-plus me-1"></i> Register IP
        </a>
    </div>

    <div class="card-body">
        <p class="text-muted">View and manage registered intellectual property and trademarks.</p>
        <table class="table table-hover table-sm align-middle text-center"
            style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <thead>
                <tr>
                    <th>IP Type</th>
                    <th>Title</th>
                    <th>Owner</th>
                    <th>Status</th>
                    <th>Registration No.</th>
                    <th>Expiry Date</th>
                    <th>Is Disputed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($records->count())
                    @foreach ($records as $record)
                        <tr>
                            <td>{{ $record->IPType }}</td>
                            <td>{{ $record->Title }}</td>
                            <td>{{ $record->Owner }}</td>
                            <td>{{ $record->Status }}</td>
                            <td>{{ $record->RegistrationNumber }}</td>
                            <td>{{ $record->ExpiryDate }}</td>
                            <td>
                                @if($record->IsDisputed)
                                    <span class="badge bg-danger">Yes</span>
                                @else
                                    <span class="badge bg-success">No</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('legal.intellectual.show', $record->Id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('legal.intellectual.edit', $record->Id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-danger custom-delete-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#customDeleteConfirmModal"
                                    data-name="{{ $record->IPType }}: {{ $record->Title }}"    
                                    data-route="{{ route('legal.intellectual.destroy', $record->Id) }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <button type="button" 
                                    class="btn btn-sm btn-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#disputedModal-{{ $record->Id }}">
                                    <i class="fas fa-hand-paper"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="p-0">
                            <div class="text-center p-4 border rounded-3 bg-light">
                                <p class="mb-3 text-muted fs-5">
                                    <i class="fas fa-info-circle me-2 text-info"></i>
                                    <i>No records found.</i>
                                </p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Dispute Modals --}}
    @foreach($records as $record)
        {{-- Disputed Modal for this record --}}
    <form method="POST" action="{{ route('legal.intellectual.raiseDispute', $record->Id) }}">
        @csrf
        @method('PATCH')
        <div class="modal fade" id="disputedModal-{{ $record->Id }}" tabindex="-1" aria-labelledby="disputedModalLabel-{{ $record->Id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="disputedModalLabel-{{ $record->Id }}">
                            {{ $record->IsDisputed ? 'Update Dispute' : 'Dispute Intellectual Property' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="DisputeReason-{{ $record->Id }}" class="form-label">Dispute Reason</label>
                            <textarea 
                                class="form-control" 
                                id="DisputeReason-{{ $record->Id }}" 
                                name="DisputeReason" 
                                rows="3" 
                                required>{{ old('DisputeReason', $record->DisputeReason) }}</textarea>
                        </div>
                        <div class="mb-3">
                            {{-- hidden input ensures unchecked checkboxes still send value --}}
                            <input type="hidden" name="IsDisputed" value="0">
                            <input 
                                type="checkbox" 
                                id="IsDisputed-{{ $record->Id }}" 
                                name="IsDisputed" 
                                value="1"
                                {{ old('IsDisputed', $record->IsDisputed) ? 'checked' : '' }}>
                            <label for="IsDisputed-{{ $record->Id }}" class="form-label">Mark as Disputed</label>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">
                            {{ $record->IsDisputed ? 'Update Dispute' : 'Submit Dispute' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endforeach

</div>

@include('components.modals.delete-confirm')
@endsection
