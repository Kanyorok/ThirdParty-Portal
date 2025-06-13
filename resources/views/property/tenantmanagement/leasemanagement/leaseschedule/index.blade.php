@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
    <a href="{{ route('schedulelease.create') }}" class="btn btn-success">🧾 Generate Schedule</a>
    @if($leaseschedules->count())
        <table class="table table-bordered table-striped mt-4">
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
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="btn btn-outline-primary" onclick="window.print()">🖨️ Print Schedule</button>
    @else
        <p>No lease renewals registered yet.</p>
        @endif
</div>
@endsection
