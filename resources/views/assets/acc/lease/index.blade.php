{{-- resources/views/assets/acc/lease/index.blade.php --}}
@extends('layouts.app')
@section('title','Lease Assets')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted">IFRS 16 Lease Assets</h6>
      <a href="{{ route('assets.acc.lease.create') }}" class="btn btn-primary btn-sm">New</a>
    </div>
    <div class="card-body p-3">
      @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light">
            <tr>
              <th>#</th><th>Lease Code</th><th>Asset</th><th>Book</th>
              <th>Commencement</th><th>Term (m)</th><th class="text-end">Payment</th>
              <th>Freq</th><th>Disc. Rate p.a.</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $i => $r)
              <tr>
                <td>{{ $rows->firstItem()+$i }}</td>
                <td>{{ $r->LeaseCode }}</td>
                <td>{{ $r->AssetID }}</td>
                <td>{{ $r->BookID }}</td>
                <td>{{ $r->Commencement }}</td>
                <td>{{ $r->TermMonths }}</td>
                <td class="text-end">{{ number_format($r->PaymentAmount,2) }}</td>
                <td>{{ $r->PaymentFreq }}</td>
                <td>{{ rtrim(rtrim(number_format($r->DiscountRatePA*100,4), '0'),'.') }}%</td>
                <td>{{ $r->Status }}</td>
              </tr>
            @empty
              <tr><td colspan="10" class="text-center text-muted">No leases captured.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $rows->links() }}
    </div>
  </div>
</div>
@endsection
