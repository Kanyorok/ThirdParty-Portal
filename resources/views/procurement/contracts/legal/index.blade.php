@extends('layouts.app')
@section('title', 'Legal Integration - Contract Requests')
@section('content')

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">⚖️ Legal Integration - Contract Requests</h4>
            <div>
                <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Contracts
                </a>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>About Legal Integration:</strong>
            This page tracks contract requests that have been sent to the Legal Department for creation and management.
            These contracts are managed externally by the Legal team.
        </div>

        <!-- Contract Requests Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Contract Requests Sent to Legal</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Legal Ref</th>
                            <th>Tender Ref</th>
                            <th>Title</th>
                            <th>Winning Supplier</th>
                            <th>Contract Value</th>
                            <th>Sent Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($legalRequests as $index => $request)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong
                                        class="text-primary">{{ $request->ContractRequestRef ?? 'Pending' }}</strong>
                                </td>
                                <td>{{ $request->tender->TenderNo ?? 'N/A' }}</td>
                                <td>{{ $request->tender->Title ?? 'N/A' }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-secondary text-white me-2"
                                             style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                            {{ strtoupper(substr($request->winningSupplier->SupplierName ?? 'U', 0, 2)) }}
                                        </div>
                                        {{ $request->winningSupplier->SupplierName ?? '--' }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <strong>{{ $request->tender->currency->Code ?? 'KES' }} {{ number_format($request->AwardedAmount ?? 0, 2) }}</strong>
                                </td>
                                <td>{{ $request->ModifiedOn ? $request->ModifiedOn->format('Y-m-d H:i') : '--' }}</td>
                                <td>
                                    <span class="badge {{ $request->contract_status_badge['class'] }}">
                                        {{ $request->contract_status_badge['text'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('awards.unified', $request->tender->Id) }}"
                                           class="btn btn-sm btn-outline-info" title="View Award Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="followUpLegal('{{ $request->ContractRequestRef }}')"
                                                title="Follow Up">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <br>
                                    No contract requests sent to Legal yet.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($legalRequests->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $legalRequests->links() }}
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5>{{ $legalRequests->total() }}</h5>
                        <p class="mb-0">Total Requests</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h5>{{ $legalRequests->where('ContractStatus', 'Sent to Legal')->count() }}</h5>
                        <p class="mb-0">Pending with Legal</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5>{{ $legalRequests->where('ContractStatus', 'Under Review')->count() }}</h5>
                        <p class="mb-0">Under Review</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5>{{ $legalRequests->where('ContractStatus', 'Approved')->count() }}</h5>
                        <p class="mb-0">Completed</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integration Guidelines -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">📋 Integration Guidelines</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>📤 Sending to Legal:</h6>
                        <ul class="text-muted">
                            <li>Select "Legal Managed" during contract creation</li>
                            <li>Provide complete contract details and requirements</li>
                            <li>Legal team receives notification with tender & award details</li>
                            <li>Reference number generated for tracking</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>🔄 Status Updates:</h6>
                        <ul class="text-muted">
                            <li><span class="badge bg-primary">Sent to Legal</span> - Request submitted</li>
                            <li><span class="badge bg-warning text-dark">Under Review</span> - Being processed</li>
                            <li><span class="badge bg-success">Approved</span> - Contract ready</li>
                            <li><span class="badge bg-dark">Executed</span> - Contract signed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function followUpLegal(reference) {
            if (reference && reference !== 'Pending') {
                alert(`Follow up with Legal Department regarding contract request: ${reference}\n\nContact: Legal Department\nReference: ${reference}`);
            } else {
                alert('Reference number not available yet. Please try again later.');
            }
        }
    </script>

@endsection
