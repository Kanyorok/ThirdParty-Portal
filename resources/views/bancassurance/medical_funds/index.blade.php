@extends('layouts.app')

@section('title', 'Medical Funds')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-end align-items-center mb-3 gap-2">
        <div class="d-flex gap-2">
            <a href="{{ route('bancassurance.medicalfunds.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Fund
            </a>
        </div>
    </div>

    @if(session('success')) 
        <div class="alert alert-success">{{ session('success') }}</div> 
    @endif

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
                            <option value="{{ $p->Id }}" @selected(request('provider_id')==$p->Id)>{{ $p->Name ?? '-'}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Coverage Type</label>
                    <select name="coverage_type" class="form-select">
                        <option value="">All</option>
                        @foreach ($coverages as $ct)
                            <option value="{{ $ct->ID }}" @selected(request('coverage_type')==$ct->ID)>{{ $ct->Description ?? '-'}}</option>
                        @endforeach
                    </select>
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
        @foreach ([
            ['label' => 'Total Funds', 'value' => number_format($totals['count'] ?? 0)],
            ['label' => 'Active Funds', 'value' => number_format($totals['active'] ?? 0)],
            ['label' => 'Sum Coverage Limits', 'value' => number_format((float)($totals['coverage_sum'] ?? 0),2)],
            ['label' => 'Avg Coverage Limit', 'value' => number_format((float)($totals['avg_cov_limit'] ?? 0),2)]
        ] as $stat)
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">{{ $stat['label'] }}</div>
                        <div class="fs-5 fw-semibold">{{ $stat['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
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
                                <th class="text-center">Active</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($funds as $i => $f)
                            <tr>
                                <td>{{ $funds->firstItem() + $i }}</td>
                                <td class="fw-semibold">{{ $f->FundName ?? '-' }}</td>
                                <td>{{ optional($f->provider)->Name ?? '—' }}</td>
                                <td>{{ $f->coverages->Description ?? '—' }}</td>
                                <td class="text-end">{{ number_format((float)($f->CoverageLimit ?? 0),2) }}</td>
                                <td class="text-center">
                                    @if($f->IsActive)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                            <i class="fas fa-check-circle me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill">
                                            <i class="fas fa-times-circle me-1"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>{{ optional($f->CreatedOn)->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('bancassurance.medicalfunds.show', ['medical_fund' => $f->Id]) }}"
                                           class="btn btn-sm btn-info">
                                            <i class="bi bi-eye me-1"></i>Open
                                        </a>

                                        <a href="{{ route('bancassurance.medicalfunds.edit', ['medical_fund' => $f->Id]) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>

                                        <form action="{{ route('bancassurance.medicalfunds.destroy', ['medical_fund' => $f->Id]) }}"
                                              method="POST"
                                              onsubmit="return confirm('Archive this fund?');"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" type="submit">
                                                <i class="bi bi-archive me-1"></i>Archive
                                            </button>
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
