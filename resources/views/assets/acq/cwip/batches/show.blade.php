@extends('layouts.app')
@section('title','Batch • '.$batch->BatchNo)

@section('content')
<div class="container my-3">
  @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif

  <div class="card shadow-sm mb-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted">Batch</h6>
      <div>
        @if($batch->Status==='Draft')
          <form method="post" action="{{ route('assets.acq.wizard.post',$batch->Id) }}" class="d-inline">
            @csrf <button class="btn btn-primary btn-sm">Post Batch</button>
          </form>
        @endif
        <a href="{{ route('assets.acq.cap-batches.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
      </div>
    </div>
    <div class="card-body p-3">
      <div class="row g-3">
        <div class="col-md-3"><strong>Batch No</strong><div>{{ $batch->BatchNo }}</div></div>
        <div class="col-md-3"><strong>Date</strong><div>{{ $batch->BatchDate }}</div></div>
        <div class="col-md-3"><strong>Mode</strong><div>{{ $batch->Mode }}</div></div>
        <div class="col-md-3"><strong>Status</strong><div>{{ $batch->Status }}</div></div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Lines</h6></div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Source</th><th>CWIP</th><th>Asset</th><th>Class</th><th>Book</th><th>Amount</th><th>Dep Start</th></tr></thead>
          <tbody>
            @foreach($lines as $i => $l)
            <tr>
              <td>{{ $i+1 }}</td>
              <td>{{ $l->SourceType }}</td>
              <td>{{ $l->CWIPLineID ?? '—' }}</td>
              <td>{{ $l->AssetName }} ({{ $l->AssetCode ?? 'new' }})</td>
              <td>{{ $l->ClassID }}</td>
              <td>{{ $l->BookID }}</td>
              <td>{{ number_format($l->CapitalizeAmt,2) }}</td>
              <td>{{ $l->DepStartDate ?? '—' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
