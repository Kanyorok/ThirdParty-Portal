@extends('layouts.app')
@section('title','Asset • '.$asset->AssetCode)

@section('content')
<div class="container my-3">
  @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif

  <div class="card shadow-sm rounded-3 mb-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="fas fa-box me-2"></i> Profile</h6>
      <div>
        <a href="{{ route('assets.master.register.edit',$asset->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
        <a href="{{ route('assets.master.register.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
      </div>
    </div>
    <div class="card-body p-3">
      <div class="row g-3">
        <div class="col-md-3"><strong>Code</strong><div>{{ $asset->AssetCode }}</div></div>
        <div class="col-md-5"><strong>Name</strong><div>{{ $asset->AssetName }}</div></div>
        <div class="col-md-2"><strong>Status</strong><div>{{ $asset->Status }}</div></div>
        <div class="col-md-2"><strong>Class</strong><div>{{ $asset->ClassID }}</div></div>
        <div class="col-md-3"><strong>Location</strong><div>{{ $asset->LocationID }}</div></div>
        <div class="col-md-3"><strong>Serial</strong><div>{{ $asset->SerialNumber }}</div></div>
        <div class="col-md-3"><strong>Manufacturer</strong><div>{{ $asset->Manufacturer }}</div></div>
        <div class="col-md-3"><strong>Model</strong><div>{{ $asset->Model }}</div></div>
      </div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs" id="assetTabs" role="tablist">
    <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-fin" type="button">Financials</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-comp" type="button">Components</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-met" type="button">Meters & Calibration</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-doc" type="button">Attachments</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-hist" type="button">History</button></li>
  </ul>
  <div class="tab-content border-start border-end border-bottom p-3 rounded-bottom">
    {{-- Financials --}}
    <div class="tab-pane fade show active" id="tab-fin">
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>Book</th><th>Cost</th><th>Method</th><th>Life (m)</th><th>Residual %</th><th>Dep Start</th><th>AccumDep</th><th>NBV</th></tr></thead>
          <tbody>
            @forelse($bookValues as $b)
            <tr>
              <td>{{ $b->BookID }}</td><td>{{ number_format($b->AcquisitionCost,2) }}</td>
              <td>{{ $b->DepMethod ?? '—' }}</td><td>{{ $b->UsefulLifeMonths ?? '—' }}</td>
              <td>{{ $b->ResidualPct !== null ? number_format($b->ResidualPct,2) : '—' }}</td>
              <td>{{ $b->DepStartDate ?? '—' }}</td>
              <td>{{ number_format($b->AccumDep,2) }}</td>
              <td>{{ number_format($b->NBV,2) }}</td>
            </tr>
            @empty <tr><td colspan="8" class="text-center text-muted">No book values.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Components --}}
    <div class="tab-pane fade" id="tab-comp">
      <form class="mb-2" method="post" action="{{ route('assets.master.register.components.store',$asset->Id) }}">
        @csrf
        <div class="row g-2">
          <div class="col-md-4"><input name="ComponentName" class="form-control form-control-sm" placeholder="Component name" required></div>
          <div class="col-md-2"><input name="SerialNumber" class="form-control form-control-sm" placeholder="Serial"></div>
          <div class="col-md-1"><input type="number" name="Quantity" value="1" class="form-control form-control-sm" min="1"></div>
          <div class="col-md-2"><input type="date" name="AcquisitionDate" class="form-control form-control-sm"></div>
          <div class="col-md-2"><input type="number" step="0.01" name="Cost" class="form-control form-control-sm" placeholder="Cost"></div>
          <div class="col-md-1 d-flex align-items-center">
            <div class="form-check"><input class="form-check-input" type="checkbox" name="IsCritical" id="ic"><label class="form-check-label" for="ic">Critical</label></div>
          </div>
        </div>
        <div class="mt-2"><button class="btn btn-primary btn-sm">Add Component</button></div>
      </form>
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Name</th><th>Serial</th><th>Qty</th><th>Date</th><th>Cost</th><th>Critical</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            @forelse($components as $i => $c)
            <tr>
              <td>{{ $i+1 }}</td><td>{{ $c->ComponentName }}</td><td>{{ $c->SerialNumber }}</td><td>{{ $c->Quantity }}</td>
              <td>{{ $c->AcquisitionDate ?? '—' }}</td><td>{{ number_format($c->Cost,2) }}</td><td>{{ $c->IsCritical ? 'Yes':'No' }}</td>
              <td class="text-end">
                <form method="post" action="{{ route('assets.master.register.components.destroy',[$asset->Id,$c->Id]) }}" onsubmit="return confirm('Delete?')">
                  @csrf @method('DELETE') <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
            @empty <tr><td colspan="8" class="text-center text-muted">No components.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Meters & Calibration --}}
    <div class="tab-pane fade" id="tab-met">
      <form class="mb-2" method="post" action="{{ route('assets.master.register.meters.store',$asset->Id) }}">
        @csrf
        <div class="row g-2">
          <div class="col-md-3"><input name="MeterName" class="form-control form-control-sm" placeholder="Meter name" required></div>
          <div class="col-md-2"><input name="Unit" class="form-control form-control-sm" placeholder="Unit (km, hr, kWh)" required></div>
          <div class="col-md-2">
            <select name="ReadingType" class="form-select form-select-sm">
              @foreach(['CUMULATIVE','GAUGE'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-2"><input type="number" step="0.01" name="InitialReading" class="form-control form-control-sm" placeholder="Initial" required></div>
          <div class="col-md-2"><button class="btn btn-primary btn-sm">Add Meter</button></div>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Meter</th><th>Unit</th><th>Reading</th><th>Type</th><th>Last Date</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            @forelse($meters as $i => $m)
            <tr>
              <td>{{ $i+1 }}</td><td>{{ $m->MeterName }}</td><td>{{ $m->Unit }}</td><td>{{ $m->CurrentReading }}</td><td>{{ $m->ReadingType }}</td><td>{{ $m->LastReadingDate }}</td>
              <td class="text-end">
                <form method="post" action="{{ route('assets.master.register.meters.destroy',[$asset->Id,$m->Id]) }}" onsubmit="return confirm('Delete meter?')">
                  @csrf @method('DELETE') <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
            @empty <tr><td colspan="7" class="text-center text-muted">No meters.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <hr>
      <h6>Calibration</h6>
      <form class="mb-2" method="post" action="{{ route('assets.master.register.calibrations.store',$asset->Id) }}">
        @csrf
        <div class="row g-2">
          <div class="col-md-3">
            <select name="MeterID" class="form-select form-select-sm">
              <option value="">(no meter)</option>
              @foreach($meters as $m)<option value="{{ $m->Id }}">{{ $m->MeterName }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-3">
            <select name="ProviderID" class="form-select form-select-sm">
              <option value="">Provider</option>
              @foreach($providers as $p)<option value="{{ $p->Id }}">{{ $p->Name }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-2"><input name="CertificateNo" class="form-control form-control-sm" placeholder="Certificate #"></div>
          <div class="col-md-2"><input type="date" name="CalibrationDate" class="form-control form-control-sm" required></div>
          <div class="col-md-2"><input type="date" name="NextDueDate" class="form-control form-control-sm"></div>
          <div class="col-md-2">
            <select name="Result" class="form-select form-select-sm">@foreach(['PASS','FAIL'] as $r)<option value="{{ $r }}">{{ $r }}</option>@endforeach</select>
          </div>
          <div class="col-md-4"><input name="Remarks" class="form-control form-control-sm" placeholder="Remarks"></div>
          <div class="col-md-2"><button class="btn btn-primary btn-sm">Add Calibration</button></div>
        </div>
      </form>
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Date</th><th>Provider</th><th>Cert #</th><th>Meter</th><th>Result</th><th>Next Due</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            @forelse($calibrations as $i => $c)
            <tr>
              <td>{{ $i+1 }}</td><td>{{ $c->CalibrationDate }}</td><td>{{ $c->ProviderID }}</td><td>{{ $c->CertificateNo }}</td>
              <td>{{ $c->MeterID ?? '—' }}</td><td>{{ $c->Result }}</td><td>{{ $c->NextDueDate ?? '—' }}</td>
              <td class="text-end">
                <form method="post" action="{{ route('assets.master.register.calibrations.destroy',[$asset->Id,$c->Id]) }}" onsubmit="return confirm('Delete calibration?')">
                  @csrf @method('DELETE') <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
            @empty <tr><td colspan="8" class="text-center text-muted">No calibrations.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Attachments --}}
    <div class="tab-pane fade" id="tab-doc">
      <form class="mb-2" method="post" action="{{ route('assets.master.register.attachments.store',$asset->Id) }}">
        @csrf
        <div class="row g-2">
          <div class="col-md-2"><input name="DocType" class="form-control form-control-sm" placeholder="Doc Type (Invoice)"></div>
          <div class="col-md-4"><input name="FileName" class="form-control form-control-sm" placeholder="File name"></div>
          <div class="col-md-6"><input name="DMSPath" class="form-control form-control-sm" placeholder="DMS link/path"></div>
        </div>
        <div class="mt-2"><button class="btn btn-primary btn-sm">Add Attachment</button></div>
      </form>
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Type</th><th>File</th><th>DMS Path</th><th>Uploaded</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            @forelse($attachments as $i => $d)
            <tr>
              <td>{{ $i+1 }}</td><td>{{ $d->DocType }}</td><td>{{ $d->FileName }}</td><td>{{ $d->DMSPath }}</td><td>{{ $d->UploadedOn }}</td>
              <td class="text-end">
                <form method="post" action="{{ route('assets.master.register.attachments.destroy',[$asset->Id,$d->Id]) }}" onsubmit="return confirm('Delete attachment?')">
                  @csrf @method('DELETE') <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
            @empty <tr><td colspan="6" class="text-center text-muted">No attachments.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- History --}}
    <div class="tab-pane fade" id="tab-hist">
      <form class="mb-2" method="post" action="{{ route('assets.master.register.history.store',$asset->Id) }}">
        @csrf
        <div class="row g-2">
          <div class="col-md-3"><input name="EventType" class="form-control form-control-sm" placeholder="Event (Edited/Transfer...)" required></div>
          <div class="col-md-2"><input type="date" name="EventDate" class="form-control form-control-sm" required></div>
          <div class="col-md-3"><input name="Reference" class="form-control form-control-sm" placeholder="Reference"></div>
          <div class="col-md-4"><input name="Remarks" class="form-control form-control-sm" placeholder="Remarks"></div>
        </div>
        <div class="mt-2"><button class="btn btn-primary btn-sm">Add Event</button></div>
      </form>
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Date</th><th>Event</th><th>Ref</th><th>Remarks</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            @forelse($history as $i => $h)
            <tr>
              <td>{{ $i+1 }}</td><td>{{ $h->EventDate }}</td><td>{{ $h->EventType }}</td><td>{{ $h->Reference }}</td><td>{{ $h->Remarks }}</td>
              <td class="text-end">
                <form method="post" action="{{ route('assets.master.register.history.destroy',[$asset->Id,$h->Id]) }}" onsubmit="return confirm('Delete event?')">
                  @csrf @method('DELETE') <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
            @empty <tr><td colspan="6" class="text-center text-muted">No history yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection
