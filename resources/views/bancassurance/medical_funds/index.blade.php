@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h4 class="mb-0">Medical Funds</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('bancassurance.medicalfunds.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Fund
            </a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <!-- FILTER BAR -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Fund or Provider">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Provider</label>
                    <select name="provider_id" class="form-select">
                        <option value="">All</option>
                        @foreach($providers as $p)
                            <option value="{{ $p->ID }}" @selected(request('provider_id')==$p->ID)>{{ $p->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Coverage Type</label>
                    <input type="text" name="coverage_type" value="{{ request('coverage_type') }}" class="form-control" placeholder="Inpatient / Outpatient">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Active</label>
                    <select name="active" class="form-select">
                        <option value="">All</option>
                        <option value="1" @selected(request('active')==='1')>Active</option>
                        <option value="0" @selected(request('active')==='0')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Coverage Limit (Min — Max)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="min_limit" value="{{ request('min_limit') }}" class="form-control" placeholder="Min">
                        <span class="input-group-text">—</span>
                        <input type="number" step="0.01" name="max_limit" value="{{ request('max_limit') }}" class="form-control" placeholder="Max">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Created From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Created To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Sort</label>
                    <select name="sort" class="form-select">
                        <option value="created_desc" @selected(($sort ?? '')==='created_desc')>Newest</option>
                        <option value="created_asc"  @selected(($sort ?? '')==='created_asc')>Oldest</option>
                        <option value="name_asc"     @selected(($sort ?? '')==='name_asc')>Name A→Z</option>
                        <option value="name_desc"    @selected(($sort ?? '')==='name_desc')>Name Z→A</option>
                        <option value="limit_asc"    @selected(($sort ?? '')==='limit_asc')>Limit Low→High</option>
                        <option value="limit_desc"   @selected(($sort ?? '')==='limit_desc')>Limit High→Low</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Apply</button>
                    <a href="{{ route('bancassurance.medicalfunds.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @php
      $chips = [];
      foreach (['search'=>'Q','provider_id'=>'Provider','coverage_type'=>'Coverage','active'=>'Active','min_limit'=>'Min','max_limit'=>'Max','from'=>'From','to'=>'To','sort'=>'Sort'] as $k=>$label){
          if(request()->filled($k)) $chips[] = $label.': '.e(request($k));
      }
    @endphp
    @if(count($chips))
      <div class="mb-3">
        @foreach($chips as $c)
            <span class="badge bg-light text-dark border me-1">{{ $c }}</span>
        @endforeach
      </div>
    @endif

    <!-- QUICK STATS -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Funds</div>
                    <div class="fs-5 fw-semibold">{{ number_format($totals['count'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Active Funds</div>
                    <div class="fs-5 fw-semibold">{{ number_format($totals['active'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Sum Coverage Limits</div>
                    <div class="fs-5 fw-semibold">{{ number_format((float)($totals['coverage_sum'] ?? 0),2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Avg Coverage Limit</div>
                    <div class="fs-5 fw-semibold">{{ number_format((float)($totals['avg_cov_limit'] ?? 0),2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="card-body p-0">
            @if($funds->count())
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fund</th>
                                <th>Provider</th>
                                <th>Coverage</th>
                                <th class="text-end">Limit</th>
                                <th>Active</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($funds as $i => $f)
                            <tr>
                                <td>{{ $funds->firstItem() + $i }}</td>
                                <td class="fw-semibold">{{ $f->FundName }}</td>
                                <td>{{ optional($f->provider)->Name ?? '—' }}</td>
                                <td>{{ $f->CoverageType ?? '—' }}</td>
                                <td class="text-end">{{ number_format((float)($f->CoverageLimit ?? 0),2) }}</td>
                                <td>
                                    {!! $f->IsActive
                                        ? '<span class="badge bg-success">Yes</span>'
                                        : '<span class="badge bg-secondary">No</span>' !!}
                                </td>
                                <td>{{ optional($f->CreatedOn)->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('bancassurance.medicalfunds.show', ['medical_fund' => $f->ID]) }}"
                                           class="btn btn-sm btn-outline-info">Open</a>

                                        <a href="{{ route('bancassurance.medicalfunds.edit', ['medical_fund' => $f->ID]) }}"
                                           class="btn btn-sm btn-outline-primary">Edit</a>

                                        <form action="{{ route('bancassurance.medicalfunds.destroy', ['medical_fund' => $f->ID]) }}"
                                              method="POST"
                                              onsubmit="return confirm('Archive this fund?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Archive</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-muted">No medical funds found. Adjust filters or create a new one.</div>
            @endif
        </div>

        @if($funds->hasPages())
            <div class="card-footer">
                {{ $funds->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
