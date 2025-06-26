@extends('layouts.app')
@section('title', 'Patent Details')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Patent Details</h3>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Tenant</dt>
                    <dd class="col-sm-8">{{ $clearancetenant->Tenant }}</dd>

                    <dt class="col-sm-4">Exit Date</dt>
                    <dd class="col-sm-8">{{ $clearancetenant->ExitDate ?? '-' }}</dd>

                    <dt class="col-sm-4">Final Inspection done</dt>
                    <dd class="col-sm-8">{{ $clearancetenant->FinalInspection ?? '-' }}</dd>

                    <dt class="col-sm-4">Dues Cleared</dt>
                    <dd class="col-sm-8">{{ $clearancetenant->AllDuesPaid ?? '-' }}</dd>

                    <dt class="col-sm-4">Keys Returned</dt>
                    <dd class="col-sm-8">{{ $clearancetenant->KeysReturned ?? '-' }}</dd>

                    <dt class="col-sm-4">Additional Notes</dt>
                    <dd class="col-sm-8">{{ $clearancetenant->AdditionalNotes ?? '-' }}</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">    <span class="badge bg-{{ $clearancetenant->Status->badgeColor() }}">{{ $clearancetenant->Status->label() }}</span></dd>

                </dl>
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-primary">Edit</a>
                <a href="#" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
