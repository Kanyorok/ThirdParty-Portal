
@extends('layouts.app')
@section('title', 'Review Proposal')

@section('content')
<div class="container mt-4">
    <h4>Review Proposal – Policy ID #{{ $policy->Id }}</h4>

    <div class="mb-4">
        <strong>Customer:</strong> {{ $policy->customer->FullName }}<br>
        <strong>Product:</strong> {{ $policy->product->Name }}<br>
        <strong>Sum Assured:</strong> {{ number_format($policy->SumAssured, 2) }}<br>
        <strong>Premium:</strong> {{ number_format($policy->PremiumAmount, 2) }}<br>
        <td>
            <span class="badge bg-{{ $policy->Status->badgeColor() }}">
                {{ $policy->Status->label() }}
            </span>
            </td>
        </div>
               <div class="card-footer" id="ticketsAttachementContents">
                    @foreach($policy->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                        {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                    @endforeach
                </div>        

        <form action="{{ route('bancassurance.policies.submitUnderwriting', $policy->Id) }}" method="POST"
              enctype="multipart/form-data">
            @csrf

        <div class="mb-3">
            <label class="form-label">Upload Proposal Documents (PDF, Images, etc.)</label>
            <input type="file" name="file[]" class="form-control" multiple required>
        </div>

        <div class="text-end">
            <button class="btn btn-primary">Submit to Underwriter</button>
        </div>
    </form>
</div>
@endsection
@section('scripts')
 @include('snippets.actions.preview-files')
@endsection   

