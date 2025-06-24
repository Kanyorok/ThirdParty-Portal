@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <a href="{{ route('schedulelease.create') }}" class="btn btn-success">🧾 Generate Schedule</a>
    @if($leaseschedules->count())
        <table class="table table-bordered table-striped mt-4" id="LeaseSchedule">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Lease Name</th>
                <th>Payment Frequency</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Base Rent</th>
                <th>Service Charge</th>
                <th>Parking Fee</th>
                <th>Other Charges</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($leaseschedules as $leaseschedule)
                <tr>
                    <td>{{ $loop->iteration ?? '-' }}</td>
                    <td>{{ $leaseschedule->leaseID ?? '-' }}</td>
                    <td>{{ $leaseschedule->PaymentFrequency ?? '-' }}</td>
                    <td>{{ $leaseschedule->StartDate ?? '-' }}</td>
                    <td>{{ $leaseschedule->EndDate ?? '-' }}</td>
                    <td>{{ $leaseschedule->BaseRent ?? '-' }}</td>
                    <td>{{ $leaseschedule->ServiceCharge ?? '-' }}</td>
                    <td>{{ $leaseschedule->ParkingFee ?? '-' }}</td>
                    <td>{{ $leaseschedule->OtherCharges ?? '-' }}</td>
                    <td>
            <a href="{{ route('schedulelease.show', $leaseschedule->id) }}" class="btn btn-sm btn-info">👁 View</a>
            <a href="{{ route('schedulelease.edit', $leaseschedule->id) }}" class="btn btn-sm btn-warning">Edit</a>
            <form action="{{ route('schedulelease.destroy', $leaseschedule->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this lease schedule?');">Delete</button>
            </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="btn btn-outline-primary" onclick="window.print()">🖨️ Print Schedule</button>
    @else
        <p>No lease renewals registered yet.</p>
        @endif
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#LeaseSchedule').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>

@endsection
