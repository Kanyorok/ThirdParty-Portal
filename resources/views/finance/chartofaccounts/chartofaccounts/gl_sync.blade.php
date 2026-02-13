@extends('layouts.app')

@section('title', 'GL Sync')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
@endsection

@section('content')
    @php
        $initialSyncId = session('active_sync_id') ?? optional($activeSync)->Id;
        $latestStatus = optional($latestSync)->Status;
        $statusClass = match ($latestStatus) {
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'running', 'pending' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    @endphp

    <div class="container my-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1 text-info"><i class="fas fa-sync-alt me-2"></i>Sync General Ledgers (Nimble)</h4>
                <p class="text-muted mb-0">Pull GL accounts from Nimble using background jobs with pagination support.</p>
            </div>
            <a href="{{ route('chartofaccounts.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(empty($allowThirdPartyPosting))
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fas fa-ban me-2"></i>
                <div>
                    Third-party posting is disabled. Set <code>ALLOW_THIRD_PARTY_FINANCE_POSTING=true</code> in your <code>.env</code> to enable syncing.
                </div>
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted">Synced GL accounts</span>
                            <i class="fas fa-database text-info"></i>
                        </div>
                        <h3 class="fw-bold mb-1">{{ number_format($syncCount ?? 0) }}</h3>
                        <small class="text-muted">Rows in <code>t_FinanceSyncGLAccounts</code></small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted">Last synced at</span>
                            <i class="fas fa-clock text-info"></i>
                        </div>
                        <h6 class="fw-bold mb-1">{{ $lastSyncedAt ? \Carbon\Carbon::parse($lastSyncedAt)->format('M d, Y h:i A') : 'Never' }}</h6>
                        <small class="text-muted">{{ $lastSyncedAt ? \Carbon\Carbon::parse($lastSyncedAt)->diffForHumans() : 'No sync run yet' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted">Latest run status</span>
                            <i class="fas fa-tasks text-info"></i>
                        </div>
                        <h6 class="fw-bold mb-1 text-uppercase">{{ $latestStatus ?? 'none' }}</h6>
                        <span class="badge {{ $statusClass }}">{{ $latestStatus ? ucfirst($latestStatus) : 'No runs yet' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="syncProgressCard"
             class="card shadow-sm mb-3 {{ $initialSyncId ? '' : 'd-none' }}"
             data-sync-id="{{ $initialSyncId }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">Sync Progress</h6>
                    <span id="syncStatusBadge" class="badge bg-warning text-dark">Pending</span>
                </div>
                <div class="small text-muted mb-2" id="syncStatusMessage">Waiting for updates...</div>
                <div class="progress mb-2" style="height: 10px;">
                    <div id="syncProgressBar" class="progress-bar bg-info" style="width: 0%"></div>
                </div>
                <div class="row small text-muted">
                    <div class="col-md-3">Synced: <span id="syncRecordsSynced">0</span></div>
                    <div class="col-md-3">Failed: <span id="syncRecordsFailed">0</span></div>
                    <div class="col-md-3">Total: <span id="syncTotalRecords">0</span></div>
                    <div class="col-md-3">Page: <span id="syncCurrentPage">0</span></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Start Sync</h6>
                        <small class="text-muted">Pulls from Nimble endpoint and processes all pages until completion.</small>
                    </div>
                    <button type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmSyncModal"
                            {{ empty($allowThirdPartyPosting) ? 'disabled' : '' }}>
                        <i class="fas fa-sync-alt me-2"></i>Start Sync
                    </button>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Request body uses <code>BankID</code>, <code>GLAccountTypeID</code>, <code>GLTypeGroupID</code>, <code>Source=NIMBLE</code>, and <code>PageSize</code> (default 1000).
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Synced GL Accounts</h6>
                <small class="text-muted">Showing {{ $syncedGls->count() }} of {{ number_format($syncedGls->total()) }}</small>
            </div>
            <div class="card-body">
                <form id="glSearchForm" method="GET" action="{{ route('chartofaccounts.glsync') }}" class="row g-2 mb-3">
                    <div class="col-md-8">
                        <label for="gl_account" class="form-label small text-muted mb-1">Search GL Account</label>
                        <select id="gl_account" name="gl_account" class="form-select form-select-sm">
                            @if(!empty($selectedGlOption))
                                <option value="{{ $selectedGlOption['id'] }}" selected>{{ $selectedGlOption['text'] }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                        <a href="{{ route('chartofaccounts.glsync') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    </div>
                </form>

                @if($syncedGls->count())
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">#</th>
                                <th>GL Code</th>
                                <th>GL Name</th>
                                <th>Description</th>
                                <th class="text-center" style="width: 120px;">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($syncedGls as $gl)
                                <tr>
                                    <td>{{ $syncedGls->firstItem() + $loop->index }}</td>
                                    <td>{{ $gl->GLCode ?? '-' }}</td>
                                    <td>{{ $gl->GLName ?? '-' }}</td>
                                    <td>{{ $gl->Description ?? '-' }}</td>
                                    <td class="text-center">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary view-gl-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#glDetailsModal"
                                            data-gl-code="{{ $gl->GLCode ?? '' }}"
                                            data-gl-name="{{ $gl->GLName ?? '' }}"
                                            data-description="{{ $gl->Description ?? '' }}"
                                            data-gl-account-type-id="{{ $gl->GLAccountTypeID ?? '' }}"
                                            data-gl-type-group-id-value="{{ $gl->GLTypeGroupIDValue ?? '' }}"
                                            data-gl-sub-account-type-id-value="{{ $gl->GLSubAccountTypeIDValue ?? '' }}"
                                            data-branch-id="{{ $gl->BranchID ?? '' }}"
                                            data-source="{{ $gl->Source ?? '' }}"
                                            data-source-table="{{ $gl->SourceTable ?? '' }}"
                                            data-is-active="{{ (int) (bool) $gl->IsActive }}"
                                            data-created-on="{{ optional($gl->CreatedOn)->toDateTimeString() }}"
                                            data-modified-on="{{ optional($gl->ModifiedOn)->toDateTimeString() }}"
                                        >
                                            <i class="fas fa-eye me-1"></i> View
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-2">
                        {{ $syncedGls->links() }}
                    </div>
                @else
                    <div class="alert alert-secondary mb-0">
                        No synced GL records found yet. Run sync first to populate this table.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmSyncModal" tabindex="-1" aria-labelledby="confirmSyncModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmSyncModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>Confirm Sync
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="startSyncForm" action="{{ route('chartofaccounts.glsync.start') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3">This will queue a background sync and replace data in <code>t_FinanceSyncGLAccounts</code>.</p>
                        <label for="page_size" class="form-label fw-semibold">Page size</label>
                        <select name="page_size" id="page_size" class="form-select">
                            @foreach([50, 100, 500, 1000, 2000, 5000] as $size)
                                <option value="{{ $size }}" {{ $size === 1000 ? 'selected' : '' }}>{{ number_format($size) }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Default is 1000.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button id="startSyncSubmitBtn" type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-1"></i>Yes, Start Sync
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="glDetailsModal" tabindex="-1" aria-labelledby="glDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="glDetailsModalLabel">GL Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>GL Code:</strong> <span id="detailGLCode">-</span></div>
                        <div class="col-md-6"><strong>GL Name:</strong> <span id="detailGLName">-</span></div>
                        <div class="col-md-6"><strong>Account Type:</strong> <span id="detailGLAccountTypeID">-</span></div>
                        <div class="col-md-6"><strong>Type Group:</strong> <span id="detailGLTypeGroupIDValue">-</span></div>
                        <div class="col-md-6"><strong>Sub Account Type:</strong> <span id="detailGLSubAccountTypeIDValue">-</span></div>
                        <div class="col-md-6"><strong>Branch ID:</strong> <span id="detailBranchID">-</span></div>
                        <div class="col-md-6"><strong>Source:</strong> <span id="detailSource">-</span></div>
                        <div class="col-md-6"><strong>Is Active:</strong> <span id="detailIsActive">-</span></div>
                        <div class="col-md-12"><strong>Description:</strong> <span id="detailDescription">-</span></div>
                        <div class="col-md-12"><strong>Source Table:</strong> <span id="detailSourceTable">-</span></div>
                        <div class="col-md-6"><strong>Created On:</strong> <span id="detailCreatedOn">-</span></div>
                        <div class="col-md-6"><strong>Modified On:</strong> <span id="detailModifiedOn">-</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const progressCard = document.getElementById('syncProgressCard');
            const progressBaseUrl = @json(url('/finance/chartofaccounts/gl-sync/progress'));
            const glSearchSelect = document.getElementById('gl_account');
            const glSearchForm = document.getElementById('glSearchForm');

            if (window.jQuery && $.fn.select2 && glSearchSelect) {
                $('#gl_account').select2({
                    placeholder: 'Search by GL code or name',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: @json(route('chartofaccounts.glsync.search')),
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { q: params.term || '' };
                        },
                        processResults: function (data) {
                            return { results: data.results || [] };
                        },
                        cache: true,
                    },
                    templateResult: function (data) {
                        if (!data.id) return data.text;
                        const code = data.code || data.id || '';
                        const name = data.name || '';
                        return $('<div><strong>' + code + '</strong> <small class="text-muted">(' + name + ')</small></div>');
                    },
                    templateSelection: function (data) {
                        if (!data.id) return data.text;
                        const code = data.code || data.id || '';
                        const name = data.name || '';
                        return code && name ? (code + ' (' + name + ')') : (data.text || code);
                    }
                });

                $('#gl_account').on('select2:select', function () {
                    if (glSearchForm) glSearchForm.submit();
                });

                $('#gl_account').on('select2:clear', function () {
                    if (glSearchForm) glSearchForm.submit();
                });
            }

            if (!progressCard) {
                return;
            }

            let syncId = progressCard.dataset.syncId || null;
            let timer = null;

            const statusBadge = document.getElementById('syncStatusBadge');
            const statusMessage = document.getElementById('syncStatusMessage');
            const progressBar = document.getElementById('syncProgressBar');
            const recordsSynced = document.getElementById('syncRecordsSynced');
            const recordsFailed = document.getElementById('syncRecordsFailed');
            const totalRecords = document.getElementById('syncTotalRecords');
            const currentPage = document.getElementById('syncCurrentPage');
            const startSyncForm = document.getElementById('startSyncForm');
            const startSyncSubmitBtn = document.getElementById('startSyncSubmitBtn');
            const confirmSyncModalEl = document.getElementById('confirmSyncModal');
            const detailFieldIds = [
                'GLCode',
                'GLName',
                'Description',
                'GLAccountTypeID',
                'GLTypeGroupIDValue',
                'GLSubAccountTypeIDValue',
                'BranchID',
                'Source',
                'SourceTable',
                'CreatedOn',
                'ModifiedOn',
            ];

            const statusBadgeClass = function (status) {
                if (status === 'completed') return 'badge bg-success';
                if (status === 'failed') return 'badge bg-danger';
                if (status === 'running' || status === 'pending') return 'badge bg-warning text-dark';
                return 'badge bg-secondary';
            };

            const updateProgressUi = function (payload) {
                const status = payload.status || 'unknown';
                const percentage = payload.percentage ?? 0;
                const errorText = payload.isFailed && payload.error ? ` (${payload.error})` : '';

                statusBadge.className = statusBadgeClass(status);
                statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                statusMessage.textContent = (payload.message || 'Processing...') + errorText;
                progressBar.style.width = Math.max(0, Math.min(100, percentage)) + '%';
                recordsSynced.textContent = payload.recordsSynced ?? 0;
                recordsFailed.textContent = payload.recordsFailed ?? 0;
                totalRecords.textContent = payload.totalRecords ?? 0;
                currentPage.textContent = payload.currentPage ?? 0;
            };

            const stopPolling = function () {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
            };

            const poll = async function () {
                if (!syncId) {
                    return;
                }

                try {
                    const response = await fetch(progressBaseUrl + '/' + syncId, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    if (!payload || !payload.success) {
                        return;
                    }

                    progressCard.classList.remove('d-none');
                    updateProgressUi(payload);

                    if (payload.isComplete || payload.isFailed) {
                        stopPolling();
                    }
                } catch (error) {
                    console.error('Failed to poll GL sync progress', error);
                }
            };

            const startPolling = function () {
                stopPolling();
                if (syncId) {
                    progressCard.dataset.syncId = syncId;
                    progressCard.classList.remove('d-none');
                    poll();
                    timer = setInterval(poll, 3000);
                }
            };

            if (startSyncForm) {
                startSyncForm.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    if (startSyncSubmitBtn) {
                        startSyncSubmitBtn.disabled = true;
                    }

                    const formData = new FormData(startSyncForm);
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    try {
                        const response = await fetch(startSyncForm.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf || formData.get('_token'),
                            },
                            credentials: 'same-origin',
                            body: formData,
                        });

                        let payload = null;
                        try {
                            payload = await response.json();
                        } catch (e) {
                            payload = null;
                        }

                        if (!response.ok || !payload || !payload.success) {
                            if (payload?.syncRunId) {
                                syncId = payload.syncRunId;
                                statusMessage.textContent = payload.message || 'Sync is already running.';
                                statusBadge.className = 'badge bg-warning text-dark';
                                statusBadge.textContent = 'Running';
                                startPolling();
                                const modal = bootstrap.Modal.getInstance(confirmSyncModalEl);
                                if (modal) {
                                    modal.hide();
                                }
                                return;
                            }

                            statusMessage.textContent = payload?.message || 'Failed to start sync.';
                            statusBadge.className = 'badge bg-danger';
                            statusBadge.textContent = 'Failed';
                            return;
                        }

                        syncId = payload.syncRunId;
                        statusMessage.textContent = payload.message || 'Sync started.';
                        statusBadge.className = 'badge bg-warning text-dark';
                        statusBadge.textContent = 'Pending';

                        const modal = bootstrap.Modal.getInstance(confirmSyncModalEl);
                        if (modal) {
                            modal.hide();
                        }

                        startPolling();
                    } catch (error) {
                        console.error('Failed to start sync', error);
                        statusMessage.textContent = 'Failed to start sync due to a network error.';
                        statusBadge.className = 'badge bg-danger';
                        statusBadge.textContent = 'Failed';
                    } finally {
                        if (startSyncSubmitBtn) {
                            startSyncSubmitBtn.disabled = false;
                        }
                    }
                });
            }

            if (syncId) {
                startPolling();
            }

            document.querySelectorAll('.view-gl-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    const data = {
                        GLCode: button.dataset.glCode || '',
                        GLName: button.dataset.glName || '',
                        Description: button.dataset.description || '',
                        GLAccountTypeID: button.dataset.glAccountTypeId || '',
                        GLTypeGroupIDValue: button.dataset.glTypeGroupIdValue || '',
                        GLSubAccountTypeIDValue: button.dataset.glSubAccountTypeIdValue || '',
                        BranchID: button.dataset.branchId || '',
                        Source: button.dataset.source || '',
                        SourceTable: button.dataset.sourceTable || '',
                        CreatedOn: button.dataset.createdOn || '',
                        ModifiedOn: button.dataset.modifiedOn || '',
                        IsActive: button.dataset.isActive === '1',
                    };

                    detailFieldIds.forEach(function (field) {
                        const el = document.getElementById('detail' + field);
                        if (el) {
                            el.textContent = data[field] ?? '-';
                        }
                    });

                    const activeEl = document.getElementById('detailIsActive');
                    if (activeEl) {
                        activeEl.textContent = data.IsActive ? 'Yes' : 'No';
                    }
                });
            });
        });
    </script>
@endsection
