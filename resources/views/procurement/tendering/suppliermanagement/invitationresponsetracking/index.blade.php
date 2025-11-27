@extends('layouts.app')
@section('title', 'Invitation Response Tracker')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
<a href="{{ route('tenderresponse.create') }}" class="btn btn-success">➕ Add Response</a>
    <h4></h4>
    <div class="table-responsive">
        <table id="responsetrackingTable" class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Ref</th>
                    <th>Supplier</th>
                    <th>Sent On</th>
                    <th>Status</th>
                    <th>Responded On</th>
                    <th>Remarks</th>
                </tr>
            </thead>

            <tbody>
    @forelse ($invitations as $index => $invitation)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                @php
                    $tender = \App\Models\Procurement\Tender::find($invitation->TenderId);
                @endphp
                @if($tender)
                    {{ trim((($tender->TenderNo ?? '') . ' - ' . ($tender->Title ?? ''))) }}
                @else
                    {{ $invitation->TenderId }}
                @endif
            </td>
            <td>
                @php
                    $supplierName = $invitation->SupplierId;
                    try {
                        // First try Supplier model (t_Suppliers) then its thirdParty relation
                        $sup = \App\Models\ThirdParies\Supplier::with('thirdParty')->find($invitation->SupplierId);
                        if($sup && $sup->thirdParty) {
                            $supplierName = $sup->thirdParty->ThirdPartyName ?? ($sup->thirdParty->TradingName ?? $invitation->SupplierId);
                        } else {
                            // Fallback: maybe SupplierId is actually a ThirdParties Id
                            $tp = \App\Models\ThirdParty\ThirdParties::find($invitation->SupplierId);
                            if($tp) {
                                $supplierName = $tp->ThirdPartyName ?? ($tp->TradingName ?? $invitation->SupplierId);
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore and show raw id
                    }
                @endphp
                {{ $supplierName }}
            </td>
            <td>{{ \Carbon\Carbon::parse($invitation->InvitationDate)->format('d M Y') }}</td>           <td>
                @php $status = strtolower($invitation->ResponseStatus ?? ''); @endphp
                @if($status === 'accepted')
                    <span class="badge bg-success">{{ $invitation->ResponseStatus }}</span>
                @elseif($status === 'pending' || $status === 'declined')
                    <span class="badge bg-danger">{{ $invitation->ResponseStatus }}</span>
                @else
                    <span class="badge bg-secondary">{{ $invitation->ResponseStatus }}</span>
                @endif
            </td>
            <td>{{ \Carbon\Carbon::parse($invitation->ResponseDate)->format('d M Y') }}</td>           <td>{{ $invitation->DeclineReason ?? 'Ready to submit bid' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="text-center">No invitations found.</td>
        </tr>
    @endforelse
</tbody>
        </table>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        @if(!$invitations->isEmpty())
        $('#responsetrackingTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                emptyTable: ""
            }
        });
        @endif
    });
</script>
@endsection

