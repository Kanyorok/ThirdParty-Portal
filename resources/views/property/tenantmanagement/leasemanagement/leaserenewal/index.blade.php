@extends('layouts.app')
@section('title', 'Lease Renewals')
@section('content')
    @section('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @endsection
<div class="container mt-4">
<a href="{{ route('renewlease.create') }}" class="btn btn-primary mb-3">Renew Lease</a>
  <h4 class="fw-bold mb-3">📋 Lease Renewals</h4>

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
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($leaserenewals as $leaserenewal)
      <tr>
          <td>{{ $loop->iteration ?? '-' }}</td>
          <td>{{ $leaserenewal->lease->LeaseNumber ?? '-' }}</td>
          <td>{{ $leaserenewal->EndDateCurrentLease ? \Carbon\Carbon::parse($leaserenewal->EndDateCurrentLease ) ->format('d/m/Y') :  '-' }}</td>
          <td>{{ $leaserenewal->NewStartDate ? \Carbon\Carbon::parse($leaserenewal->NewStartDate)->format('d/m/Y') : '-' }}</td>
          <td>{{ $leaserenewal->NewEndDate ? \Carbon\Carbon::parse($leaserenewal->NewEndDate)->format('d/m/Y') : '-' }}</td>
          <td>{{ $leaserenewal->paymentFrequency->Description ?? '-' }}</td>
          <td><a href="{{ route('renewlease.show', $leaserenewal->Id) }}" class="btn btn-sm btn-outline-secondary">View</a>
            <a href="{{ route('renewlease.edit', $leaserenewal->Id) }}" class="btn btn-sm btn-warning">Edit</a>
            <form action="{{ route('renewlease.destroy', $leaserenewal->Id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this lease schedule?');">Delete</button>
            </form>
          </td>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No lease renewals registered yet.</p>
    @endif
</div>
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
