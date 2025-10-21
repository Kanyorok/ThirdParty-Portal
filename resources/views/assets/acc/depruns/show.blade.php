@extends('layouts.app')
@section('title','Dep Run • '.$run->Id)
@section('content')
<div class="container my-3">
  @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
  <div class="card shadow-sm mb-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted">Run Details</h6>
      <div>
        @if($run->Status==='Draft')
          <form method="post" action="{{ route('assets.acc.depruns.post',$run->Id) }}" class="d-inline">@csrf
            <button class="btn btn-primary btn-sm">Post Run</button>
          </form>
        @endif
        <a href="{{ route('assets.acc.depruns.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
      </div>
    </div>
    <div class="card-body p-3">
      <div class="row g-3">
        <div class="col-md-3"><strong>Book</strong><div>{{ $run->BookID }}</div></div>
        <div class="col-md-3"><strong>Period</strong><div>{{ $run->PeriodStart }} → {{ $run->PeriodEnd }}</div></div>
        <div class="col-md-3"><strong>Status</strong><div>{{ $run->Status }}</div></div>
      </div>
    </div>
  </div>
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Lines</h6></div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Asset</th><th>Method</th><th>Opening</th><th>Months</th><th>Dep</th><th>Closing</th></tr></thead>
          <tbody>
            @foreach($lines as $i => $l)
              <tr>
                <td>{{ $lines->firstItem()+$i }}</td>
                <td>{{ $l->AssetID }}</td>
                <td>{{ $l->MethodUsed }}</td>
                <td>{{ number_format($l->OpeningNBV,2) }}</td>
                <td>{{ $l->Months }}</td>
                <td>{{ number_format($l->DepAmount,2) }}</td>
                <td>{{ number_format($l->ClosingNBV,2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{ $lines->links() }}
    </div>
  </div>
</div>
@endsection
