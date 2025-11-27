@extends('layouts.app')

@section('title', 'Initiated Tenders')

@push('styles')
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .badge {
            font-size: 0.85em;
            padding: 0.4em 0.7em;
        }
        .action-buttons .btn {
            margin-right: 0.3rem;
        }
        .action-buttons form {
            margin-bottom: 0;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-4">
        @php
            // Ensure newest (last) record appears first in the listing.
            $isPaginator = isset($tenders) && method_exists($tenders, 'links');
            if ($isPaginator) {
                $tendersSorted = $tenders; // assume controller handled ordering for paginator
            } else {
                $tendersCollection = isset($tenders) ? collect($tenders) : collect();
                if ($tendersCollection->isNotEmpty()) {
                    // Prefer sorting by Id (descending) as a proxy for newest items
                    $tendersSorted = $tendersCollection->sortByDesc(fn($t) => $t->Id ?? null)->values();
                } else {
                    $tendersSorted = $tendersCollection;
                }
            }
        @endphp
        @php
            // Load TenderStatus descriptions from t_CodeDetails (Value -> Description)
            $tenderStatusMap = [];
            try {
                $rows = \Illuminate\Support\Facades\DB::table('t_CodeDetails')->where('CodeID', 'TenderStatus')->get();
                foreach ($rows as $r) {
                    $tenderStatusMap[$r->Value] = $r->Description;
                }
            } catch (\Throwable $e) {
                // ignore and fallback to enum displayName
            }
        @endphp
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 text-primary"><i class="fas fa-list-alt me-2"></i>Initiated Tenders</h3>
            @canWrite('tender')
                <a href="{{ route('initiatetender.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> New Tender
                </a>
            @endcanWrite
        </div>

        <div class="alert alert-info" role="alert" style="background:#eef6ff;border:1px solid #cfe2ff;color:#084298;">
            <i class="fa fa-info-circle me-2"></i>
            <span title="Open: all suppliers can bid. Restricted: only invited based on selected item category. Use 'Add to Grid' to add items.'">
                <strong>Guidance:</strong> Tender Initiation supports two types: Open (all suppliers can bid) and Restricted (only invited suppliers based on the selected item category). Add items to the tender by clicking Add to Grid.
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="tendersTable">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tender No.</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Category</th>
                            {{-- <th>Est. Value</th> --}}
                            <th>Currency</th>
                            {{-- <th>PR No.</th> --}}
                            <th>Deadline</th>
                            <th>Opening Date</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th style="min-width: 180px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($tendersSorted as $tender)
                            <tr>
                                <td>{{ $isPaginator ? ($tenders->firstItem() + $loop->index) : $loop->iteration }}</td>
                                <td>{{ $tender->TenderNo }}</td>
                                <td>
                                    <a href="{{ route('initiatetender.show', $tender->Id) }}" title="View {{ $tender->Title }}">
                                        {{ Str::limit($tender->Title, 40) }}
                                    </a>
                                </td>
                                <td>
                                    @if($tender->TenderType)
                                        <span class="badge
                                        @if($tender->TenderType === \App\Enums\TenderTypeEnum::Restricted) bg-warning text-dark
                                        @elseif($tender->TenderType === \App\Enums\TenderTypeEnum::Open) bg-success
                                        @else bg-info text-dark
                                        @endif">
                                        {{ $tender->TenderType?->name ?? 'Unknown' }}
                                    </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($tender->TenderCategory)
                                        {{ $tender->tenderCategory?->TenderCategory }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                {{-- <td>{{ $tender->EstimatedValue ? number_format($tender->EstimatedValue, 2) : 'N/A' }}</td> --}}
                                <td>
                                    {{ $tender->currency ? $tender->currency->Code : 'N/A' }}
                                </td>
                                {{-- <td>{{ $tender->RelatedPRID ? 'PR/' . $tender->RelatedPRID : 'N/A' }}</td> --}}
                                <td>{{ $tender->SubmissionDeadline ? $tender->SubmissionDeadline->format('d M Y') : 'N/A' }}</td>
                                <td>{{ $tender->OpeningDate ? $tender->OpeningDate->format('d M Y') : 'N/A' }}</td>
                                <td>
                                    @if($tender->Status)
                                        <span class="badge rounded-pill
                                    @switch($tender->Status)
                                        @case(\App\Enums\TenderStatusEnum::Draft) bg-secondary @break
                                        @case(\App\Enums\TenderStatusEnum::Published) bg-success @break
                                        @case(\App\Enums\TenderStatusEnum::Closed) bg-dark @break
                                        @default bg-light text-dark @break
                                    @endswitch">
                                    {{ $tender->Status->displayName() }}
                                </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if ($tender->ApprovalStatus== \App\Enums\TenderApprovalStatusEnum::APPROVED)
                                        <span class="badge rounded-pill bg-success text-white">
                                            Approved
                                         </span>
                                    @endif
                                    @if ($tender->ApprovalStatus== \App\Enums\TenderApprovalStatusEnum::REJECTED)
                                        <span class="badge rounded-pill bg-danger text-white">
                                            Rejected
                                         </span>
                                    @endif
                                    @if ($tender->ApprovalStatus== \App\Enums\TenderApprovalStatusEnum::PENDING)
                                        <span class="badge rounded-pill bg-warning text-dark">
                                            Pending
                                         </span>
                                    @endif
                                </td>
                                <td class="action-buttons">
                                    @canRead('tender')
                                        <a href="{{ route('initiatetender.show', $tender->Id) }}" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcanRead

                                    @if ($tender->ApprovalStatus === \App\Enums\TenderApprovalStatusEnum::APPROVED)
                                        {{-- Approved: hide Edit and Delete actions --}}
                                    @elseif ($tender->ApprovalStatus === \App\Enums\TenderApprovalStatusEnum::REJECTED)
                                        {{-- Rejected: keep Edit disabled (read-only) --}}
                                        @canUpdate('tender')
                                            <a href="{{ route('initiatetender.edit', $tender->Id) }}"
                                               class="btn btn-sm btn-outline-primary disabled" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcanUpdate
                                        @canDelete('tender')
                                            <form action="{{ route('initiatetender.destroy', $tender->Id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete tender \'{{ $tender->TenderNo }}\'? This action cannot be undone.')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endcanDelete
                                    @else
                                        {{-- Other statuses: allow Edit and Delete --}}
                                        @canUpdate('tender')
                                            <a href="{{ route('initiatetender.edit', $tender->Id) }}"
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcanUpdate
                                        @canDelete('tender')
                                            <form action="{{ route('initiatetender.destroy', $tender->Id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete tender \'{{ $tender->TenderNo }}\'? This action cannot be undone.')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endcanDelete
                                    @endif
                                </td>
                            </tr>
                        @empty
                            {{-- <tr>
                                <td colspan="12" class="text-center py-4">
                                    <i class="fas fa-folder-open fa-2x text-muted mb-2"></i><br>
                                    No initiated tenders found. <a href="{{ route('initiatetender.create') }}">Create a new one?</a>
                                </td>
                            </tr> --}}
                        @endforelse
                        </tbody>
                    </table>
{{--                </div>--}}
{{--                @if($tenders->hasPages())--}}
{{--                    <div class="mt-3 d-flex justify-content-center">--}}
{{--                        {{ $tenders->links() }}--}}
{{--                    </div>--}}
{{--                @endif--}}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
     <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
     <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tendersTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
            });
        });

        // Auto-dismiss of alerts disabled to keep the initiated tender list and guidance visible.
        // If you want alerts to auto-dismiss later, re-enable with a timeout value.
        // Example re-enable (uncomment):
        // window.setTimeout(function() {
        //     $(".alert").fadeTo(500, 0).slideUp(500, function(){
        //         $(this).remove();
        //     });
        // }, 5000);
    </script>
@endpush
