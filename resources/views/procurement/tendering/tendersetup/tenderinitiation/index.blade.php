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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 text-primary"><i class="fas fa-list-alt me-2"></i>Initiated Tenders</h3>
            <a href="{{ route('initiatetender.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> New Tender
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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
                        @forelse($tenders as $tender)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
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
                                <td>{{ $tender->SubmissionDeadline ? $tender->SubmissionDeadline->format('M d, Y H:i') : 'N/A' }}</td>
                                <td>{{ $tender->OpeningDate ? $tender->OpeningDate->format('M d, Y H:i') : 'N/A' }}</td>
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
                                    <a href="{{ route('initiatetender.show', $tender->Id) }}" class="btn btn-sm btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if ($tender->ApprovalStatus === \App\Enums\TenderApprovalStatusEnum::APPROVED || $tender->ApprovalStatus === \App\Enums\TenderApprovalStatusEnum::REJECTED)
                                        {{-- If tender is approved or rejected, disable edit button --}}
                                        <a href="{{ route('initiatetender.edit', $tender->Id) }}"
                                           class="btn btn-sm btn-outline-primary disabled" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('initiatetender.edit', $tender->Id) }}"
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <form action="{{ route('initiatetender.destroy', $tender->Id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                onclick="return confirm('Are you sure you want to delete tender \'{{ $tender->TenderNo }}\'? This action cannot be undone.')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                <i class="fas fa-folder-open fa-2x text-muted mb-2"></i><br>
                                    No initiated tenders found. <a href="{{ route('initiatetender.create') }}">Create a new one?</a>
                                </td>
                            </tr>
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

        // Dismiss alerts automatically after some time
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        }, 5000); // 5 seconds
    </script>
@endpush
