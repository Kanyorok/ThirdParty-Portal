@extends('layouts.app')

@section('title', 'Employee Response')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Employee Response</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.discipline.cases.show', $case->Id) }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @if($response)
                <div class="alert alert-info">
                    Response submitted on {{ $response->SubmittedOn?->format('Y-m-d H:i') }}.
                </div>
            @endif
            @if($notice)
                <div class="alert alert-secondary">
                    <div><strong>Notice:</strong> {{ $notice->NoticeType }} | Issued {{ $notice->IssuedOn?->format('Y-m-d') ?? '-' }}</div>
                    <div><strong>Response Due:</strong> {{ $notice->ResponseDueOn?->format('Y-m-d') ?? '-' }}</div>
                    @if($notice->document)
                        <div><a href="{{ route('file.preview', ['document' => $notice->document->DocumentId]) }}" target="_blank">View notice document</a></div>
                    @endif
                </div>
            @else
                <div class="alert alert-warning">
                    No show cause notice has been issued for this case yet.
                </div>
            @endif
            <form action="{{ route('hr.discipline.cases.response.store', $case->Id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Notice *</label>
                        <select name="NoticeID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($notices ?? [] as $item)
                                <option value="{{ $item->Id }}" @selected(old('NoticeID', $notice?->Id) == $item->Id)>
                                    {{ $item->NoticeType }} | {{ $item->IssuedOn?->format('Y-m-d') ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Response Text</label>
                        <textarea name="ResponseText" class="form-control" rows="5">{{ old('ResponseText') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Attachment</label>
                        <input type="file" name="ResponseDocument" class="form-control">
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.discipline.cases.show', $case->Id) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Response</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
