@extends('layouts.app')
@section('title', 'Commissions Earned')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    {{-- filter form remains the same --}}

        <table id="commissionsTable" class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Policy</th>
                    <th>Claim Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($claims as $e)
                    <tr>
                        <td>{{ $e->Id }}</td>
                        <td>{{ $e->policy->PolicyNumber ?? '-'}}</td>
                        <td>{{ optional($claims->firstWhere('Id', $e->Id)?->claimtype)->Description ?? 'N/A' }}</td>
                        <td>{{ number_format($e->ClaimAmount, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($e->ClaimDate)->format('d/m/Y') }}</td>
                        <td class="status">{{ optional($claims->firstWhere('Id', $e->Id))->status->Description ?? 'N/A' }}</td>
                        <td class="action-cell">
                            <a href="{{ route('bancassurance.commissions.payouts.pay', $e->Id) }}" 
                               class="btn btn-sm btn-success payout-btn">💰 Payout</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
</div>

{{-- JavaScript to control button behavior --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    const rows = document.querySelectorAll("#commissionsTable tbody tr");

    rows.forEach(row => {
        let status = row.querySelector(".status").innerText.trim();
        let actionCell = row.querySelector(".action-cell");
        let payoutBtn = actionCell.querySelector(".payout-btn");

        if (status.toLowerCase() === "paid") {
            // Leave payout button enabled
            payoutBtn.style.display = "inline-block";
        } else {
            // Replace payout button with pending button
            payoutBtn.remove();
            let pendingBtn = document.createElement("button");
            pendingBtn.className = "btn btn-sm btn-secondary";
            pendingBtn.innerText = "⌛ Pending Payment";
            pendingBtn.disabled = true;
            actionCell.appendChild(pendingBtn);
        }
    });
});
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#commissionsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
