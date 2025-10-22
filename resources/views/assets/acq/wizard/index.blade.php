@extends('layouts.app')
@section('title','Capitalization Wizard')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Create Capitalization Batch</h6></div>
    <div class="card-body p-3">
      <form method="post" action="{{ route('assets.acq.wizard.create-batch') }}">
        @csrf
        <div class="row g-2 mb-3">
          <div class="col-md-2">
            <label class="form-label">Mode</label>
            <select name="Mode" class="form-select form-select-sm">@foreach(['SINGLE','PARTIAL','MULTI'] as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach</select>
          </div>
          <div class="col-md-2"><label class="form-label">Batch Date</label><input type="date" name="BatchDate" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required></div>
          <div class="col-md-8"><label class="form-label">Remarks</label><input name="Remarks" class="form-control form-control-sm"></div>
        </div>

        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Select</th><th>CWIP #</th><th>Description</th><th>Total</th><th>Asset Name</th>
                <th>Class</th><th>Location</th><th>Book</th><th>Dep Start</th><th>Cap Amount</th><th>Residual %</th>
              </tr>
            </thead>
            <tbody>
              @forelse($lines as $L)
              <tr>
                <td><input type="checkbox" name="Lines[{{ $L->Id }}][CWIPLineID]" value="{{ $L->Id }}"></td>
                <td>{{ $L->Id }}</td>
                <td>{{ $L->Description }}</td>
                <td>{{ number_format($L->TotalCost,2) }}</td>
                <td><input name="Lines[{{ $L->Id }}][AssetName]" class="form-control form-control-sm" placeholder="New asset name"></td>
                <td>
                  <select name="Lines[{{ $L->Id }}][ClassID]" class="form-select form-select-sm">
                    @foreach($classes as $c)<option value="{{ $c->Id }}" @selected($L->ClassID==$c->Id)>{{ $c->Name }}</option>@endforeach
                  </select>
                </td>
                <td>
                  <select name="Lines[{{ $L->Id }}][LocationID]" class="form-select form-select-sm">
                    <option value="">—</option>
                    @foreach($locations as $loc)<option value="{{ $loc->Id }}" @selected($L->LocationID==$loc->Id)>{{ $loc->Code }} — {{ $loc->Site }}</option>@endforeach
                  </select>
                </td>
                <td>
                  <select name="Lines[{{ $L->Id }}][BookID]" class="form-select form-select-sm">
                    @foreach($books as $b)<option value="{{ $b->Id }}" @selected($L->BookID==$b->Id)>{{ $b->Name }}</option>@endforeach
                  </select>
                </td>
                <td><input type="date" name="Lines[{{ $L->Id }}][DepStartDate]" class="form-control form-control-sm"></td>
                <td><input type="number" step="0.01" name="Lines[{{ $L->Id }}][CapitalizeAmt]" value="{{ $L->TotalCost }}" class="form-control form-control-sm"></td>
                <td><input type="number" step="0.01" name="Lines[{{ $L->Id }}][ResidualPct]" class="form-control form-control-sm"></td>
                <input type="hidden" name="Lines[{{ $L->Id }}][AssetCode]" value="">
                <input type="hidden" name="Lines[{{ $L->Id }}][Notes]" value="">
              </tr>
              @empty <tr><td colspan="11" class="text-center text-muted">No open CWIP lines.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-2">
          <button class="btn btn-primary btn-sm">Create Batch</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
