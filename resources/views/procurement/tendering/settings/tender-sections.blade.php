@extends('layouts.app')
@section('title', 'Tender Evaluation Sections Setup')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-clipboard-list"></i> Tender Evaluation Sections Setup</h4>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Tender Selection -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-search"></i> Select Tender to Configure Evaluation Sections</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('tender-sections.show') }}" class="row align-items-end">
                    <div class="col-md-8">
                        <label for="tender" class="form-label fw-bold">Available Tenders:</label>
                        <select name="tender" class="form-select" id="tenderSelect" onchange="this.form.submit()">
                            <option value="">-- Select Tender --</option>
                            @foreach($tenders as $tender)
                                <option value="{{ $tender->Id }}"
                                    {{ request('tender') == $tender->Id ? 'selected' : '' }}>
                                    {{ $tender->TenderNo }} - {{ $tender->Title }}
                                    ({{ ucfirst($tender->Status) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        @if(request('tender'))
                            @php
                                $selectedTender = $tenders->firstWhere('Id', request('tender'));
                                $sectionsCount = $selectedTender?->tenderSections->count() ?? 0;
                                $totalWeight = $selectedTender?->tenderSections->sum('Weight') ?? 0;
                            @endphp
                            <div class="text-muted small">
                                <strong>Status:</strong> {{ $sectionsCount > 0 ? 'Configured' : 'Not Configured' }}<br>
                                @if($sectionsCount > 0)
                                    <strong>Sections:</strong> {{ $sectionsCount }}<br>
                                    <strong>Weight:</strong> {{ $totalWeight }}%
                                    @if(abs($totalWeight - 100) > 0.01)
                                        <span class="text-danger">⚠️ Invalid</span>
                                    @else
                                        <span class="text-success">✅ Valid</span>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Tenders Overview -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Tenders Evaluation Configuration Overview</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Tender Reference</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Sections Configured</th>
                            <th>Total Weight</th>
                            <th>Configuration Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($tenders as $tender)
                            @php
                                $sectionsCount = $tender->tenderSections->count();
                                $totalWeight = $tender->tenderSections->sum('Weight');
                                $isValid = abs($totalWeight - 100) < 0.01;
                                $readiness = $tender->getEvaluationReadiness();
                            @endphp
                            <tr>
                                <td><strong>{{ $tender->TenderNo }}</strong></td>
                                <td>{{ Str::limit($tender->Title, 50) }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $tender->Status === 'Published' ? 'success' : ($tender->Status === 'Draft' ? 'secondary' : 'primary') }}">
                                        {{ ucfirst($tender->Status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($sectionsCount > 0)
                                        <span class="badge bg-info">{{ $sectionsCount }} sections</span>
                                    @else
                                        <span class="text-muted">Not configured</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($sectionsCount > 0)
                                        <span class="badge bg-{{ $isValid ? 'success' : 'danger' }}">
                                            {{ $totalWeight }}%
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sectionsCount === 0)
                                        <span class="badge bg-warning">⚠️ Not Configured</span>
                                    @elseif(!$isValid)
                                        <span class="badge bg-danger">❌ Invalid Weights</span>
                                    @elseif($readiness['ready'])
                                        <span class="badge bg-success">✅ Ready for Evaluation</span>
                                    @else
                                        <span class="badge bg-info">⏳ Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('tender-sections.show') }}?tender={{ $tender->Id }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-cog"></i> Configure
                                        </a>
                                        @if($sectionsCount > 0)
                                            <form method="POST"
                                                  action="{{ route('tender-sections.destroy', $tender->Id) }}"
                                                  style="display: inline-block;"
                                                  onsubmit="return confirm('Remove all evaluation sections from this tender?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i> Reset
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No tenders available for evaluation setup.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Available Sections Overview -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tags"></i> Available Evaluation Sections</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($sections as $section)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">{{ $section->SectionName }}</h6>
                                    <p class="card-text small text-muted">
                                        {{ $section->Description ?? 'No description available' }}
                                    </p>
                                    <div class="small">
                                        <strong>Criteria:</strong> {{ $section->criteria->count() }}
                                        @if($section->criteria->count() > 0)
                                            <div class="mt-1">
                                                @foreach($section->criteria->take(3) as $criteria)
                                                    <span
                                                        class="badge bg-light text-dark">{{ $criteria->CriteriaName }}</span>
                                                @endforeach
                                                @if($section->criteria->count() > 3)
                                                    <span
                                                        class="text-muted">+{{ $section->criteria->count() - 3 }} more</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($sections->isEmpty())
                    <div class="text-center text-muted">
                        <i class="fas fa-info-circle"></i>
                        No evaluation sections available. Please create sections in Settings first.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
