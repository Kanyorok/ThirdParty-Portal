@extends('layouts.app')

@section('title', 'GL Sync')

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
                <h4 class="mb-1 text-info"><i class="fas fa-sync-alt me-2"></i>Sync General Ledgers (NMB)</h4>
                <p class="text-muted mb-0">Pull GL accounts from NMB using background jobs with pagination support.</p>
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
                        <small class="text-muted">Pulls from NMB endpoint and processes all pages until completion.</small>
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
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const progressCard = document.getElementById('syncProgressCard');
            const progressBaseUrl = @json(url('/finance/chartofaccounts/gl-sync/progress'));

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
        });
    </script>
@endsection
