@extends('layouts.app')

@section('title', 'Lease Renewals')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        /* Wrap table header text */
        th {
            white-space: normal !important;
            word-wrap: break-word;
        }
    </style>
@endsection

@section('content')
<div class="container mt-4">

    <a href="{{ route('renewlease.create') }}" class="btn btn-primary mb-3">
        Renew Lease
    </a>

    <p><small>This is a list of renewals</small></p>

    @if($leaserenewals->count())
        <table class="table table-bordered table-striped align-middle" id="LeaseRenewal">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Lease Number</th>
                    <th>End Date of Current Lease</th>
                    <th>New Start Date</th>
                    <th>New End Date</th>
                    <th>Payment Frequency</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaserenewals as $leaserenewal)
                    <tr>
                        <td>{{ $loop->iteration ?? '-' }}</td>
                        <td>{{ $leaserenewal->lease->LeaseNumber ?? '-' }}</td>
                        <td>
                            {{ $leaserenewal->EndDateCurrentLease 
                                ? \Carbon\Carbon::parse($leaserenewal->EndDateCurrentLease)->format('d M Y') 
                                : '-' 
                            }}
                        </td>
                        <td>
                            {{ $leaserenewal->NewStartDate 
                                ? \Carbon\Carbon::parse($leaserenewal->NewStartDate)->format('d/m/Y') 
                                : '-' 
                            }}
                        </td>
                        <td>
                            {{ $leaserenewal->NewEndDate 
                                ? \Carbon\Carbon::parse($leaserenewal->NewEndDate)->format('d/m/Y') 
                                : '-' 
                            }}
                        </td>
                        <td>{{ $leaserenewal->paymentFrequency->Description ?? '-' }}</td>
                        <td>
                            @php
                                $statusEnum = \App\Enums\Core\ApprovalEnum::from($leaserenewal->Status ?? 'P');
                            @endphp
                            <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                {{ $statusEnum->label() }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('renewlease.show', $leaserenewal->Id) }}" 
                               class="btn btn-sm btn-info text-white" title="View lease">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="{{ route('renewlease.edit', $leaserenewal->Id) }}" 
                               class="btn btn-sm btn-warning" title="Edit lease">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <form action="{{ route('renewlease.destroy', $leaserenewal->Id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        title="Delete lease" 
                                        onclick="return confirm('Are you sure you want to delete this lease schedule?');">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i>
            No lease renewals registered yet.
        </div>
    @endif

</div>

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#LeaseRenewal').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection

@endsection
